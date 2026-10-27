<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Drush\Commands;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserInterface;
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
    private readonly Connection $database,
  ) {
    parent::__construct();
  }

  /**
   * Loads the synthetic dataset of dev/e2e/fixture.sql through the Entity API.
   */
  #[CLI\Command(name: 'ancora-academic:load-fixture', aliases: ['aa-fixture'])]
  #[CLI\Option(name: 'fixture-only', description: 'Only the rows of fixture.sql, without the teacher the HTTP harness adds for subject B.')]
  #[CLI\Option(name: 'owner', description: 'The account name of the user who owns the records (default: the first account).')]
  #[CLI\Usage(name: 'drush ancora-academic:load-fixture --owner=planner', description: 'Create plan P, owned by "planner", with everything the engine needs to place 8 of 8 activities.')]
  public function loadFixture(array $options = ['fixture-only' => FALSE, 'owner' => NULL]): int {
    $owner = NULL;
    if ($options['owner'] !== NULL) {
      $owner = $this->account((string) $options['owner']);
      if ($owner === NULL) {
        return self::EXIT_FAILURE;
      }
    }
    $plan = $this->fixture->load(!$options['fixture-only'], $owner);
    $this->logger()->success(dt('Created plan @code (ID @id) with the synthetic dataset, owned by user @uid.', [
      '@code' => $plan->getCode(),
      '@id' => $plan->id(),
      '@uid' => $plan->getOwnerId(),
    ]));
    return self::EXIT_SUCCESS;
  }

  /**
   * Prints the export of a plan as JSON.
   */
  #[CLI\Command(name: 'ancora-academic:export', aliases: ['aa-export'])]
  #[CLI\Argument(name: 'plan', description: 'The plan code.')]
  #[CLI\Option(name: 'owner', description: 'The account name of the plan owner, when several users have a plan with this code.')]
  #[CLI\Option(name: 'period', description: 'Narrow the export to the period with this code.')]
  #[CLI\Option(name: 'legacy', description: 'Print the rows of the legacy engine tables (ids are the codes) instead of the export.')]
  #[CLI\Usage(name: 'drush ancora-academic:export P --period=PER', description: 'Export plan P, period PER.')]
  public function export(string $plan, array $options = ['owner' => NULL, 'period' => NULL, 'legacy' => FALSE]): int {
    $properties = ['code' => $plan];
    if ($options['owner'] !== NULL) {
      $owner = $this->account((string) $options['owner']);
      if ($owner === NULL) {
        return self::EXIT_FAILURE;
      }
      $properties['uid'] = $owner->id();
    }
    $plans = $this->entityTypeManager->getStorage('ancora_plan')->loadByProperties($properties);
    if (count($plans) > 1) {
      $this->logger()->error(dt('Several users have a plan with code @code; name one with --owner.', ['@code' => $plan]));
      return self::EXIT_FAILURE;
    }
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

  /**
   * Gives every plan and catalogue record of one user to another user.
   *
   * The records of the plans follow their plan. This is an operator tool
   * (for example after the update of #70 gave the existing records to the
   * first account); the legacy application had no such screen. It refuses,
   * and changes nothing, when the new owner already has a record of the
   * same type and code.
   */
  #[CLI\Command(name: 'ancora-academic:reassign-owner')]
  #[CLI\Argument(name: 'from', description: 'The account name of the current owner.')]
  #[CLI\Argument(name: 'to', description: 'The account name of the new owner.')]
  #[CLI\Usage(name: 'drush ancora-academic:reassign-owner admin planner', description: 'Give the records of "admin" to "planner".')]
  public function reassignOwner(string $from, string $to): int {
    $old = $this->account($from);
    $new = $this->account($to);
    if ($old === NULL || $new === NULL) {
      return self::EXIT_FAILURE;
    }
    $transaction = $this->database->startTransaction();
    $moved = 0;
    try {
      foreach ([...AcademicEntityTypes::OWNED, ...AcademicEntityTypes::OWNED_GRID] as $entity_type_id) {
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
        $ids = $storage->getQuery()->accessCheck(FALSE)->condition('uid', $old->id())->execute();
        foreach ($storage->loadMultiple($ids) as $entity) {
          $entity->set('uid', $new->id());
          $violations = $entity->validate();
          if ($violations->count() > 0) {
            throw new \RuntimeException(sprintf('%s %s: %s', $entity_type_id, $entity->label(), strip_tags((string) $violations->get(0)->getMessage())));
          }
          $entity->save();
          $moved++;
        }
      }
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      $this->logger()->error(dt('Nothing was reassigned: @message', ['@message' => $e->getMessage()]));
      return self::EXIT_FAILURE;
    }
    $this->logger()->success(dt('@count records of @from now belong to @to.', [
      '@count' => $moved,
      '@from' => $from,
      '@to' => $to,
    ]));
    return self::EXIT_SUCCESS;
  }

  /**
   * Loads an account by name, or logs an error.
   */
  private function account(string $name): ?UserInterface {
    $accounts = $this->entityTypeManager->getStorage('user')->loadByProperties(['name' => $name]);
    $account = reset($accounts);
    if (!$account instanceof UserInterface) {
      $this->logger()->error(dt('No account named @name.', ['@name' => $name]));
      return NULL;
    }
    return $account;
  }

}
