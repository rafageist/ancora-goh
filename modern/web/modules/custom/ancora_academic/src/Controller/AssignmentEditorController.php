<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Controller;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\RoomAssignment;
use Drupal\ancora_academic\Entity\TeacherAssignment;
use Drupal\ancora_academic\Form\AssignmentEditorForm;
use Drupal\ancora_academic\TemplateAssignments;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The visual assignment editor of activity templates (#122).
 *
 * Without ?template=, the list of the user's templates to pick from (with
 * the D13 order arrows); with it, the editor of that template's teachers,
 * groups and rooms (AssignmentEditorForm). ?q= filters both lists.
 */
final class AssignmentEditorController extends ControllerBase {

  use AutowireTrait;

  public function __construct(
    private readonly TemplateAssignments $assignments,
    private readonly RequestStack $requestStack,
  ) {}

  /**
   * The editor page, or the template list.
   */
  public function page(): array {
    $query = $this->requestStack->getCurrentRequest()->query;
    $search = trim((string) $query->get('q', ''));
    $id = (string) $query->get('template', '');
    $build = [
      '#attached' => ['library' => ['ancora_academic/assignment_editor']],
      '#cache' => ['max-age' => 0],
    ];
    if ($id === '') {
      return $build + $this->picker($search);
    }
    $template = ctype_digit($id) ? $this->entityTypeManager()->getStorage('ancora_activity_template')->load((int) $id) : NULL;
    if (!$template instanceof ActivityTemplate) {
      throw new NotFoundHttpException();
    }
    if (!$template->access('update')) {
      throw new AccessDeniedHttpException();
    }
    $build['search'] = $this->search($search, (int) $template->id());
    $build['form'] = $this->formBuilder()->getForm(AssignmentEditorForm::class, $template, $search);
    return $build;
  }

  /**
   * The title of the editor page.
   */
  public function title(): TranslatableMarkup {
    $id = (string) $this->requestStack->getCurrentRequest()->query->get('template', '');
    $template = ctype_digit($id) ? $this->entityTypeManager()->getStorage('ancora_activity_template')->load((int) $id) : NULL;
    return $template instanceof ActivityTemplate && $template->access('view')
      ? $this->t('Assignments of @template', ['@template' => $template->label()])
      : $this->t('Assignment editor');
  }

