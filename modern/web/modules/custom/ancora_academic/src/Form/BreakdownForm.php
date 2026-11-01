<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\Entity\Breakdown;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * The options form of a subject breakdown (legacy form_desglose.tpl).
 *
 * As the legacy form, it shows the plan, the subject and the period, and
 * edits only the four options: the minimum and maximum number of days
 * between activities (free integers, as the legacy text inputs post them to
 * set_Desglose.php) and the two checkboxes. The subject and period never
 * change; the breakdown itself is created and deleted by
 * BreakdownMaintainer, not by a form.
 */
final class BreakdownForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $breakdown = $this->entity;
    assert($breakdown instanceof Breakdown);
    $plan = $breakdown->get('plan')->entity;
    $form['context'] = [
      '#type' => 'container',
      '#weight' => -100,
      '#attributes' => ['class' => ['ancora-breakdown-context']],
      'facts' => [
        '#theme' => 'item_list',
        '#items' => [
          $this->t('Plan: %plan', ['%plan' => $plan?->label() ?? '?']),
          $this->t('Subject: %subject', ['%subject' => $breakdown->getSubject()?->label() ?? '?']),
          $this->t('Period: %period', ['%period' => $breakdown->getPeriod()?->label() ?? '?']),
        ],
      ],
    ];
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state): array {
    $actions = parent::actions($form, $form_state);
    // Breakdowns are not deleted by a user (legacy: only by the cascade).
    unset($actions['delete']);
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $status = parent::save($form, $form_state);
    $this->messenger()->addStatus($this->t('Updated the breakdown of %label.', ['%label' => $this->entity->label()]));
    $form_state->setRedirectUrl($this->entity->toUrl('collection', [
      'query' => ['plan' => $this->entity->get('plan')->target_id],
    ]));
    return $status;
  }

}
