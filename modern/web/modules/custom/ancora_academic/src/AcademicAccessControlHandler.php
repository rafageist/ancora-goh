<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control for every academic entity type: the legacy "creador" rule.
 *
 * Legacy (docs/modernization/legacy-parity-inventory.md PAR-001, PAR-002):
 * every record belongs to the user who created it, and every data entry list
 * loads "creador = $_SESSION['user']", so a user sees and edits only their
 * own plans, catalogues and the records of their plans. No role sees other
 * users' records in those lists, not even the seeded superuser. Here:
 *
 * - view, update, delete: the permission ("view academic data", "edit
 *   academic data") and being the record's creator (getCreatorId(): the
 *   owner of a plan or catalogue record, the plan's owner otherwise);
 * - teachers and rooms are visible (view, view label) to every user with
 *   "view academic data": the legacy timetable explorer lists every user's
 *   teachers and rooms (custom_getui_ExploradorListProfesores.php,
 *   ..ExploradorListLugares.php use getAll(); quirk Q-23, open question
 *   A-5). Editing them stays the owner's;
 * - create: "edit academic data". Any data entry user creates their own
 *   plans, as any legacy user allowed to run add_Planificacion did;
 * - subject breakdowns (legacy desglose) are kept by BreakdownMaintainer,
 *   as the legacy triggers and cascades keep them: a user may view and
 *   update (the options) those of their own plans, and never create or
 *   delete one (the legacy UI has no such action);
 * - "administer academic data" (restricted) allows everything. It has no
 *   legacy counterpart in the screens; it is the operator's way to reach
 *   every record of the site (for example to reassign an owner).
 *
 * The lists, the reference widgets and Views are filtered the same way by
 * AcademicQueryAccess. Deleting a record that is still referenced is refused
 * by the entity itself (AcademicEntityBase::preDelete), not by access. The
 * tenant boundary is the site (#72).
 */
final class AcademicAccessControlHandler extends EntityAccessControlHandler {

  public const VIEW = 'view academic data';
  public const EDIT = 'edit academic data';
  public const ADMINISTER = 'administer academic data';

  /**
   * Types whose records every viewer may see (quirk Q-23).
   */
  public const VISIBLE_TO_ALL = ['ancora_teacher', 'ancora_room'];

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    assert($entity instanceof AcademicEntityInterface);
    if ($account->hasPermission(self::ADMINISTER)) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    if (in_array($entity->getEntityTypeId(), AcademicEntityTypes::DERIVED, TRUE) && $operation === 'delete') {
      return AccessResult::neutral()->cachePerPermissions();
    }
    $permission = match ($operation) {
      'view', 'view label' => self::VIEW,
      'update', 'delete' => self::EDIT,
      default => NULL,
    };
    if ($permission === NULL) {
      return AccessResult::neutral()->cachePerPermissions();
    }
    $allowed = AccessResult::allowedIfHasPermission($account, $permission);
    if ($permission === self::VIEW && in_array($entity->getEntityTypeId(), self::VISIBLE_TO_ALL, TRUE)) {
      return $allowed;
    }
    $own = AccessResult::allowedIf($entity->getCreatorId() !== NULL && $entity->getCreatorId() === (int) $account->id())
      ->cachePerUser()
      ->addCacheableDependency($entity);
    if ($entity->hasField('plan') && ($plan = $entity->get('plan')->entity) !== NULL) {
      $own->addCacheableDependency($plan);
    }
    return $allowed->andIf($own);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    if (in_array($this->entityTypeId, AcademicEntityTypes::DERIVED, TRUE)) {
      return AccessResult::neutral();
    }
    return AccessResult::allowedIfHasPermissions($account, [self::EDIT, self::ADMINISTER], 'OR');
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, ?FieldItemListInterface $items = NULL): AccessResultInterface {
    // The owner is set when the record is created; only an administrator
    // may change it.
    if ($operation === 'edit' && $field_definition->getName() === 'uid') {
      return AccessResult::allowedIfHasPermission($account, self::ADMINISTER);
    }
    return parent::checkFieldAccess($operation, $field_definition, $account, $items);
  }

}
