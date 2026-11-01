<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Workspace;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserDataInterface;

/**
 * The last plan an account opened in the workspace, for "Resume" (#141).
 *
 * Stored with Drupal's user.data service under this module, per account
 * and per site (each organization site has its own database). It holds two
 * ids only: the plan and the period. Nothing academic is written. Access is
 * checked again on every read: a plan that was deleted, or that the
 * account may no longer view, is not offered (and its record is dropped);
 * a period that no longer belongs to the plan is ignored.
 */
final class ResumeStore {

  /**
   * The user.data name.
   */
  public const NAME = 'workspace_last_plan';

  public function __construct(
    private readonly UserDataInterface $userData,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Records the plan and period an authenticated account opened.
   */
  public function record(AccountInterface $account, Plan $plan, ?Period $period): void {
    if ($account->isAnonymous()) {
      return;
    }
    $value = ['plan' => (int) $plan->id(), 'period' => $period === NULL ? NULL : (int) $period->id()];
    if ($this->userData->get('ancora_scheduling', (int) $account->id(), self::NAME) !== $value) {
      $this->userData->set('ancora_scheduling', (int) $account->id(), self::NAME, $value);
    }
  }

  /**
   * The plan and period to resume, revalidated, or NULL.
   *
   * @return array{plan: \Drupal\ancora_academic\Entity\Plan, period: ?\Drupal\ancora_academic\Entity\Period}|null
   *   The plan the account may still view, and the period when it still
   *   belongs to that plan.
   */
  public function resume(AccountInterface $account): ?array {
    if ($account->isAnonymous()) {
      return NULL;
    }
    $value = $this->userData->get('ancora_scheduling', (int) $account->id(), self::NAME);
    if (!is_array($value) || !isset($value['plan'])) {
      return NULL;
    }
    $plan = $this->entityTypeManager->getStorage('ancora_plan')->load((int) $value['plan']);
    if (!$plan instanceof Plan || !$plan->access('view', $account)) {
      $this->userData->delete('ancora_scheduling', (int) $account->id(), self::NAME);
      return NULL;
    }
    $period = isset($value['period']) ? $this->entityTypeManager->getStorage('ancora_period')->load((int) $value['period']) : NULL;
    if (!$period instanceof Period || (int) $period->get('plan')->target_id !== (int) $plan->id()) {
      $period = NULL;
    }
    return ['plan' => $plan, 'period' => $period];
  }

}
