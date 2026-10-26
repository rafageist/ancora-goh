<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Drush\Commands;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

/**
 * Drush commands of the Áncora academic module.
 */
final class AcademicCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly SyntheticFixture $fixture,
    private readonly AcademicExport $export,
    private readonly LegacyRows $legacyRows,
  ) {
    parent::__construct();
  }

  /**
   * Loads the synthetic dataset of dev/e2e/fixture.sql through the Entity API.
   */
  #[CLI\Command(name: 'ancora-academic:load-fixture', aliases: ['aa-fixture'])]
  #[CLI\Option(name: 'fixture-only', description: 'Only the rows of fixture.sql, without the teacher the HTTP harness adds for subject B.')]
  #[CLI\Usage(name: 'drush ancora-academic:load-fixture', description: 'Create plan P with everything the engine needs to place 8 of 8 activities.')]
  public function loadFixture(array $options = ['fixture-only' => FALSE]): int {
    $plan = $this->fixture->load(!$options['fixture-only']);
    $this->logger()->success(dt('Created plan @code (ID @id) with the synthetic dataset.', [
      '@code' => $plan->getCode(),
      '@id' => $plan->id(),
    ]));
    return self::EXIT_SUCCESS;
  }

  /**
   * Prints the export of a plan as JSON.
   */
  #[CLI\Command(name: 'ancora-academic:export', aliases: ['aa-export'])]
  #[CLI\Argument(name: 'plan', description: 'The plan code.')]
  #[CLI\Option(name: 'period', description: 'Narrow the export to the period with this code.')]
  #[CLI\Option(name: 'legacy', description: 'Print the rows of the legacy engine tables instead of the export.')]
  #[CLI\Usage(name: 'drush ancora-academic:export P --period=PER', description: 'Export plan P, period PER.')]
  public function export(string $plan, array $options = ['period' => NULL, 'legacy' => FALSE]): int {
    $plans = $this->entityTypeManager->getStorage('ancora_plan')->loadByProperties(['code' => $plan]);
    $plan_entity = reset($plans);
    if (!$plan_entity instanceof Plan) {
      $this->logger()->error(dt('No plan with code @code.', ['@code' => $plan]));
      return self::EXIT_FAILURE;
    }
    $period = NULL;
    if ($options['period'] !== NULL) {
      $periods = $this->entityTypeManager->getStorage('ancora_period')->loadByProperties([
        'plan' => $plan_entity->id(),
        'code' => $options['period'],
      ]);
      $period = reset($periods);
      if (!$period instanceof Period) {
        $this->logger()->error(dt('Plan @plan has no period with code @code.', [
          '@plan' => $plan,
          '@code' => $options['period'],
        ]));
        return self::EXIT_FAILURE;
      }
    }
    $data = $this->export->export($plan_entity, $period);
    if ($options['legacy']) {
      $data = $this->legacyRows->build($data);
    }
    $this->output()->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    return self::EXIT_SUCCESS;
  }

}
