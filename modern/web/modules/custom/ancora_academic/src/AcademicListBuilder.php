<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Administrative list of an academic entity type.
 *
 * The columns come from the entity class (listColumns()). The list can be
 * narrowed to one plan with the query argument ?plan=<plan ID>.
 */
final class AcademicListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected $limit = 100;

  /**
   * The plan the list is narrowed to, if any.
   */
  private ?int $planFilter = NULL;

  /**
   * The entity field manager.
   */
  private EntityFieldManagerInterface $fieldManager;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): static {
    $instance = parent::createInstance($container, $entity_type);
    $instance->fieldManager = $container->get('entity_field.manager');
    $plan = $container->get('request_stack')->getCurrentRequest()?->query->get('plan');
    if (is_string($plan) && ctype_digit($plan)) {
      $instance->planFilter = (int) $plan;
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
    if ($this->planFilter !== NULL && isset($fields['plan'])) {
      $query->condition('plan', $this->planFilter);
    }
    foreach (['plan', 'code', 'subject', 'template', 'sequence'] as $sort) {
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
      $header[$name] = $name === 'label' && !isset($definitions['label'])
        ? $this->entityType->getLabel()
        : $definitions[$name]->getLabel();
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
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]);
    return $build;
  }

}
