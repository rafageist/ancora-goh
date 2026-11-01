<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Controller\OrderController;
use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\Breakdown;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Administrative list of an academic entity type.
 *
 * The columns come from the entity class (listColumns()). The list can be
 * narrowed to one plan, period or subject with the query arguments
 * ?plan=<ID>, ?period=<ID> and ?subject=<ID> (for the types that have the
 * column). A subject breakdown also shows its activity templates in their
 * order, as the legacy breakdown browser does (desglose_detalles.tpl).
 */
final class AcademicListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected $limit = 100;

  /**
   * The query arguments the list can be narrowed by.
   */
  private const FILTERS = ['plan', 'period', 'subject'];

  /**
   * The records the list is narrowed to: field name => ID.
   *
   * @var array<string, int>
   */
  private array $filters = [];

  /**
   * The entity field manager.
   */
  private EntityFieldManagerInterface $fieldManager;

  /**
   * The activity template storage.
   */
  private EntityStorageInterface $templateStorage;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): static {
    $instance = parent::createInstance($container, $entity_type);
    $instance->fieldManager = $container->get('entity_field.manager');
    $instance->templateStorage = $container->get('entity_type.manager')->getStorage('ancora_activity_template');
    $query = $container->get('request_stack')->getCurrentRequest()?->query;
    foreach (self::FILTERS as $filter) {
      $value = $query?->get($filter);
      if (is_string($value) && ctype_digit($value)) {
        $instance->filters[$filter] = (int) $value;
      }
    }
    return $instance;
  }

  /**
   * Returns the field names of the columns.
   *
   * @return list<string>
   *   Field names.
   */
  private function columns(): array {
    $class = $this->entityType->getClass();
    assert(is_subclass_of($class, AcademicEntityInterface::class));
    return $class::listColumns();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityListQuery(): QueryInterface {
    $query = $this->getStorage()->getQuery()->accessCheck(TRUE);
    $fields = \array_flip($this->columns());
    foreach ($this->filters as $field => $id) {
      if (isset($fields[$field])) {
        $query->condition($field, $id);
      }
    }
    // Activity types in their scheduling order (the legacy list), D13.
    foreach (['plan', 'priority', 'code', 'subject', 'period', 'template', 'sequence'] as $sort) {
      if (isset($fields[$sort])) {
        $query->sort($sort);
      }
    }
    $query->sort($this->entityType->getKey('id'));
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $definitions = $this->fieldManager->getBaseFieldDefinitions($this->entityTypeId);
    $header = [];
    foreach ($this->columns() as $name) {
      $header[$name] = match (TRUE) {
        $name === 'templates' => $this->t('Activity templates, in order'),
        $name === 'label' && !isset($definitions['label']) => $this->entityType->getLabel(),
        default => $definitions[$name]->getLabel(),
      };
    }
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    assert($entity instanceof AcademicEntityInterface);
    $row = [];
    foreach ($this->columns() as $name) {
      $row[$name] = $name === 'label' ? $entity->toLink() : $this->renderValue($entity, $name);
    }
    return $row + parent::buildRow($entity);
  }

  /**
   * Renders a field as plain text for a table cell.
   */
  private function renderValue(AcademicEntityInterface $entity, string $name): string {
    if ($name === 'templates' && $entity instanceof Breakdown) {
      return $this->templatesOf($entity);
    }
    $items = $entity->get($name);
    if ($items instanceof EntityReferenceFieldItemListInterface) {
      $labels = [];
      foreach ($items->referencedEntities() as $referenced) {
        $labels[] = $referenced instanceof AcademicEntityInterface && $referenced->getCode() !== ''
          ? $referenced->getCode()
          : (string) $referenced->label();
      }
      return implode(', ', $labels);
    }
    if ($items->getFieldDefinition()->getType() === 'boolean') {
      return (string) ($items->value ? $this->t('Yes') : $this->t('No'));
    }
    return implode(', ', array_map('strval', array_column($items->getValue(), 'value')));
  }

  /**
   * The activity templates of a breakdown in their order: "1 Lecture, 2 Lab".
   */
  private function templatesOf(Breakdown $breakdown): string {
    $storage = $this->templateStorage;
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('subject', $breakdown->get('subject')->target_id)
      ->condition('period', $breakdown->get('period')->target_id)
      ->sort('sequence')
      ->execute();
    $labels = [];
    foreach ($storage->loadMultiple($ids) as $template) {
      assert($template instanceof ActivityTemplate);
      $labels[] = $template->getSequence() . ' ' . ($template->getActivityType()?->label() ?? '?');
    }
    return $labels === [] ? (string) $this->t('None') : implode(', ', $labels);
  }

  /**
   * {@inheritdoc}
   *
   * Activity types and templates have the legacy up and down arrows
   * (OrderController, D13).
   */
  protected function getDefaultOperations(EntityInterface $entity): array {
    $operations = parent::getDefaultOperations($entity);
    $type = array_search($entity->getEntityTypeId(), OrderController::TYPES, TRUE);
    if ($type !== FALSE && $entity->access('update')) {
      foreach (['up' => $this->t('Move up'), 'down' => $this->t('Move down')] as $direction => $title) {
        $operations['move_' . $direction] = [
          'title' => $title,
          'weight' => $direction === 'up' ? 20 : 21,
          'url' => $this->ensureDestination(Url::fromRoute('ancora_academic.order.move', [
            'type' => $type,
            'id' => $entity->id(),
            'direction' => $direction,
          ])),
        ];
      }
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]);
    return $build;
  }

}
