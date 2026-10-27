<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\ReferenceGuard;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Delete form of every academic entity type.
 *
 * A plain confirmation, as the legacy "¿Está seguro?". The delete applies
 * the legacy cascade (AcademicEntityBase::preDelete, D5): the form says
 * which records reference this one; they are deleted with it, or lose the
 * link to it, and placements go with their template, group, teacher or
 * room.
 */
final class AcademicDeleteForm extends ContentEntityDeleteForm {

  /**
   * The reference guard.
   */
  private ReferenceGuard $guard;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);
    $instance->guard = $container->get(ReferenceGuard::class);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $summary = $this->guard->describe($this->guard->dependents($this->entity));
    if ($summary !== '') {
      $form['description'] = [
        '#markup' => $this->t('%label is used by @dependents. Deleting it also deletes them, or removes it from them (a group keeps its other cohorts, a teacher assignment its other groups), with the placements that depend on them. This action cannot be undone.', [
          '%label' => $this->entity->label(),
          '@dependents' => $summary,
        ]),
      ];
      $form['#cache']['max-age'] = 0;
    }
    return $form;
  }

}
