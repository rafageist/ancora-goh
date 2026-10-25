<?php

declare(strict_types=1);

namespace Drupal\ancora;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control for academic terms.
 *
 * Viewing needs "view ancora academic terms"; every other operation needs the
 * entity type's admin permission. The tenant boundary is added in #72.
 */
final class AcademicTermAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    if ($operation === 'view') {
      return AccessResult::allowedIfHasPermissions($account, [
        'view ancora academic terms',
        'administer ancora academic terms',
      ], 'OR');
    }
    return parent::checkAccess($entity, $operation, $account);
  }

}
