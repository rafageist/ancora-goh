<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme hook implementations for the Áncora academic module.
 */
final class AcademicThemeHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'ancora_academic_planning' => [
        'variables' => [
          'intro' => NULL,
          'steps' => [],
        ],
      ],
    ];
  }

}
