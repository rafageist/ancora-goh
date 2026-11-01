<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Hook;

use Drupal\ancora_engine\EngineSettings;
use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Status report entries of the engine worker.
 */
final class AncoraEngineRequirements {

  use StringTranslationTrait;

  public function __construct(
    private readonly EngineSettings $settings,
  ) {}

  /**
   * Implements hook_runtime_requirements().
   */
  #[Hook('runtime_requirements')]
  public function runtime(): array {
    $loaded = extension_loaded('pgsql');
    $configured = $this->settings->isConfigured();
    return [
      'ancora_engine_pgsql' => [
        'title' => $this->t('Áncora engine: PHP pgsql extension'),
        'value' => $loaded ? $this->t('Loaded') : $this->t('Missing'),
        'description' => $loaded ? '' : $this->t('The engine worker runs generar() through the PHP pgsql extension (asynchronous query, heartbeat and cancellation).'),
        'severity' => $loaded ? RequirementSeverity::OK : RequirementSeverity::Error,
      ],
      'ancora_engine_database' => [
        'title' => $this->t('Áncora engine: engine database'),
        'value' => $configured ? $this->t('Configured') : $this->t('Not configured'),
        'description' => $configured ? '' : $this->t("Set \$settings['ancora_engine']['database'] or the ANCORA_ENGINE_DB_* environment variables."),
        'severity' => $configured ? RequirementSeverity::OK : RequirementSeverity::Warning,
      ],
    ];
  }

}
