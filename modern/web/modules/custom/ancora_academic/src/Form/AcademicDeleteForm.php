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
 * A record that other academic records still reference is not deleted: the
 * form names the dependents and offers no delete button. The entity refuses
 * the deletion as well (AcademicEntityBase::preDelete), whatever the caller.
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
        '#markup' => $this->t('%label cannot be deleted because it is still used by @dependents. Delete or change those records first.', [
          '%label' => $this->entity->label(),
          '@dependents' => $summary,
        ]),
      ];
      $form['actions']['submit']['#access'] = FALSE;
      $form['#cache']['max-age'] = 0;
    }
    return $form;
  }

}
