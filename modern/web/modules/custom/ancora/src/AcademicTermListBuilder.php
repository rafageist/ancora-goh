<?php

declare(strict_types=1);

namespace Drupal\ancora;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Administrative list of academic terms, ordered by first day.
 */
final class AcademicTermListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getEntityListQuery(): QueryInterface {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort('starts_on')
      ->sort('id');
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    return [
      'label' => $this->t('Name'),
      'code' => $this->t('Code'),
      'starts_on' => $this->t('First day'),
      'ends_on' => $this->t('Last day'),
    ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    assert($entity instanceof AcademicTermInterface);
    return [
      'label' => $entity->toLink(),
      'code' => $entity->getCode(),
      'starts_on' => $entity->getStartDate(),
      'ends_on' => $entity->getEndDate(),
    ] + parent::buildRow($entity);
  }

}
