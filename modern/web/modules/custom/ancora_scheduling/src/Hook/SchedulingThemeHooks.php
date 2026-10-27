<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme and form hook implementations for the Áncora scheduling module.
 */
final class SchedulingThemeHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'ancora_scheduling_timetable' => [
        'variables' => [
          'period' => NULL,
          'period_id' => NULL,
          'periods' => [],
          'filters' => [],
          'filtered' => FALSE,
          'reset_url' => NULL,
          'scopes' => [],
          'request_url' => NULL,
          'plan_url' => NULL,
          'explorer_url' => NULL,
        ],
      ],
      'ancora_workspace_welcome' => [
        'variables' => [
          'login_url' => NULL,
          'no_access' => FALSE,
        ],
      ],
      'ancora_workspace_landing' => [
        'variables' => [
          'plans' => NULL,
          'create' => NULL,
          'getting_started' => NULL,
          'catalogues' => [],
          'quick_links' => NULL,
          'runs' => NULL,
        ],
      ],
      'ancora_workspace_plan' => [
        'variables' => [
          'facts' => [],
          'actions' => NULL,
          'periods' => NULL,
          'records' => [],
          'catalogues' => [],
          'runs' => NULL,
        ],
      ],
    ];
  }

  /**
   * Implements hook_form_FORM_ID_alter() for the user login form.
   *
   * After logging in, a planner arrives at the planning overview instead of
   * the account page. A destination in the URL still wins.
   */
  #[Hook('form_user_login_form_alter')]
  public function userLoginFormAlter(array &$form, FormStateInterface $form_state): void {
    $form['#submit'][] = [self::class, 'redirectToWorkspace'];
  }

  /**
   * Submit handler: redirects to the planning overview.
   */
  public static function redirectToWorkspace(array &$form, FormStateInterface $form_state): void {
    if ($form_state->get('uid')) {
      $form_state->setRedirect('ancora_scheduling.workspace');
    }
  }

}
