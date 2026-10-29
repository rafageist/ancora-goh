<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\Entity\PlacementChange;
use Drupal\ancora_scheduling\Sync\WorkspaceSync;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\UserInterface;

/**
 * Moves one saved placement to a checked day and slot (#130, time only).
 *
 * The command contract (docs/modernization/timetable-editing.md §5):
 *
 * - requestCheck(): queues a destination check (PlacementCheckRunner, run
 *   by the worker in a workspace). Allowed whenever the user may edit the
 *   placement; a queued generation job does not block it.
 * - check(): the check's state and, when done, its grid.
 * - commit(): moves the placement to one cell of a done check, in ONE
 *   Drupal transaction under the publication's advisory lock, refusing
 *   (PlacementModificationException, nothing written) when:
 *   1. the site is not in workspace mode;
 *   2. the user may not edit the placement (its plan's owner with "edit
 *      academic data", or "administer academic data");
 *   3. the check is not a done check of this placement, or is older than
 *      CHECK_TTL;
 *   4. a generation job of the site is queued or running (the input
 *      version covers the whole site: the move would make its publication
 *      stale);
 *   5. the site input changed since the check (WorkspaceSync version);
 *   6. the placement changed since the check (signature);
 *   7. the cell is not a valid cell of the check, or is where it is now;
 *   8. for an undo: the change is not the placement's last one, or the
 *      cell is not the one it came from.
 *   Then only the placement's day and first slot are set (group, teacher,
 *   room, other resources, engine id and job are kept), the record is
 *   validated and saved as the plan's creator, and a PlacementChange is
 *   written. No engine row is written: in workspace mode there is none
 *   outside job workspaces, and the next job loads the placement at its
 *   new cell, where the legacy RULEs book it (cases 148, 149).
 * - undo(): commit() back to the cell before the placement's last change,
 *   with a new check.
 *
 * The valid cells are valid within the placement's period, as the
 * scheduler sees it. They are NOT checked against other plans whose dates
 * overlap (a shared teacher or room, case 150).
 */
final class PlacementModification {

  use StringTranslationTrait;

  /**
   * Seconds a done check stays usable for a commit.
   */
  public const CHECK_TTL = 900;

  public function __construct(
    private readonly Connection $database,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EngineSettings $settings,
    private readonly JobRepository $jobs,
    private readonly WorkspaceSync $input,
    private readonly AccountSwitcherInterface $accountSwitcher,
    private readonly TimeInterface $time,
  ) {}

  /**
   * Whether moves are offered: workspace mode only.
   */
  public function isAvailable(): bool {
    return $this->settings->isWorkspace();
  }

  /**
   * A placement's signature: everything a move must keep, plus its cell.
   */
  public static function signature(Placement $placement): string {
    $ids = static fn (string $field): array => array_map('intval', array_column($placement->get($field)->getValue(), 'target_id'));
    return hash('sha256', json_encode([
      (int) $placement->get('template')->target_id,
      (int) $placement->get('student_group')->target_id,
      $ids('teacher'),
      $ids('room'),
      $ids('other_resources'),
      (int) $placement->get('day')->value,
      (int) $placement->get('slot')->value,
      $placement->getEngineId(),
    ], JSON_THROW_ON_ERROR));
  }

  /**
   * Queues a destination check of a placement; returns its id.
   *
   * A queued or running check of the same placement and user is reused.
   */
  public function requestCheck(Placement $placement, AccountInterface $account): int {
    $this->assertAvailable();
    $this->assertAccess($placement, $account);
    $open = $this->database->select('ancora_placement_check', 'c')
      ->fields('c', ['id'])
      ->condition('placement_id', $placement->id())
      ->condition('requested_by', $account->id())
      ->condition('state', ['queued', 'running'], 'IN')
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchField();
    if ($open) {
      return (int) $open;
    }
    $now = $this->time->getCurrentTime();
    return (int) $this->database->insert('ancora_placement_check')
      ->fields([
        'placement_id' => (int) $placement->id(),
        'requested_by' => (int) $account->id(),
        'state' => 'queued',
        'created' => $now,
        'changed' => $now,
      ])
      ->execute();
  }

