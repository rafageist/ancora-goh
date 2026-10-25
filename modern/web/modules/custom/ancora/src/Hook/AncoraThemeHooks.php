<?php

declare(strict_types=1);

namespace Drupal\ancora\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme hook implementations for the Áncora module.
 */
final class AncoraThemeHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'ancora_status' => [
        'variables' => [
          'heading' => NULL,
          'items' => [],
        ],
      ],
    ];
  }

}
