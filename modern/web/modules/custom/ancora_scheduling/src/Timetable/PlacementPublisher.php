<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_engine\Engine\JobOutputInterface;
use Drupal\ancora_engine\Engine\PublicationException;
use Drupal\ancora_engine\Engine\RunResult;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Entity\Placement;
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
 *    the engine ids are mapped back to the records and checked
 *    (PlacementMapper), and the record is created and validated through the
 *    Entity API.
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
  public const LOCK = 2026095;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
    private readonly JobRepository $jobs,
    private readonly WorkspaceSync $input,
    private readonly LoggerInterface $logger,
    private readonly AccountSwitcherInterface $accountSwitcher,
    private readonly PlacementMapper $mapper,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function publish(Job $job, RunResult $run): array {
    $source = $job->source;
    if ($source === NULL) {
      throw new PublicationException(PublicationException::INVALID, 'Only a job with a source can be published.');
    }
    $this->warm($source->periodId);
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
        $values = $this->mapper->values($row, $period, $creator, sprintf('Placement %d', $i + 1));
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
   * Warms the caches the validation needs, before the transaction.
   *
   * PlacementMapper::warmAccess(). Nothing is checked here: the
   * publication checks everything again in its transaction.
   */
  private function warm(int $periodId): void {
    $period = $this->entityTypeManager->getStorage('ancora_period')->load($periodId);
    $owner = $period instanceof Period ? $this->entityTypeManager->getStorage('user')->load((int) $period->getCreatorId()) : NULL;
    if ($owner instanceof UserInterface) {
      $this->mapper->warmAccess($owner);
    }
  }

}