  /**
   * A check of a placement: state, error and, when done, the grid.
   *
   * @return array|null
   *   id, placement_id, state, error, version, signature, created, changed,
   *   result (decoded), expired; NULL if there is no such check of it.
   */
  public function check(Placement $placement, int|string $id): ?array {
    if (!ctype_digit((string) $id)) {
      return NULL;
    }
    $row = $this->database->select('ancora_placement_check', 'c')->fields('c')
      ->condition('id', (int) $id)
      ->condition('placement_id', (int) $placement->id())
      ->execute()
      ->fetchAssoc();
    if (!$row) {
      return NULL;
    }
    $row['result'] = $row['result'] === NULL ? NULL : json_decode($row['result'], TRUE, 8, JSON_THROW_ON_ERROR);
    $row['expired'] = $row['state'] === 'done' && (int) $row['changed'] < $this->time->getCurrentTime() - self::CHECK_TTL;
    return $row;
  }

  /**
   * The cell of a done check, or NULL.
   */
  public static function cell(array $check, int $day, int $slot): ?array {
    foreach ($check['result']['cells'] ?? [] as $cell) {
      if ($cell['day'] === $day && $cell['slot'] === $slot) {
        return $cell;
      }
    }
    return NULL;
  }

  /**
   * Moves the placement to a cell of a done check (see the class comment).
   *
   * @throws \Drupal\ancora_scheduling\Timetable\PlacementModificationException
   *   When the move is refused; nothing is written then.
   */
  public function commit(Placement $placement, int|string $checkId, int|string $day, int|string $slot, AccountInterface $account, ?PlacementChange $undo = NULL): PlacementChange {
    $this->assertAvailable();
    if (!ctype_digit((string) $day) || !ctype_digit((string) $slot)) {
      throw new PlacementModificationException('cell');
    }
    [$day, $slot] = [(int) $day, (int) $slot];
    $transaction = $this->database->startTransaction();
    try {
      $this->database->query('SELECT pg_advisory_xact_lock(:key)', [':key' => PlacementPublisher::LOCK]);
      $storage = $this->entityTypeManager->getStorage('ancora_placement');
      $storage->resetCache([$placement->id()]);
      $placement = $storage->load($placement->id());
      if (!$placement instanceof Placement) {
        throw new PlacementModificationException('gone');
      }
      $this->assertAccess($placement, $account);
      $check = $this->check($placement, $checkId);
      if ($check === NULL || $check['state'] !== 'done') {
        throw new PlacementModificationException('check');
      }
      if ($check['expired']) {
        throw new PlacementModificationException('expired');
      }
      if (($job = $this->jobs->activeForSite()) !== NULL) {
        throw new PlacementModificationException('job', ['@id' => $job->id]);
      }
      if (!hash_equals((string) $check['version'], $this->input->currentVersion())) {
        throw new PlacementModificationException('version');
      }
      if (!hash_equals((string) $check['signature'], self::signature($placement))) {
        throw new PlacementModificationException('placement');
      }
      $cell = self::cell($check, $day, $slot);
      $from = ['day' => (int) $placement->get('day')->value, 'slot' => (int) $placement->get('slot')->value];
      if ($cell === NULL || !$cell['ok']) {
        throw new PlacementModificationException('cell');
      }
      if ($from === ['day' => $day, 'slot' => $slot]) {
        throw new PlacementModificationException('same');
      }
      if ($undo !== NULL) {
        $last = $this->lastChange($placement);
        if ($last === NULL || (int) $last->id() !== (int) $undo->id()) {
          throw new PlacementModificationException('undo');
        }
        if ([(int) $undo->get('from_day')->value, (int) $undo->get('from_slot')->value] !== [$day, $slot]) {
          throw new PlacementModificationException('undo');
        }
      }

      $placement->set('day', $day)->set('slot', $slot);
      $change = $this->entityTypeManager->getStorage('ancora_placement_change')->create([
        'plan' => $placement->get('plan')->target_id,
        'placement' => $placement->id(),
        'from_day' => $from['day'],
        'from_slot' => $from['slot'],
        'to_day' => $day,
        'to_slot' => $slot,
        'uid' => $account->id(),
        'reason' => $undo === NULL ? 'move' : 'undo',
        'reverts' => $undo?->id(),
        'version' => $check['version'],
        'check_id' => (int) $check['id'],
      ]);
      $this->saveAsCreator($placement, $change);
      return $change;
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }
  }

  /**
   * Moves the placement back to where it was before its last move.
   *
   * @throws \Drupal\ancora_scheduling\Timetable\PlacementModificationException
   *   When the undo is refused; nothing is written then.
   */
  public function undo(PlacementChange $change, int|string $checkId, AccountInterface $account): PlacementChange {
    $placement = $change->get('placement')->entity;
    if (!$placement instanceof Placement) {
      throw new PlacementModificationException('gone');
    }
    return $this->commit($placement, $checkId, (int) $change->get('from_day')->value, (int) $change->get('from_slot')->value, $account, $change);
  }

