<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Engine\JobOutputInterface;
use Drupal\ancora_engine\Engine\PublicationException;
use Drupal\ancora_engine\Engine\RunResult;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\WorkspaceSync;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;

/**
 * Publishes a workspace run's new placements as placement entities (#95).
 *
 * Registered as "ancora_engine.job_output". One transaction of Drupal's
 * connection, serialized for the site by an advisory lock:
 *
 * 1. fence: the job must still be running in this attempt (a heartbeat in
 *    the same transaction), otherwise nothing is published;
 * 2. stale input: the site rows and stored placements must still have the
 *    version recorded at the snapshot (WorkspaceSync::currentVersion());
 *    otherwise the result was computed from other data and nothing is
 *    published;
 * 3. for every new placement, in the order the run made them: an existing
 *    record with the same engine id is a replay and is skipped; otherwise
 *    the engine ids are mapped back to the records (the entity id is the
 *    numbered part of the id, LegacyRows::engineId()), checked, and the
 *    record is created and validated through the Entity API.
 *
 * Checks (refuse, nothing published): the template belongs to the job's
 * period; the group, teacher and room exist and belong to the plan's
 * creator (D1-D3); the creador of the row is that creator; the day and the
 * first slot are inside the period; a teacher and a room are present when
 * the template requires them; no other placement has the same template and
 * group. Invariants of the timetable as a whole (collisions, overflow) are
 * NOT refused: legacy keeps what generar() placed (D12, recorded as a
 * proposal in docs/modernization/placement-promotion.md).
 *
 * The records are validated as the plan's creator (the account is switched
 * for the publication): the reference checks of the Entity API then apply
 * that user's access, the legacy "creador" rule.
 *
 * Any error rolls the whole transaction back: no record of the run exists.
 * Placements of earlier runs are never changed or deleted here.
 */
final class PlacementPublisher implements JobOutputInterface {

  /**
   * The advisory lock of the publication (one at a time per site).
   */
  private const LOCK = 2026095;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
    private readonly JobRepository $jobs,
    private readonly WorkspaceSync $input,
    private readonly LoggerInterface $logger,
    private readonly AccountSwitcherInterface $accountSwitcher,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function publish(Job $job, RunResult $run): array {
    $source = $job->source;
    if ($source === NULL) {
      throw new PublicationException(PublicationException::INVALID, 'Only a job with a source can be published.');
    }
    $transaction = $this->database->startTransaction();
    $switched = FALSE;
    try {
      $this->database->query('SELECT pg_advisory_xact_lock(:key)', [':key' => self::LOCK]);
      if (!$this->jobs->heartbeat($job)) {
        throw new PublicationException(PublicationException::FENCED, 'The run is no longer the current attempt of the job; nothing was published.');
      }
      if ($this->input->currentVersion() !== ($run->input['site_version'] ?? NULL)) {
        throw new PublicationException(PublicationException::STALE, 'The academic data or the stored placements changed after the input snapshot; nothing was published. Request the timetable again.');
      }
      $storage = $this->entityTypeManager->getStorage('ancora_placement');
      $period = $this->entityTypeManager->getStorage('ancora_period')->load($source->periodId);
      if (!$period instanceof Period) {
        throw new PublicationException(PublicationException::INVALID, 'The period of the job no longer exists.');
      }
      $creator = (int) $period->getCreatorId();
      $owner = $this->entityTypeManager->getStorage('user')->load($creator);
      if (!$owner instanceof UserInterface || !$owner->isActive()) {
        throw new PublicationException(PublicationException::INVALID, 'The creator of the plan is not an active user.');
      }
      $this->accountSwitcher->switchTo($owner);
      $switched = TRUE;
      $published = 0;
      $replayed = 0;
      foreach ($run->output as $i => $row) {
        $existing = $storage->getQuery()->accessCheck(FALSE)->condition('engine_id', (string) $row['id'])->execute();
        if ($existing !== []) {
          $replayed++;
          continue;
        }
        $values = $this->values($row, $period, $creator, $i);
        $duplicate = $storage->getQuery()->accessCheck(FALSE)
          ->condition('template', $values['template'])
          ->condition('student_group', $values['student_group'])
          ->count()->execute();
        if ($duplicate > 0) {
          throw new PublicationException(PublicationException::INVALID, sprintf('Placement %d: the template is already placed for this group.', $i + 1));
        }
        $placement = $storage->create($values + ['job' => $job->id]);
        assert($placement instanceof Placement);
        $violations = $placement->validate();
        if ($violations->count() > 0) {
          throw new PublicationException(PublicationException::INVALID, sprintf('Placement %d is invalid: %s', $i + 1, $violations->get(0)->getMessage()));
        }
        $placement->save();
        $published++;
      }
    }
    catch (PublicationException $e) {
      $transaction->rollBack();
      throw $e;
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      $this->logger->error('Job @id: publishing its placements failed: @message', [
        '@id' => $job->id,
        '@message' => $e->getMessage(),
      ]);
      throw new PublicationException(PublicationException::FAILED, 'Saving the placements failed; nothing was published.', $e);
    }
    finally {
      if ($switched) {
        $this->accountSwitcher->switchBack();
      }
    }
    unset($transaction);
    return ['published' => $published, 'already_published' => $replayed];
  }

