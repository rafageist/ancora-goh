<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control for every academic entity type.
 *
 * - view: "view academic data";
 * - create, update and delete: "edit academic data";
 * - creating and deleting plans: "administer academic data" only;
 * - "administer academic data" (the admin permission) allows everything.
 *
 * Deleting a record that is still referenced is refused by the entity itself
 * (AcademicEntityBase::preDelete), not by access, so that the delete form
 * can explain why. The tenant boundary is the site (#72).
 */
final class AcademicAccessControlHandler extends EntityAccessControlHandler {

  public const VIEW = 'view academic data';
  public const EDIT = 'edit academic data';
  public const ADMINISTER = 'administer academic data';

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    $viewers = [self::VIEW, self::ADMINISTER];
    $editors = [self::EDIT, self::ADMINISTER];
    return match (TRUE) {
      $operation === 'view', $operation === 'view label' => AccessResult::allowedIfHasPermissions($account, $viewers, 'OR'),
      $operation === 'delete' && $entity->getEntityTypeId() === 'ancora_plan' => AccessResult::allowedIfHasPermission($account, self::ADMINISTER),
      $operation === 'update', $operation === 'delete' => AccessResult::allowedIfHasPermissions($account, $editors, 'OR'),
      default => AccessResult::neutral()->cachePerPermissions(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    if ($this->entityTypeId === 'ancora_plan') {
      return AccessResult::allowedIfHasPermission($account, self::ADMINISTER);
    }
    return AccessResult::allowedIfHasPermissions($account, [self::EDIT, self::ADMINISTER], 'OR');
  }

}
