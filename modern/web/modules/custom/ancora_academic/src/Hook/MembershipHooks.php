<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Hook;

use Drupal\ancora_academic\Entity\Cohort;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\GroupMembership;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The legacy group membership and data entry context (#70, D6, D10).
 *
 * - After a group is saved, the cohorts it gained leave the other groups of
 *   the same slot (GroupMembership, mueve_o_crea_brigada_de_grupo).
 * - The group form offers the cohorts of the group's specialty and level,
 *   as the legacy group browser does (get_browser_GruposXClasifFiltrados,
 *   dame_brigadas_sin_grupo), plus the cohorts it already has. It is a
 *   choice list, not a rule: nothing refuses another cohort (case 136).
 * - The subject form requires a specialty, as the legacy subject tree
 *   always gives one; the field itself is nullable like the legacy column.
 */
final class MembershipHooks {

  public function __construct(
    private readonly GroupMembership $membership,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_ENTITY_TYPE_insert() for student groups.
   */
  #[Hook('ancora_student_group_insert')]
  public function groupInsert(EntityInterface $entity): void {
    assert($entity instanceof StudentGroup);
    $this->membership->move($entity, $entity->getCohortIds());
  }

  /**
   * Implements hook_ENTITY_TYPE_update() for student groups.
   */
  #[Hook('ancora_student_group_update')]
  public function groupUpdate(EntityInterface $entity): void {
    assert($entity instanceof StudentGroup);
    $original = $entity->getOriginal();
    $this->membership->move($entity, $this->membership->added($entity, $original instanceof StudentGroup ? $original : NULL));
  }

  /**
   * Implements hook_form_alter() for the group and subject forms.
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    $form_object = $form_state->getFormObject();
    if (!$form_object instanceof EntityFormInterface) {
      return;
    }
    $entity = $form_object->getEntity();
    if ($entity->getEntityTypeId() === 'ancora_subject' && isset($form['specialty']['widget'])) {
      $form['specialty']['widget']['#required'] = TRUE;
      return;
    }
    if (!$entity instanceof StudentGroup || !isset($form['cohorts']['widget']['#options'])) {
      return;
    }
    $specialty = $entity->get('specialty')->target_id;
    $level = $entity->get('level')->target_id;
    if ($specialty === NULL || $level === NULL) {
      return;
    }
    $members = array_flip(array_map('strval', $entity->getCohortIds()));
    $cohorts = $this->entityTypeManager->getStorage('ancora_cohort')->loadMultiple(array_keys($form['cohorts']['widget']['#options']));
    foreach (array_keys($form['cohorts']['widget']['#options']) as $id) {
      $cohort = $cohorts[$id] ?? NULL;
      $same = $cohort instanceof Cohort
        && (string) $cohort->get('specialty')->target_id === (string) $specialty
        && (string) $cohort->get('level')->target_id === (string) $level;
      if (!$same && !isset($members[(string) $id])) {
        unset($form['cohorts']['widget']['#options'][$id]);
      }
    }
    $form['cohorts']['widget']['#description'] = new TranslatableMarkup('The cohorts of the specialty and level of this group. A cohort is in one group per plan, activity type, specialty and level: checking it here moves it out of its other group. A group with placed activities cannot receive cohorts, and a cohort cannot leave such a group for another one; unchecking a cohort always removes it.');
  }

}
