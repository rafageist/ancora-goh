<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme hook implementations for the Áncora scheduling module.
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
          'scopes' => [],
          'request_url' => NULL,
        ],
      ],
    ];
  }

}
