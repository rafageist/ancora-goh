<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\RoomAssignment;
use Drupal\ancora_academic\Entity\TeacherAssignment;
use Drupal\ancora_academic\TemplateAssignments;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The visual assignment editor of one activity template (#122).
 *
 * Three panels: the template and its groups; its teachers and rooms by
 * priority; the owner's teachers and rooms that it does not have yet. Every
 * change is a button of this form (CSRF form token, the template's update
 * access, TemplateAssignments and the Entity API), so it works without
 * JavaScript and from the keyboard. assignment-editor.js adds drag and
 * drop: a drop fills the "drop" fields with stable ids and presses the same
 * hidden button; the server decides what the drop means from the ids, never
 * from a position on the screen. A removal asks first and can be undone.
 * The "state" field holds a token of the template's assignments when the
 * page was built: a change made meanwhile (another window or user) refuses
 * the request and shows the current state.
 */
final class AssignmentEditorForm extends FormBase {

  /**
   * The assignment operations.
   *
   * The services are not readonly: a rebuilt form is serialized into the
   * form cache, and DependencySerializationTrait restores them.
   */
  protected TemplateAssignments $assignments;

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The private tempstore factory.
   */
  protected PrivateTempStoreFactory $tempStoreFactory;

  public function __construct(TemplateAssignments $assignments, EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $temp_store_factory) {
    $this->assignments = $assignments;
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * The undo and feedback store of the current user.
   */
  private function store(): PrivateTempStore {
    return $this->tempStoreFactory->get('ancora_academic_assignment_editor');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get(TemplateAssignments::class),
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_academic_assignment_editor';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?ActivityTemplate $template = NULL, string $search = ''): array {
    if ($template === NULL) {
      return $form;
    }
    $form_state->set('template', (int) $template->id());
    $form_state->set('search', $search);
    $tid = (int) $template->id();
    $form['#attributes']['class'][] = 'ancora-assign';
    $form['#attributes']['data-ancora-assign'] = $tid;
    $form['#cache']['max-age'] = 0;

    $form['state'] = ['#type' => 'hidden', '#value' => $this->assignments->state($template)];
    $form['drop'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#attributes' => ['class' => ['ancora-assign__drop-fields', 'hidden']],
    ];
    foreach (['op', 'item', 'target', 'value', 'confirmed'] as $field) {
      $form['drop'][$field] = [
        '#type' => 'hidden',
        '#default_value' => '',
        '#attributes' => ['data-ancora-drop' => $field],
      ];
    }
    $form['drop']['apply'] = $this->button($this->t('Apply the dropped change'), ['drop'], [
      'data-ancora-drop' => 'apply',
      'tabindex' => '-1',
      'aria-hidden' => 'true',
      'class' => ['visually-hidden'],
    ]);

    $form['feedback'] = $this->feedback($tid);
    $form['panels'] = ['#type' => 'container', '#attributes' => ['class' => ['ancora-assign__panels']]];
    $form['panels']['template'] = $this->templatePanel($template, $search);
    $form['panels']['assigned'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['ancora-assign__panel', 'ancora-assign__assigned']],
      'teachers' => $this->assignedPanel($template, 'teacher', $form_state),
      'rooms' => $this->assignedPanel($template, 'room', $form_state),
    ];
    $form['panels']['available'] = $this->availablePanel($template, $search);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $template = $this->entityTypeManager->getStorage('ancora_activity_template')->load((int) $form_state->get('template'));
    if (!$template instanceof ActivityTemplate || !$template->access('update')) {
      $this->messenger()->addError($this->t('You may not change the assignments of this template.'));
      return;
    }
    $input = $form_state->getUserInput();
    $op = $form_state->getTriggeringElement()['#ancora_op'] ?? [];
    if ($op === ['drop']) {
      $op = $this->dropOperation((array) ($input['drop'] ?? []));
    }
    $action = $op[0] ?? '';
    if ($action === 'cancel') {
      $this->finish($form_state, $template, NULL, NULL, $op[3] ?? NULL);
      return;
    }
    if (!hash_equals($this->assignments->state($template), (string) ($input['state'] ?? ''))) {
      $this->finish($form_state, $template, [(string) $this->t('The assignments of this template changed after this page was loaded, in another window or by another user. Nothing was changed: the page now shows the current assignments. Repeat your change if it is still needed.')], NULL);
      return;
    }
    [$messages, $status, $focus] = $this->apply($template, $op, $input, $form_state);
    if ($messages === NULL) {
      // A confirmation step: the form is rebuilt with the question.
      return;
    }
    // The undo offer is for the last change only.
    if ($messages === [] && $action !== 'confirm_remove') {
      $this->store()->delete($this->key('undo', (int) $template->id()));
    }
    $this->finish($form_state, $template, $messages, $messages === [] ? $status : NULL, $focus);
  }

  /**
   * Runs one operation.
   *
   * @return array{0: ?list<string>, 1: ?string, 2: ?string}
   *   The error messages (NULL for a confirmation step), the success
   *   message and the id of the element to focus.
   */
  private function apply(ActivityTemplate $template, array $op, array $input, FormStateInterface $form_state): array {
    [$action, $kind, $id] = $op + ['', '', ''];
    $kind = (string) $kind;
    if (!isset(TemplateAssignments::KINDS[$kind]) && $action !== 'undo') {
      return [[(string) $this->t('That change is not possible here.')], NULL, NULL];
    }
    $invalid = [
      [(string) $this->t('That assignment is not one of this template. The page now shows the current assignments.')],
      NULL,
      NULL,
    ];
    switch ($action) {
      case 'assign':
        [$messages, $assignment] = $this->assignments->assign($template, $kind, (string) $id);
        $resource = $assignment?->get(TemplateAssignments::KINDS[$kind][1])->entity;
        return [
          $messages,
          (string) $this->t('Assigned %label with priority 1.', ['%label' => $resource?->label() ?? '']),
          $assignment ? $this->cardId($kind, (int) $assignment->id()) : $this->panelId($kind),
        ];

      case 'priority':
        $assignment = $this->assignments->assignment($template, $kind, (string) $id);
        if ($assignment === NULL) {
          return $invalid;
        }
        $value = $op[3] ?? ($input['priority'][$kind][$id] ?? '');
        $messages = $this->assignments->setPriority($template, $assignment, (string) $value);
        return [
          $messages,
          (string) $this->t('%label now has priority @priority.', [
            '%label' => $this->resourceLabel($assignment),
            '@priority' => $value,
          ]),
          $this->cardId($kind, (int) $id),
        ];

      case 'add_group':
      case 'remove_group':
        $assignment = $this->assignments->assignment($template, 'teacher', (string) $id);
        if (!$assignment instanceof TeacherAssignment) {
          return $invalid;
        }
        $group = (string) ($op[3] ?? ($input['add_group'][$id] ?? ''));
        $groups = $assignment->getGroupIds();
        if ($action === 'add_group') {
          if (in_array((int) $group, $groups, TRUE)) {
            return [
              [(string) $this->t('%label already gives the activity to that group.', ['%label' => $this->resourceLabel($assignment)])],
              NULL,
              $this->cardId('teacher', (int) $id),
            ];
          }
          $groups[] = $group;
        }
        else {
          $groups = array_values(array_filter($groups, static fn (int $g): bool => (string) $g !== $group));
        }
        $label = (string) (ctype_digit($group) ? $this->entityTypeManager->getStorage('ancora_student_group')->load((int) $group)?->label() : '');
        $messages = $this->assignments->setGroups($template, $assignment, $groups);
        $status = $action === 'add_group'
          ? $this->t('%teacher now gives the activity to %group.', [
            '%teacher' => $this->resourceLabel($assignment),
            '%group' => $label,
          ])
          : $this->t('%teacher no longer gives the activity to %group.', [
            '%teacher' => $this->resourceLabel($assignment),
            '%group' => $label,
          ]);
        return [$messages, (string) $status, $this->cardId('teacher', (int) $id)];

      case 'remove':
        $assignment = $this->assignments->assignment($template, $kind, (string) $id);
        if ($assignment === NULL) {
          return $invalid;
        }
        $form_state->set('confirm', [$kind, (int) $id]);
        $form_state->setRebuild();
        return [NULL, NULL, NULL];

      case 'confirm_remove':
        $assignment = $this->assignments->assignment($template, $kind, (string) $id);
        if ($assignment === NULL) {
          return $invalid;
        }
        $snapshot = $this->assignments->remove($assignment);
        $this->store()->set($this->key('undo', (int) $template->id()), $snapshot);
        return [
          [],
          (string) $this->t('Removed %label from this template.', ['%label' => $snapshot['label']]),
          $this->panelId($kind),
        ];

      case 'undo':
        $snapshot = $this->store()->get($this->key('undo', (int) $template->id()));
        $messages = is_array($snapshot) ? $this->assignments->restore($template, $snapshot) : [(string) $this->t('There is nothing to undo.')];
        if ($messages === []) {
          $this->store()->delete($this->key('undo', (int) $template->id()));
        }
        return [
          $messages,
          (string) $this->t('Assigned %label again.', ['%label' => $snapshot['label'] ?? '']),
          $this->panelId((string) ($snapshot['kind'] ?? 'teacher')),
        ];
    }
    return [[(string) $this->t('That change is not possible here.')], NULL, NULL];
  }

  /**
   * What a drop means, from the stable ids of the dragged item and target.
   *
   * Drops: an available teacher or room on its panel (assign); an assigned
   * one on a priority lane of its panel (priority); a group on a teacher
   * assignment (add_group); an assigned one on the available panel (remove,
   * which the script confirms first). Anything else is refused.
   */
  private function dropOperation(array $drop): array {
    $op = (string) ($drop['op'] ?? '');
    $item = (string) ($drop['item'] ?? '');
    $target = (string) ($drop['target'] ?? '');
    $value = (string) ($drop['value'] ?? '');
    return match ($op) {
      'assign' => ['assign', $target, $item],
      'priority' => ['priority', $target, $item, $value],
      'add_group' => ['add_group', 'teacher', $target, $item],
      'remove' => ($drop['confirmed'] ?? '') === '1' ? ['confirm_remove', $target, $item] : ['remove', $target, $item],
      default => ['invalid'],
    };
  }

  /**
   * Stores the feedback and returns to the editor, at the changed element.
   */
  private function finish(FormStateInterface $form_state, ActivityTemplate $template, ?array $errors, ?string $status, ?string $focus = NULL): void {
    $tid = (int) $template->id();
    if ($errors !== NULL && $errors !== []) {
      $this->store()->set($this->key('feedback', $tid), ['type' => 'error', 'messages' => $errors]);
    }
    elseif ($status !== NULL) {
      $this->store()->set($this->key('feedback', $tid), ['type' => 'status', 'messages' => [$status]]);
    }
    $query = ['template' => $tid];
    $search = (string) $form_state->get('search');
    if ($search !== '') {
      $query['q'] = $search;
    }
    $options = ['query' => $query];
    if ($focus !== NULL) {
      $options['fragment'] = $focus;
    }
    $form_state->setRedirectUrl(Url::fromRoute('ancora_academic.assignment_editor', [], $options));
  }

  /**
   * The last change's message (once), with the undo button after a removal.
   */
  private function feedback(int $tid): array {
    $feedback = $this->store()->get($this->key('feedback', $tid));
    $this->store()->delete($this->key('feedback', $tid));
    $undo = $this->store()->get($this->key('undo', $tid));
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-assign__feedback'],
        'id' => 'ancora-assign-feedback',
        'tabindex' => '-1',
      ],
    ];
    if (is_array($feedback)) {
      $error = $feedback['type'] === 'error';
      $build['#attributes']['role'] = $error ? 'alert' : 'status';
      $build['#attributes']['class'][] = $error ? 'ancora-assign__feedback--error' : 'ancora-assign__feedback--status';
      $build['#attributes']['data-ancora-feedback'] = $feedback['type'];
      $build['messages'] = [
        '#theme' => 'item_list',
        // Our own translated messages and violation texts (tags stripped,
        // labels escaped); #markup is XSS-filtered once more.
        '#items' => array_map(static fn (string $m): array => ['#markup' => $m], $feedback['messages']),
      ];
    }
    if (is_array($undo)) {
      $build['undo'] = $this->button($this->t('Undo: assign @label again', [
        '@label' => $undo['label'],
      ]), ['undo', '', '']);
    }
    return $build;
  }

  /**
   * The template, its groups (draggable onto teachers) and the way back.
   */
  private function templatePanel(ActivityTemplate $template, string $search): array {
    $subject = $template->getSubject();
    $type = $template->get('activity_type')->entity;
    $panel = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-assign__panel', 'ancora-assign__template'],
        'aria-labelledby' => 'ancora-assign-template-title',
        'role' => 'region',
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Template'),
        '#attributes' => ['id' => 'ancora-assign-template-title'],
      ],
      'summary' => [
        '#theme' => 'item_list',
        '#items' => [
          $this->t('Subject: @subject', ['@subject' => $subject?->label() ?? '?']),
          $this->t('Period: @period', ['@period' => $template->get('period')->entity?->label() ?? '?']),
          $this->t('Activity type: @type', ['@type' => $type?->label() ?? '?']),
          $this->t('Number @sequence in its subject and period', ['@sequence' => (int) $template->get('sequence')->value]),
          $template->get('requires_teacher')->value ? $this->t('Requires a teacher') : $this->t('Does not require a teacher'),
          $template->get('requires_room')->value ? $this->t('Requires a room') : $this->t('Does not require a room'),
        ],
      ],
      'nav' => [
        '#theme' => 'item_list',
        '#attributes' => ['class' => ['ancora-assign__nav']],
        '#items' => [
          ['#type' => 'link', '#title' => $this->t('Back to the template'), '#url' => $template->toUrl()],
          [
            '#type' => 'link',
            '#title' => $this->t('All templates'),
            '#url' => Url::fromRoute('ancora_academic.assignment_editor', [], ['query' => $search === '' ? [] : ['q' => $search]]),
          ],
          [
            '#type' => 'link',
            '#title' => $this->t('Other resources'),
            '#url' => Url::fromRoute('ancora_academic.template_resources', [], ['query' => ['template' => $template->id()]]),
          ],
        ],
      ],
      'groups_title' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $this->t('Groups of this template')],
      'groups_help' => ['#markup' => '<p class="ancora-assign__help">' . $this->t('The groups of the plan that attend this activity type and have the specialty and level of the subject. Drag one onto a teacher, or use the teacher\'s "Add group" button.') . '</p>'],
    ];
    $items = [];
    foreach ($this->assignments->groups($template) as $gid => $group) {
      $label = (string) $group->label();
      $items[] = [
        '#markup' => $this->t('@group <span class="ancora-assign__meta">enrolment @total</span>', [
          '@group' => $label,
          '@total' => $this->assignments->enrollment($group),
        ]),
        '#wrapper_attributes' => [
          'class' => ['ancora-assign__chip'],
          'draggable' => 'true',
          'data-drag-kind' => 'group',
          'data-id' => $gid,
          'data-label' => $label,
          'data-search' => mb_strtolower($label),
        ],
      ];
    }
    $panel['groups'] = $items === []
      ? ['#markup' => '<p>' . $this->t('No group attends this template: add groups with this activity type, specialty and level to the plan first.') . '</p>']
      : ['#theme' => 'item_list', '#items' => $items, '#attributes' => ['class' => ['ancora-assign__chips']]];
    return $panel;
  }

  /**
   * The teachers or rooms of the template, in one lane per priority.
   */
  private function assignedPanel(ActivityTemplate $template, string $kind, FormStateInterface $form_state): array {
    $teacher = $kind === 'teacher';
    $assignments = $this->assignments->assignments($template, $kind);
    $options = $this->assignments->priorityOptions($template, $kind);
    $groups = $teacher ? $this->assignments->groups($template) : [];
    $confirm = $form_state->get('confirm');
    $panel = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-assign__list'],
        'id' => $this->panelId($kind),
        'role' => 'region',
        'aria-labelledby' => $this->panelId($kind) . '-title',
        'tabindex' => '-1',
        'data-drop-kind' => $kind,
        'data-drop-op' => 'assign',
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $teacher ? $this->t('Teachers (@count)', ['@count' => count($assignments)]) : $this->t('Rooms (@count)', ['@count' => count($assignments)]),
        '#attributes' => ['id' => $this->panelId($kind) . '-title'],
      ],
      'help' => [
        '#markup' => '<p class="ancora-assign__help">' . ($teacher
          ? $this->t('Priority 1 is preferred; several teachers may share a priority. Drag a teacher to another priority, or pick it and press "Set priority". A teacher gives the activity only to its groups.')
          : $this->t('Priority 1 is preferred; several rooms may share a priority. Drag a room to another priority, or pick it and press "Set priority". Capacity 0 means no limit.')) . '</p>',
      ],
    ];
    if ($assignments === []) {
      $panel['empty'] = [
        '#markup' => '<p class="ancora-assign__empty">' . ($teacher
        ? $this->t('No teacher yet. Drag one here from "Available", or press its "Assign" button.')
        : $this->t('No room yet. Drag one here from "Available", or press its "Assign" button.')) . '</p>',
      ];
    }
    $by_priority = [];
    foreach ($assignments as $id => $assignment) {
      $by_priority[(int) $assignment->get('priority')->value][$id] = $assignment;
    }
    $lanes = $assignments === [] ? [] : array_unique(array_merge($options, array_keys($by_priority)));
    sort($lanes);
    foreach ($lanes as $priority) {
      $lane = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['ancora-assign__lane'],
          'data-drop-kind' => $kind . '-assignment',
          'data-drop-op' => 'priority',
          'data-priority' => $priority,
          'aria-label' => $this->t('Priority @priority', ['@priority' => $priority]),
        ],
        'title' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $this->t('Priority @priority', ['@priority' => $priority]),
        ],
      ];
      foreach ($by_priority[$priority] ?? [] as $id => $assignment) {
        $lane[$id] = $this->card($kind, $assignment, $options, $groups, $confirm === [$kind, (int) $id]);
      }
      if (!isset($by_priority[$priority])) {
        $lane['empty'] = ['#markup' => '<p class="ancora-assign__empty">' . $this->t('None. Drop one here to give it priority @priority.', ['@priority' => $priority]) . '</p>'];
      }
      $panel['lane_' . $priority] = $lane;
    }
    return $panel;
  }

  /**
   * One assignment: priority, groups, details, removal.
   */
  private function card(string $kind, TeacherAssignment|RoomAssignment $assignment, array $options, array $groups, bool $confirming): array {
    $id = (int) $assignment->id();
    $label = $this->resourceLabel($assignment);
    $card = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-assign__card'],
        'id' => $this->cardId($kind, $id),
        'tabindex' => '-1',
        'draggable' => 'true',
        'data-drag-kind' => $kind . '-assignment',
        'data-id' => $id,
        'data-label' => $label,
        'aria-label' => $label,
      ],
      'name' => [
        '#type' => 'html_tag',
        '#tag' => 'h4',
        '#value' => $label,
        '#attributes' => ['class' => ['ancora-assign__name']],
      ],
    ];
    if ($assignment instanceof RoomAssignment) {
      $capacity = (int) $assignment->getRoom()?->get('capacity')->value;
      $card['capacity'] = ['#markup' => '<p class="ancora-assign__meta">' . ($capacity === 0 ? $this->t('Capacity 0 (no limit)') : $this->t('Capacity @capacity', ['@capacity' => $capacity])) . '</p>'];
    }
    $card['priority'] = [
      '#type' => 'select',
      '#title' => $this->t('Priority of @label', ['@label' => $label]),
      '#options' => array_combine($options, $options),
      '#value' => (int) $assignment->get('priority')->value,
      '#name' => "priority[$kind][$id]",
      '#attributes' => ['class' => ['ancora-assign__priority']],
    ];
    $card['set_priority'] = $this->button($this->t('Set priority'), ['priority', $kind, $id], ['aria-label' => $this->t('Set the priority of @label', ['@label' => $label])]);

    if ($assignment instanceof TeacherAssignment) {
      $card['#attributes']['data-drop-kind'] = 'group';
      $card['#attributes']['data-drop-op'] = 'add_group';
      $mine = $assignment->getGroupIds();
      $card['groups'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['ancora-assign__groups']],
        'title' => ['#markup' => '<p class="ancora-assign__meta">' . $this->t('Groups') . '</p>'],
      ];
      if ($mine === []) {
        $card['groups']['none'] = ['#markup' => '<p class="ancora-assign__warning">' . $this->t('No group yet: the engine never places this activity with this teacher.') . '</p>'];
      }
      foreach ($mine as $gid) {
        $group = $groups[$gid] ?? $this->entityTypeManager->getStorage('ancora_student_group')->load($gid);
        $group_label = (string) ($group?->label() ?? $gid);
        $card['groups'][$gid] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['ancora-assign__chip', 'ancora-assign__chip--static']],
          'label' => ['#plain_text' => $group_label],
          'remove' => $this->button('×', ['remove_group', 'teacher', $id, (string) $gid], [
            'aria-label' => $this->t('Remove group @group from @teacher', [
              '@group' => $group_label,
              '@teacher' => $label,
            ]),
            'title' => $this->t('Remove group @group from @teacher', ['@group' => $group_label, '@teacher' => $label]),
            'class' => ['ancora-assign__chip-remove'],
          ]),
        ];
      }
      $free = array_diff_key($groups, array_flip($mine));
      if ($free !== []) {
        $card['add_group'] = [
          '#type' => 'select',
          '#title' => $this->t('Group to add to @label', ['@label' => $label]),
          '#options' => array_map(static fn ($g): string => (string) $g->label(), $free),
          '#name' => "add_group[$id]",
          '#value' => array_key_first($free),
        ];
        $card['add_group_submit'] = $this->button($this->t('Add group'), ['add_group', 'teacher', $id], ['aria-label' => $this->t('Add the chosen group to @label', ['@label' => $label])]);
      }
    }

    $card['actions'] = ['#type' => 'container', '#attributes' => ['class' => ['ancora-assign__card-actions']]];
    $card['actions']['details'] = [
      '#type' => 'link',
      '#title' => $this->t('Details'),
      '#url' => Url::fromRoute('ancora_academic.assignment_details', ['kind' => $kind, 'id' => $id]),
      '#attributes' => [
        'class' => ['use-ajax', 'ancora-assign__details'],
        'data-dialog-type' => 'dialog',
        'data-dialog-options' => json_encode(['width' => 420]),
        'aria-label' => $this->t('Details of @label', ['@label' => $label]),
      ],
    ];
    if ($confirming) {
      $card['#attributes']['class'][] = 'ancora-assign__card--confirm';
      $card['actions']['question'] = [
        '#markup' => '<p class="ancora-assign__warning" role="alert" id="' . $this->cardId($kind, $id) . '-question">' . $this->t('Remove %label from this template?', ['%label' => $label]) . '</p>',
      ];
      $card['actions']['confirm'] = $this->button($this->t('Confirm removal'), ['confirm_remove', $kind, $id], [
        'class' => ['button--danger'],
        'aria-describedby' => $this->cardId($kind, $id) . '-question',
        'autofocus' => 'autofocus',
      ]);
      $card['actions']['cancel'] = $this->button($this->t('Cancel'), ['cancel', $kind, $id, $this->cardId($kind, $id)]);
    }
    else {
      $card['actions']['remove'] = $this->button($this->t('Remove'), ['remove', $kind, $id], [
        'class' => ['button--danger'],
        'aria-label' => $this->t('Remove @label from this template', ['@label' => $label]),
      ]);
    }
    return $card;
  }

  /**
   * The owner's teachers and rooms that the template does not have.
   */
  private function availablePanel(ActivityTemplate $template, string $search): array {
    $panel = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-assign__panel', 'ancora-assign__available'],
        'role' => 'region',
        'aria-labelledby' => 'ancora-assign-available-title',
        'data-drop-kind' => 'teacher-assignment room-assignment',
        'data-drop-op' => 'remove',
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Available'),
        '#attributes' => ['id' => 'ancora-assign-available-title'],
      ],
      'help' => ['#markup' => '<p class="ancora-assign__help">' . $this->t('Your teachers and rooms that this template does not have yet. Drag one onto Teachers or Rooms, or press "Assign". Drop an assigned teacher or room here to remove it.') . '</p>'],
    ];
    foreach (['teacher' => $this->t('Teachers'), 'room' => $this->t('Rooms')] as $kind => $title) {
      $items = [];
      foreach ($this->assignments->candidates($template, $kind) as $rid => $resource) {
        $label = (string) $resource->label();
        if ($search !== '' && mb_stripos($label, $search) === FALSE && mb_stripos((string) $resource->get('code')->value, $search) === FALSE) {
          continue;
        }
        $meta = $kind === 'room' ? ' <span class="ancora-assign__meta">' . $this->t('capacity @capacity', ['@capacity' => (int) $resource->get('capacity')->value]) . '</span>' : '';
        // Containers, not an item list: the buttons must be form children.
        $items[$rid] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['ancora-assign__chip'],
            'role' => 'listitem',
            'draggable' => 'true',
            'data-drag-kind' => $kind,
            'data-id' => $rid,
            'data-label' => $label,
            'data-search' => mb_strtolower($label . ' ' . $resource->get('code')->value),
          ],
          'label' => ['#markup' => '<span class="ancora-assign__name">' . htmlspecialchars($label, ENT_QUOTES) . '</span>' . $meta],
          'assign' => $this->button($this->t('Assign'), ['assign', $kind, $rid], ['aria-label' => $this->t('Assign @label', ['@label' => $label])]),
        ];
      }
      $panel[$kind] = [
        'title' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $title],
        'list' => $items === []
          ? ['#markup' => '<p class="ancora-assign__empty">' . ($search === '' ? $this->t('None left.') : $this->t('None matches the search.')) . '</p>']
          : [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['ancora-assign__chips'],
              'role' => 'list',
              'aria-label' => $title,
              'data-ancora-filterable' => $kind,
            ],
          ] + $items,
      ];
    }
    return $panel;
  }

  /**
   * A submit button of one operation, without validation of other fields.
   */
  private function button(mixed $title, array $op, array $attributes = []): array {
    // One name per operation: the triggering button is found by its name,
    // the same in every build of the form.
    return [
      '#type' => 'submit',
      '#value' => $title,
      '#name' => preg_replace('/[^a-z0-9_]+/', '_', 'op_' . implode('_', array_map('strval', $op))),
      '#ancora_op' => $op,
      '#limit_validation_errors' => [],
      '#submit' => ['::submitForm'],
      '#attributes' => $attributes + ['class' => []],
    ];
  }

  /**
   * The label of an assignment's teacher or room.
   */
  private function resourceLabel(EntityInterface $assignment): string {
    $field = $assignment instanceof TeacherAssignment ? 'teacher' : 'room';
    return (string) ($assignment->get($field)->entity?->label() ?? '?');
  }

  /**
   * The HTML id of an assignment card.
   */
  private function cardId(string $kind, int $id): string {
    return "ancora-assign-$kind-$id";
  }

  /**
   * The HTML id of the teachers or rooms panel.
   */
  private function panelId(string $kind): string {
    return "ancora-assign-$kind-panel";
  }

  /**
   * A tempstore key of the template.
   */
  private function key(string $name, int $template): string {
    return "$name:$template";
  }

}
