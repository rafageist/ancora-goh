<?php

declare(strict_types=1);

namespace Drupal\ancora\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;

/**
 * The platform status page, for operators.
 *
 * The page is rendered through the "ancora_status" theme hook, so the module
 * stays presentation-neutral and the active theme decides the markup (the
 * Áncora theme renders it with its status-card component).
 */
final class StatusController extends ControllerBase {

  public function __construct(
    private readonly Connection $database,
  ) {}

  /**
   * Builds the status page.
   */
  public function status(): array {
    $term_count = $this->entityTypeManager()
      ->getStorage('ancora_academic_term')
      ->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return [
      '#theme' => 'ancora_status',
      '#heading' => $this->t('Áncora platform'),
      '#items' => [
        ['label' => $this->t('Drupal core'), 'value' => \Drupal::VERSION],
        ['label' => $this->t('PHP'), 'value' => PHP_VERSION],
        [
          'label' => $this->t('Database'),
          'value' => $this->database->databaseType() . ' ' . $this->database->version(),
        ],
        ['label' => $this->t('Academic terms'), 'value' => (string) $term_count],
      ],
      '#cache' => [
        'contexts' => ['user.permissions'],
        'tags' => ['ancora_academic_term_list'],
      ],
    ];
  }

}
