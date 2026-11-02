<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\Breakdown;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Add and edit form of every academic entity type.
 *
 * Validation lives in the field and entity constraints (required fields,
 * ranges, unique codes, references to the creator's records and the same
 * plan, groups eligible for a template...), so it applies equally to forms,
 * Drush, the fixture loader and any future API. A new record of a plan gets
 * its plan from the ?plan=<ID> query argument (a plan the user may edit),
 * or the user's only plan. A new activity template of a breakdown
 * (?breakdown=<ID>, a breakdown the user may update) gets its plan, subject
 * and period from the breakdown, locked, as the legacy "+" of the breakdown
 * browser does. A new plan or catalogue record belongs to the
 * user who creates it. The choices of every reference are the user's own
 * records (AcademicQueryAccess).
 */
final class AcademicEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $entity = $this->entity;
    $breakdown = $entity instanceof ActivityTemplate && $entity->isNew() ? $this->requestedBreakdown() : NULL;
    if ($breakdown !== NULL) {
      $entity->set('plan', $breakdown->get('plan')->target_id);
      $entity->set('subject', $breakdown->get('subject')->target_id);
      $entity->set('period', $breakdown->get('period')->target_id);
    }
    if ($entity->isNew() && $entity->hasField('plan') && $entity->get('plan')->isEmpty() && !$form_state->isRebuilding()) {
      $plan_id = $this->defaultPlanId();
      if ($plan_id !== NULL) {
        $entity->set('plan', $plan_id);
      }
    }
    $form = parent::form($form, $form_state);
    if ($entity instanceof ActivityTemplate && $entity->isNew()) {
      $form['#cache']['contexts'][] = 'url.query_args:breakdown';
      $form['#cache']['contexts'][] = 'user';
    }
    // The legacy template edit form keeps the breakdown (subject and
    // period) and the order (get_form_edit_plantilla.php, hidden fields);
    // the order changes only with the moves (OrderUpkeep, D13). The legacy
    // "+" of a breakdown takes the subject and period from the breakdown
    // (get_form_new_plantilla.php:13-29), and with them the plan.
    if ($entity instanceof ActivityTemplate && (!$entity->isNew() || $breakdown !== NULL)) {
      $locked = $breakdown !== NULL ? ['plan', 'subject', 'period'] : ['subject', 'period'];
      foreach ($locked as $field) {
        if (isset($form[$field])) {
          $form[$field]['#disabled'] = TRUE;
        }
      }
    }
    // The legacy plan form has two date pickers that cannot be left empty
    // in practice (typing is blocked); the length is derived (Plan, PAR-010).
    if ($entity instanceof Plan) {
      foreach (['starts_on', 'ends_on'] as $field) {
        if (isset($form[$field]['widget'][0]['value'])) {
          $form[$field]['widget'][0]['value']['#required'] = TRUE;
        }
      }
    }
    return $form;
  }

  /**
   * Returns the plan a new record should default to, if any.
   */
  private function defaultPlanId(): ?int {
    $storage = $this->entityTypeManager->getStorage('ancora_plan');
    $requested = $this->getRequest()->query->get('plan');
    $plan = is_string($requested) && ctype_digit($requested) ? $storage->load($requested) : NULL;
    if ($plan !== NULL && $plan->access('update')) {
      return (int) $requested;
    }
    $ids = $storage->getQuery()->accessCheck(TRUE)->range(0, 2)->execute();
    return count($ids) === 1 ? (int) reset($ids) : NULL;
  }

  /**
   * Returns the breakdown of ?breakdown=<ID> if the user may update it.
   *
   * Anything else (malformed, unknown, another planner's) is ignored and
   * the plain form is shown, as for ?plan= (defaultPlanId()).
   */
  private function requestedBreakdown(): ?Breakdown {
    $requested = $this->getRequest()->query->get('breakdown');
    if (!is_string($requested) || !ctype_digit($requested)) {
      return NULL;
    }
    $breakdown = $this->entityTypeManager->getStorage('ancora_breakdown')->load($requested);
    return $breakdown instanceof Breakdown && $breakdown->access('update') ? $breakdown : NULL;
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
