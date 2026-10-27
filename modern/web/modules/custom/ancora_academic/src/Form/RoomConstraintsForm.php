<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\Entity\Room;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\RoomConstraints;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The room constraint editors (D9c).
 *
 * - group_rooms (?group=<id>): the fixed room and the forbidden rooms of a
 *   student group (legacy lugar_fijo, acceso_prohibido; no legacy editor,
 *   A-4/P-2). One fixed room: legacy reads only the first.
 * - travel_costs (?room=<id>): the legacy editor of a room
 *   (form_edit_lugar_costos_de_traslado.tpl): one select 0..999 per other
 *   room of the owner, 0 meaning "irrelevant" (no row).
 */
final class RoomConstraintsForm extends FormBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly RoomConstraints $rooms,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('entity_type.manager'), $container->get(RoomConstraints::class));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_academic_room_constraints';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $input = 'group_rooms'): array {
    $form_state->set('input', $input);
    $form['#cache']['contexts'] = ['url.query_args', 'user'];
    $query = $this->getRequest()->query;
    if ($input === 'group_rooms') {
      $group = $this->editable('ancora_student_group', (string) $query->get('group', ''));
      if (!$group instanceof StudentGroup) {
        $form['empty'] = ['#markup' => '<p>' . $this->t('Open this page from the page of one of your student groups.') . '</p>'];
        return $form;
      }
      $options = $this->ownRooms((int) $group->getCreatorId());
      $fixed = $this->rooms->groupRooms($group->id(), 'fixed');
      $form['intro'] = ['#markup' => '<p>' . $this->t('The engine gives the group its fixed room whenever that room is eligible and free. Forbidden rooms are stored as in the legacy application, whose generator never applies them (quirk Q-30).') . '</p>'];
      $form['fixed'] = [
        '#type' => 'select',
        '#title' => $this->t('Fixed room of %group', ['%group' => $group->label()]),
        '#options' => $options,
        '#empty_option' => $this->t('- None -'),
        '#default_value' => $fixed[0] ?? NULL,
      ];
      $form['forbidden'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Forbidden rooms'),
        '#options' => $options,
        '#default_value' => $this->rooms->groupRooms($group->id(), 'forbidden'),
      ];
      $form_state->set('target', (int) $group->id());
    }
    else {
      $room = $this->editable('ancora_room', (string) $query->get('room', ''));
      if (!$room instanceof Room) {
        $form['empty'] = ['#markup' => '<p>' . $this->t('Open this page from the page of one of your rooms.') . '</p>'];
        return $form;
      }
      $costs = $this->rooms->travelCosts($room->id());
      $form['intro'] = ['#markup' => '<p>' . $this->t('The cost of going from %room to each of your other rooms. The engine reads it when a run looks for the nearest room. 0 means irrelevant.', ['%room' => $room->label()]) . '</p>'];
      $form['costs'] = ['#type' => 'table', '#header' => [$this->t('To room'), $this->t('Cost')]];
      $choices = array_combine(range(0, 999), array_map(fn (int $c) => $c === 0 ? (string) $this->t('(irrelevant)') : (string) $c, range(0, 999)));
      foreach ($this->ownRooms((int) $room->getCreatorId()) as $id => $label) {
        if ($id === (int) $room->id()) {
          continue;
        }
        $form['costs'][$id]['name'] = ['#markup' => $label];
        $form['costs'][$id]['cost'] = [
          '#type' => 'select',
          '#title' => $this->t('Cost to @room', ['@room' => $label]),
          '#title_display' => 'invisible',
          '#options' => $choices,
          '#default_value' => $costs[$id] ?? 0,
        ];
      }
      $form_state->set('target', (int) $room->id());
    }
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['save'] = ['#type' => 'submit', '#value' => $this->t('Save'), '#button_type' => 'primary'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $target = $form_state->get('target');
    if ($form_state->get('input') === 'group_rooms') {
      $group = $this->entityTypeManager->getStorage('ancora_student_group')->load($target);
      if (!$group instanceof StudentGroup) {
        return;
      }
      $fixed = $form_state->getValue('fixed');
      $forbidden = array_map('intval', array_keys(array_filter((array) $form_state->getValue('forbidden'))));
      $messages = $this->rooms->saveGroupRooms($group, $fixed === '' || $fixed === NULL ? NULL : (int) $fixed, $forbidden);
    }
    else {
      $room = $this->entityTypeManager->getStorage('ancora_room')->load($target);
      if (!$room instanceof Room) {
        return;
      }
      $costs = [];
      foreach ((array) $form_state->getValue('costs') as $id => $row) {
        $costs[(int) $id] = (int) ($row['cost'] ?? 0);
      }
      $messages = $this->rooms->saveTravelCosts($room, $costs);
    }
    if ($messages !== []) {
      foreach ($messages as $message) {
        $this->messenger()->addError($message);
      }
      $form_state->setRebuild();
      return;
    }
    $this->messenger()->addStatus($this->t('Saved.'));
  }

  /**
   * A record the user may edit, or NULL.
   */
  private function editable(string $entity_type_id, string $id): mixed {
    $entity = ctype_digit($id) ? $this->entityTypeManager->getStorage($entity_type_id)->load($id) : NULL;
    return $entity !== NULL && $entity->access('update') ? $entity : NULL;
  }

  /**
   * The rooms of an owner, by code.
   *
   * @return array<int, string>
   *   Id => label.
   */
  private function ownRooms(int $owner): array {
    $storage = $this->entityTypeManager->getStorage('ancora_room');
    $options = [];
    foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->condition('uid', $owner)->sort('code')->execute()) as $id => $room) {
      $options[(int) $id] = (string) $room->label();
    }
    return $options;
  }

}