  /**
   * The changes of a placement, newest first.
   *
   * @return list<\Drupal\ancora_scheduling\Entity\PlacementChange>
   *   The changes.
   */
  public function history(Placement $placement): array {
    $storage = $this->entityTypeManager->getStorage('ancora_placement_change');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('placement', $placement->id())->sort('id', 'DESC')->execute();
    return array_values($storage->loadMultiple($ids));
  }

  /**
   * The placement's last change, or NULL.
   */
  public function lastChange(Placement $placement): ?PlacementChange {
    return $this->history($placement)[0] ?? NULL;
  }

  /**
   * The ids of the given placements that were moved by hand.
   *
   * @param list<int> $ids
   *   Placement ids.
   *
   * @return array<int, true>
   *   The moved ones.
   */
  public function moved(array $ids): array {
    if ($ids === [] || !$this->database->schema()->tableExists('ancora_placement_change')) {
      return [];
    }
    $moved = $this->database->select('ancora_placement_change', 'c')
      ->fields('c', ['placement'])
      ->condition('placement', $ids, 'IN')
      ->distinct()
      ->execute()
      ->fetchCol();
    return array_fill_keys(array_map('intval', $moved), TRUE);
  }

  /**
   * The text of a refusal.
   */
  public function message(PlacementModificationException $e): TranslatableMarkup {
    $a = $e->arguments;
    return match ($e->reason) {
      'cell' => $this->t('That cell is not a valid destination of this check.'),
      'gone' => $this->t('The placement no longer exists.'),
      'check' => $this->t('There is no finished destination check of this placement. Check again.'),
      'expired' => $this->t('The destination check is too old. Check again.'),
      'job' => $this->t('Timetable run @id is queued or running. Moves are refused until it ends, because the run would refuse its result as made from changed data.', $a),
      'version' => $this->t('The academic data or the timetables changed after the destinations were checked. Check again.'),
      'placement' => $this->t('The placement changed after the destinations were checked. Check again.'),
      'same' => $this->t('The placement is already there.'),
      'undo' => $this->t('Only the last move of a placement can be undone, back to where it was before that move.'),
      'mode' => $this->t('Moving placements needs the workspace mode of the engine: in the two-database mode every run deletes the placements of its scope first.'),
      'access' => $this->t('You may not move this placement.'),
      'owner' => $this->t('The plan has no owner.'),
      'invalid' => $this->t('The move is not valid: @message', $a),
      default => $this->t('The move was refused.'),
    };
  }

  /**
   * The text of a reason or warning code of a check cell.
   */
  public function label(string $code): TranslatableMarkup {
    return match ($code) {
      'overflow' => $this->t('the activity would end after the last slot of the day'),
      'period' => $this->t('the period is restricted then'),
      'activity_type' => $this->t('its activity type is restricted then'),
      'subject' => $this->t('its subject is restricted then'),
      'cohort' => $this->t('a cohort of its group is busy or restricted then'),
      'teacher' => $this->t('its teacher is busy or restricted then'),
      'room' => $this->t('its room is busy or restricted then'),
      'resource' => $this->t('one of its other resources is busy or restricted then'),
      'teacher_load' => $this->t("over the teacher's daily load limit"),
      'cohort_load' => $this->t("over a cohort's daily load limit"),
      'order' => $this->t("outside the days the subject's order allows"),
      default => $this->t('not allowed'),
    };
  }

  /**
   * Refuses unless the site is in workspace mode.
   */
  private function assertAvailable(): void {
    if (!$this->isAvailable()) {
      throw new PlacementModificationException('mode');
    }
  }

  /**
   * Refuses unless the account may edit the placement.
   */
  private function assertAccess(Placement $placement, AccountInterface $account): void {
    if (!$placement->access('update', $account)) {
      throw new PlacementModificationException('access');
    }
  }

  /**
   * Validates and saves the placement and the change as the plan's creator.
   */
  private function saveAsCreator(Placement $placement, PlacementChange $change): void {
    $creator = $this->entityTypeManager->getStorage('user')->load((int) $placement->getCreatorId());
    if (!$creator instanceof UserInterface) {
      throw new PlacementModificationException('owner');
    }
    $this->accountSwitcher->switchTo($creator);
    try {
      foreach ([$placement, $change] as $record) {
        $violations = $record->validate();
        if ($violations->count() > 0) {
          throw new PlacementModificationException('invalid', ['@message' => strip_tags((string) $violations->get(0)->getMessage())]);
        }
      }
      $placement->save();
      $change->set('placement', $placement->id());
      $change->save();
    }
    finally {
      $this->accountSwitcher->switchBack();
    }
  }

}
