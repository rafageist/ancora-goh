<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Add and edit form of every academic entity type.
 *
 * Validation lives in the field and entity constraints (required fields,
 * ranges, codes unique in the plan, references within the same plan, groups
 * eligible for a template...), so it applies equally to forms, Drush, the
 * fixture loader and any future API. A new record gets its plan from the
 * ?plan=<ID> query argument, or the only plan when there is just one.
 */
final class AcademicEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $entity = $this->entity;
    if ($entity->isNew() && $entity->hasField('plan') && $entity->get('plan')->isEmpty() && !$form_state->isRebuilding()) {
      $plan_id = $this->defaultPlanId();
      if ($plan_id !== NULL) {
        $entity->set('plan', $plan_id);
      }
    }
    return parent::form($form, $form_state);
  }

  /**
   * Returns the plan a new record should default to, if any.
   */
  private function defaultPlanId(): ?int {
    $storage = $this->entityTypeManager->getStorage('ancora_plan');
    $requested = $this->getRequest()->query->get('plan');
    if (is_string($requested) && ctype_digit($requested) && $storage->load($requested)) {
      return (int) $requested;
    }
    $ids = $storage->getQuery()->accessCheck(TRUE)->range(0, 2)->execute();
    return count($ids) === 1 ? (int) reset($ids) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $status = parent::save($form, $form_state);
    $entity = $this->entity;
    assert($entity instanceof AcademicEntityInterface);
    $arguments = [
      '@type' => $entity->getEntityType()->getSingularLabel(),
      '%label' => $entity->label(),
    ];
    $this->messenger()->addStatus($status === SAVED_NEW
      ? $this->t('Created the @type %label.', $arguments)
      : $this->t('Updated the @type %label.', $arguments));
    $form_state->setRedirectUrl($entity->toUrl('collection'));
    return $status;
  }

}
