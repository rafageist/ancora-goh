<?php

declare(strict_types=1);

namespace Drupal\ancora\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Add and edit form for academic terms.
 *
 * Validation (required fields, unique code, last day not before first day)
 * comes from the entity's field and entity constraints, so it applies equally
 * to forms, Drush and future APIs.
 */
final class AcademicTermForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $status = parent::save($form, $form_state);
    $arguments = ['%label' => $this->entity->label()];
    $this->messenger()->addStatus($status === SAVED_NEW
      ? $this->t('Created the academic term %label.', $arguments)
      : $this->t('Updated the academic term %label.', $arguments));
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $status;
  }

}
