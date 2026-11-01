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
          'resume' => NULL,
          'plans' => NULL,
          'create' => NULL,
          'getting_started' => NULL,
          'catalogues' => [],
          'quick_links' => NULL,
          'runs' => NULL,
        ],
      ],
      // The integrated planning workspace (#141): the page and its regions.
      'ancora_plan_workspace' => [
        'variables' => [
          'bar' => NULL,
          'nav' => NULL,
          'canvas' => NULL,
          'inspector' => NULL,
          'regions_url' => NULL,
          'status' => NULL,
        ],
      ],
      'ancora_workspace_bar' => [
        'variables' => [
          'plan' => NULL,
          'plans' => [],
          'period' => NULL,
          'periods' => [],
          'scopes' => [],
          'cross' => [],
          'links' => NULL,
        ],
      ],
      'ancora_workspace_nav' => [
        'variables' => [
          'lenses' => [],
          'lens_label' => NULL,
          'records' => [],
          'all' => TRUE,
        ],
      ],
      'ancora_workspace_canvas' => [
        'variables' => [
          'heading' => NULL,
          'empty' => FALSE,
          'message' => NULL,
          'rows' => [],
          'days' => [],
          'template' => 'auto',
          'status' => NULL,
        ],
      ],
      'ancora_workspace_inspector' => [
        'variables' => [
          'selected' => FALSE,
          'title' => NULL,
          'hint' => NULL,
          'summary' => NULL,
          'facts' => [],
          'pivots' => NULL,
          'cross_text' => NULL,
          'conflicts' => [],
          'history' => [],
          'actions' => NULL,
          'close_url' => NULL,
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
   * Implements hook_preprocess_HOOK() for html.
   *
   * The planning workspace (#141) uses the full width of the window.
   */
  #[Hook('preprocess_html')]
  public function preprocessHtml(array &$variables): void {
    if (\Drupal::routeMatch()->getRouteName() === 'ancora_scheduling.plan_workspace') {
      $variables['attributes']['class'][] = 'ancora-is-workspace';
    }
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