  /**
   * Maps one output row to placement values, with the checks.
   *
   * @throws \Drupal\ancora_engine\Engine\PublicationException
   */
  private function values(array $row, Period $period, int $creator, int $i): array {
    $what = sprintf('Placement %d', $i + 1);
    $template = $this->record('ancora_activity_template', $row['plantilla'] ?? NULL);
    if (!$template instanceof ActivityTemplate || (int) $template->get('period')->target_id !== (int) $period->id()) {
      throw new PublicationException(PublicationException::INVALID, "$what: the template is not a template of the job's period.");
    }
    $group = $this->record('ancora_student_group', $row['grupo'] ?? NULL);
    $teacher = ($row['profesor'] ?? NULL) === NULL ? NULL : $this->record('ancora_teacher', $row['profesor']);
    $room = ($row['lugar'] ?? NULL) === NULL ? NULL : $this->record('ancora_room', $row['lugar']);
    foreach (['group' => $group, 'teacher' => $teacher, 'room' => $room] as $name => $record) {
      $given = [
        'group' => $row['grupo'] ?? NULL,
        'teacher' => $row['profesor'] ?? NULL,
        'room' => $row['lugar'] ?? NULL,
      ][$name];
      if ($given !== NULL && (!$record instanceof AcademicEntityInterface || (int) $record->getCreatorId() !== $creator)) {
        throw new PublicationException(PublicationException::INVALID, "$what: the $name does not exist or belongs to another user.");
      }
    }
    if ((int) $template->getCreatorId() !== $creator || ($row['creador'] ?? NULL) !== ScopeMapper::creatorOf($creator)) {
      throw new PublicationException(PublicationException::INVALID, "$what: it does not belong to the plan's creator.");
    }
    if ((bool) $template->get('requires_teacher')->value && $teacher === NULL) {
      throw new PublicationException(PublicationException::INVALID, "$what: the template requires a teacher.");
    }
    if ((bool) $template->get('requires_room')->value && $room === NULL) {
      throw new PublicationException(PublicationException::INVALID, "$what: the template requires a room.");
    }
    $day = (int) ($row['dia'] ?? 0);
    $slot = (int) ($row['turno'] ?? 0);
    if ($day < 1 || $day > (int) $period->getDays() || $slot < 1 || $slot > (int) $period->getSlotsPerDay()) {
      throw new PublicationException(PublicationException::INVALID, "$what: day $day, slot $slot is outside the period.");
    }
    return [
      'plan' => $period->getPlanId(),
      'period' => $period->id(),
      'template' => $template->id(),
      'student_group' => $group->id(),
      'teacher' => $teacher?->id(),
      'room' => $room?->id(),
      'day' => $day,
      'slot' => $slot,
      'placed_at' => ($row['momento_de_colocacion'] ?? NULL) === NULL ? NULL : (float) $row['momento_de_colocacion'],
      'engine_id' => (string) $row['id'],
    ];
  }

  /**
   * The record of a numbered engine id, or NULL.
   */
  private function record(string $entityTypeId, ?string $engineId): ?AcademicEntityInterface {
    if ($engineId === NULL || !preg_match('/^([0-9]{10})' . preg_quote(LegacyRows::ID_SEPARATOR, '/') . '/', $engineId, $m)) {
      return NULL;
    }
    $entity = $this->entityTypeManager->getStorage($entityTypeId)->load((int) $m[1]);
    return $entity instanceof AcademicEntityInterface ? $entity : NULL;
  }

}