  /**
   * The details of one assignment, as a page or in a dialog.
   */
  public function details(string $kind, string $id): array {
    $type = TemplateAssignments::KINDS[$kind][0] ?? NULL;
    $assignment = $type === NULL || !ctype_digit($id) ? NULL : $this->entityTypeManager()->getStorage($type)->load((int) $id);
    if (!$assignment instanceof TeacherAssignment && !$assignment instanceof RoomAssignment) {
      throw new NotFoundHttpException();
    }
    $template = $assignment->getTemplate();
    if ($template === NULL || !$template->access('update')) {
      throw new AccessDeniedHttpException();
    }
    $items = [
      $this->t('Activity template: @label', ['@label' => $template->label()]),
      $this->t('Priority: @priority (1 is preferred; equal priorities are allowed)', ['@priority' => $assignment->getPriority()]),
    ];
    if ($assignment instanceof TeacherAssignment) {
      $groups = $assignment->get('groups')->referencedEntities();
      if ($groups === []) {
        $items[] = $this->t('No group yet: the engine never places this activity with this teacher until a group is added.');
      }
      foreach ($groups as $group) {
        $cohorts = array_map(static fn ($c): string => sprintf('%s (%d)', $c->label(), (int) $c->get('enrollment')->value), $group->get('cohorts')->referencedEntities());
        $items[] = $this->t('Group @group: enrolment @total; cohorts @cohorts', [
          '@group' => $group->label(),
          '@total' => $this->assignments->enrollment($group),
          '@cohorts' => $cohorts === [] ? $this->t('none') : implode(', ', $cohorts),
        ]);
      }
      $items[] = $this->t('The engine takes the teachers of a group in priority order and picks the least loaded one.');
    }
    else {
      $capacity = (int) $assignment->getRoom()?->get('capacity')->value;
      $items[] = $capacity === 0
        ? $this->t('Capacity: 0 (no limit)')
        : $this->t('Capacity: @capacity; the engine leaves this room out for a group whose enrolment is larger.', ['@capacity' => $capacity]);
      $items[] = $this->t('The engine tries the rooms by priority tier.');
    }
    return [
      '#title' => $assignment->label(),
      'list' => ['#theme' => 'item_list', '#items' => $items],
      'back' => [
        '#type' => 'link',
        '#title' => $this->t('Back to the assignments of the template'),
        '#url' => Url::fromRoute('ancora_academic.assignment_editor', [], ['query' => ['template' => $template->id()]]),
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * The search field (a GET form, outside the editor form).
   */
  private function search(string $search, ?int $template): array {
    $build = [
      '#type' => 'html_tag',
      '#tag' => 'form',
      '#attributes' => [
        'method' => 'get',
        'role' => 'search',
        'class' => ['ancora-assign__search'],
        'action' => Url::fromRoute('ancora_academic.assignment_editor')->toString(),
      ],
    ];
    if ($template !== NULL) {
      $build['template'] = [
        '#type' => 'html_tag',
        '#tag' => 'input',
        '#attributes' => ['type' => 'hidden', 'name' => 'template', 'value' => $template],
      ];
    }
    $build['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#attributes' => ['for' => 'ancora-assign-search'],
      '#value' => $template === NULL ? $this->t('Search the templates') : $this->t('Search teachers, rooms and groups'),
    ];
    $build['q'] = [
      '#type' => 'html_tag',
      '#tag' => 'input',
      '#attributes' => [
        'type' => 'search',
        'id' => 'ancora-assign-search',
        'name' => 'q',
        'value' => $search,
        'data-ancora-filter' => $template === NULL ? 'templates' : 'resources',
      ],
    ];
    $build['submit'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#attributes' => ['type' => 'submit', 'class' => ['button']],
      '#value' => $this->t('Search'),
    ];
    return $build;
  }

  /**
   * The user's templates, by plan and period, in subject and legacy order.
   */
  private function picker(string $search): array {
    $storage = $this->entityTypeManager()->getStorage('ancora_activity_template');
    $ids = $storage->getQuery()->accessCheck(TRUE)->sort('plan')->sort('period')->sort('subject')->sort('sequence')->sort('id')->execute();
    $build = [
      'intro' => ['#markup' => '<p>' . $this->t('Pick an activity template to assign its teachers, groups and rooms. The arrows change the order of a template within its subject and period, as the legacy arrows did.') . '</p>'],
      'search' => $this->search($search, NULL),
    ];
    $sections = [];
    $destination = Url::fromRoute('ancora_academic.assignment_editor', [], ['query' => $search === '' ? [] : ['q' => $search]])->toString();
    foreach ($storage->loadMultiple($ids) as $template) {
      if (!$template instanceof ActivityTemplate || !$template->access('update')) {
        continue;
      }
      $label = (string) $template->label();
      if ($search !== '' && mb_stripos($label, $search) === FALSE) {
        continue;
      }
      $period = $template->get('period')->entity;
      $key = $template->getPlanId() . ':' . $template->get('period')->target_id;
      $sections[$key] ??= [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => sprintf('%s: %s', $template->get('plan')->entity?->label() ?? '?', $period?->label() ?? '?'),
        'list' => ['#theme' => 'item_list', '#items' => [], '#attributes' => ['class' => ['ancora-assign__templates']]],
      ];
      $teachers = count($this->assignments->assignments($template, 'teacher'));
      $rooms = count($this->assignments->assignments($template, 'room'));
      $item = [
        'link' => [
          '#type' => 'link',
          '#title' => $label,
          '#url' => Url::fromRoute('ancora_academic.assignment_editor', [], ['query' => ['template' => $template->id()] + ($search === '' ? [] : ['q' => $search])]),
        ],
        'counts' => [
          '#markup' => ' <span class="ancora-assign__counts">' . $this->t('@teachers teachers, @rooms rooms', [
            '@teachers' => $teachers,
            '@rooms' => $rooms,
          ]) . '</span>',
        ],
      ];
      foreach ([
        'up' => $this->t('Move @label up', ['@label' => $label]),
        'down' => $this->t('Move @label down', ['@label' => $label]),
      ] as $direction => $title) {
        $item[$direction] = [
          '#type' => 'link',
          '#title' => $direction === 'up' ? '↑' : '↓',
          '#url' => Url::fromRoute('ancora_academic.order.move', [
            'type' => 'template',
            'id' => $template->id(),
            'direction' => $direction,
          ], ['query' => ['destination' => $destination]]),
          '#attributes' => ['class' => ['ancora-assign__move'], 'aria-label' => $title, 'title' => $title],
          '#prefix' => ' ',
        ];
      }
      $item['#wrapper_attributes'] = ['data-search' => mb_strtolower($label)];
      $sections[$key]['list']['#items'][] = $item;
    }
    $build['templates'] = $sections === []
      ? ['#markup' => '<p>' . ($search === '' ? $this->t('You have no activity template yet.') : $this->t('No activity template matches the search.')) . '</p>']
      : $sections;
    return $build;
  }

}
