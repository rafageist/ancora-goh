<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Engine\PublicationException;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\user\UserInterface;

/**
 * Maps one engine placement row ("actividad") to placement values (#95, #96).
 *
 * Shared by the publication of a workspace run (PlacementPublisher) and the
 * import of a two-database site's placements (TwoDatabaseImport). The engine
 * ids are mapped back to the records: the entity id is the numbered part of
 * the id (LegacyRows::engineId()).
 *
 * Checks (PublicationException::INVALID): the template belongs to the
 * period; the group, teacher and room exist and belong to the plan's creator
 * (D1-D3); the creador of the row is that creator; the day and the first
 * slot are inside the period; a teacher and a room are present when the
 * template requires them. Invariants of the timetable as a whole
 * (collisions, overflow) are not checked: legacy keeps what generar() placed
 * (D12).
 */
final class PlacementMapper {

  /**
   * The entity types a placement references (checked on validation).
   */
  private const REFERENCED = [
    'ancora_plan',
    'ancora_period',
    'ancora_activity_template',
    'ancora_student_group',
    'ancora_teacher',
    'ancora_room',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AccountSwitcherInterface $accountSwitcher,
  ) {}

  /**
   * Warms, outside any transaction, what validating as a user computes.
   *
   * Validating a placement runs one access-checked entity query per
   * reference. Their access alter (AcademicQueryAccess) computes the user's
   * permissions, which loads the roles and writes them to the caches when
   * they are cold, for example after a cache rebuild. Inside a transaction,
   * the pgsql driver runs each SELECT in a savepoint named
   * "mimic_implicit_commit", and a cache write from within it fails ("a
   * transaction named mimic_implicit_commit is already in use"). Running
   * the same queries first, as the same user, fills the caches (#96).
   */
  public function warmAccess(UserInterface $user): void {
    $this->accountSwitcher->switchTo($user);
    try {
      foreach (self::REFERENCED as $entityTypeId) {
        $this->entityTypeManager->getStorage($entityTypeId)->getQuery()->accessCheck(TRUE)->range(0, 1)->execute();
      }
    }
    finally {
      $this->accountSwitcher->switchBack();
    }
  }

  /**
   * Maps one row to placement values (without the job), with the checks.
   *
   * @param array $row
   *   The engine row: id, plantilla, grupo, profesor, lugar, dia, turno,
   *   momento_de_colocacion, creador.
   * @param \Drupal\ancora_academic\Entity\Period $period
   *   The period the placement must belong to.
   * @param int $creator
   *   The user id of the plan's creator.
   * @param string $what
   *   How errors name the row, e.g. "Placement 3".
   *
   * @throws \Drupal\ancora_engine\Engine\PublicationException
   */
  public function values(array $row, Period $period, int $creator, string $what): array {
    $template = $this->template($row['plantilla'] ?? NULL);
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
   * The activity template of a numbered engine id, or NULL.
   */
  public function template(?string $engineId): ?ActivityTemplate {
    $template = $this->record('ancora_activity_template', $engineId);
    return $template instanceof ActivityTemplate ? $template : NULL;
  }

  /**
   * The record of a numbered engine id, or NULL.
   */
  private function record(string $entityTypeId, ?string $engineId): ?AcademicEntityInterface {
    if ($engineId === NULL || !preg_match('/^([0-9]{10})' . preg_quote(LegacyRows::ID_SEPARATOR, '/') . '/', $engineId, $m)) {
      return NULL;
    }
    // Entity ids are PostgreSQL integers: ten digits may not fit.
    if ((int) $m[1] > 2147483647) {
      return NULL;
    }
    $entity = $this->entityTypeManager->getStorage($entityTypeId)->load((int) $m[1]);
    return $entity instanceof AcademicEntityInterface ? $entity : NULL;
  }

}
