<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Form;

use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\Entity\PlacementChange;
use Drupal\ancora_scheduling\Timetable\PlacementModification;
use Drupal\ancora_scheduling\Timetable\PlacementModificationException;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Move one saved placement to a checked day and slot (#130).
 *
 * The page lists the destinations the worker checked (PlacementCheckRunner)
 * as a grid of buttons; a cell that is not valid is a disabled button with
 * its reasons. Choosing a cell shows a preview; "Move here" commits it
 * (PlacementModification::commit()). The last move can be undone (a new
 * check, then a commit back). Every step is a button of this form (CSRF
 * form token, the placement's update access): it works without JavaScript
 * and from the keyboard. placement-move.js only reloads the page while the
 * check runs and lets the activity be dragged onto a valid cell, which
 * presses that cell's button.
 *
 * The destinations are valid within the period as the scheduler sees it;
 * other plans whose dates overlap are not checked (case 150), and the page
 * says so.
 */
final class PlacementMoveForm extends FormBase {

  /**
   * The move command.
   */
  protected PlacementModification $modification;

  /**
   * The job repository.
   */
  protected JobRepository $jobs;

  /**
   * The private tempstore factory (one-time feedback).
   */
  protected PrivateTempStoreFactory $tempStoreFactory;

  /**
   * The date formatter.
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(PlacementModification $modification, JobRepository $jobs, PrivateTempStoreFactory $temp_store_factory, DateFormatterInterface $date_formatter, EntityTypeManagerInterface $entity_type_manager) {
    $this->modification = $modification;
    $this->jobs = $jobs;
    $this->tempStoreFactory = $temp_store_factory;
    $this->dateFormatter = $date_formatter;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get(PlacementModification::class),
      $container->get('ancora_engine.job_repository'),
      $container->get('tempstore.private'),
      $container->get('date.formatter'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_scheduling_placement_move';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?Placement $ancora_placement = NULL): array {
    $placement = $ancora_placement;
    $form['#cache']['max-age'] = 0;
    $form['#attributes']['class'][] = 'ancora-move';
    $form['#attached']['library'][] = 'ancora_scheduling/placement_move';
    if ($placement === NULL) {
      return $form;
    }
    $form_state->set('placement', (int) $placement->id());
    if (!$this->modification->isAvailable()) {
      $form['mode'] = [
        '#markup' => '<p class="ancora-move__unavailable" role="note">' . $this->t('Moving placements is offered in the workspace mode of the engine only. This site runs the two-database mode, in which every run deletes the placements of its scope before it starts, so a move would be lost.') . '</p>',
      ];
      return $form;
    }

    $query = $this->getRequest()->query;
    $check = $this->modification->check($placement, (string) $query->get('check', ''));
    $undo = $this->undoTarget($placement, (string) $query->get('undo', ''));
    $form_state->set('check', $check === NULL ? NULL : (int) $check['id']);
    $form_state->set('undo', $undo === NULL ? NULL : (int) $undo->id());

    $form['feedback'] = $this->feedback((int) $placement->id());
    $form['summary'] = $this->summary($placement);
    $form['note'] = [
      '#markup' => '<p class="ancora-move__note" role="note"><strong>' . $this->t('What is checked:') . '</strong> ' . $this->t('the destinations are valid within this period, as the scheduler sees it: the period, the activity type, the subject and their restrictions, and every activity of the period for the cohorts of the group, the teacher, the room and the other resources. They are <strong>not</strong> checked against other plans whose dates overlap: a teacher or room shared with another plan may still be busy there.') . '</p>',
    ];
    if (($job = $this->jobs->activeForSite()) !== NULL) {
      $form['job'] = [
        '#markup' => '<p class="ancora-move__job" role="status">' . $this->t('Timetable run @id is queued or running. You can check destinations, but a move is refused until the run ends.', ['@id' => $job->id]) . '</p>',
      ];
    }

    $preview = $form_state->get('preview');
    if ($check === NULL) {
      $form['actions']['check'] = $this->button($this->t('Find the valid destinations'), 'check');
    }
    elseif (in_array($check['state'], ['queued', 'running'], TRUE)) {
      $form['pending'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['ancora-move__pending'],
          'role' => 'status',
          'aria-busy' => 'true',
          'data-ancora-check-pending' => $check['id'],
        ],
        'text' => ['#markup' => '<p>' . $this->t('Checking the destinations with the scheduler… This usually takes a few seconds.') . '</p>'],
        'refresh' => [
          '#type' => 'link',
          '#title' => $this->t('Refresh'),
          '#url' => $this->url($placement, ['check' => $check['id']] + ($undo ? ['undo' => $undo->id()] : [])),
          '#attributes' => ['class' => ['button']],
        ],
      ];
    }
    elseif ($check['state'] === 'failed' || $check['expired']) {
      $form['failed'] = [
        '#markup' => '<p class="ancora-move__error" role="alert">' . ($check['expired']
          ? $this->t('That check is too old. Check again.')
          : $this->t('The check failed: @error', ['@error' => (string) $check['error']])) . '</p>',
      ];
      $form['actions']['check'] = $this->button($this->t('Check again'), 'check');
    }
    elseif (is_array($preview)) {
      $form['preview'] = $this->preview($placement, $check, $preview[0], $preview[1], $undo);
    }
    else {
      $form['grid'] = $this->grid($placement, $check, $undo);
    }

    $form['history'] = $this->history($placement);
    $form['back'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to the timetable'),
      '#url' => Url::fromRoute('ancora_scheduling.timetable', ['ancora_plan' => $placement->get('plan')->target_id], ['query' => ['period' => $placement->get('period')->target_id]]),
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $placement = $this->entityTypeManager->getStorage('ancora_placement')->load((int) $form_state->get('placement'));
    if (!$placement instanceof Placement) {
      return;
    }
    $op = $form_state->getTriggeringElement()['#ancora_move'] ?? [];
    $undo = $form_state->get('undo') === NULL ? NULL : $this->entityTypeManager->getStorage('ancora_placement_change')->load($form_state->get('undo'));
    try {
      switch ($op[0] ?? '') {
        case 'check':
        case 'undo':
          $id = $this->modification->requestCheck($placement, $this->currentUser());
          $query = ['check' => $id];
          if ($op[0] === 'undo' && ($last = $this->modification->lastChange($placement)) !== NULL) {
            $query['undo'] = $last->id();
          }
          $form_state->setRedirectUrl($this->url($placement, $query));
          return;

        case 'preview':
          $form_state->set('preview', [(int) $op[1], (int) $op[2]]);
          $form_state->setRebuild();
          return;

        case 'cancel':
          $form_state->setRedirectUrl($this->url($placement, ['check' => $form_state->get('check')] + ($undo ? ['undo' => $undo->id()] : []), 'ancora-move-grid'));
          return;

        case 'commit':
          $change = $undo instanceof PlacementChange
            ? $this->modification->undo($undo, (int) $form_state->get('check'), $this->currentUser())
            : $this->modification->commit($placement, (int) $form_state->get('check'), (int) $op[1], (int) $op[2], $this->currentUser());
          $this->store()->set('feedback:' . $placement->id(), [
            'type' => 'status',
            'message' => (string) ($undo ? $this->t('Moved it back to day @day, slot @slot.', [
              '@day' => $op[1],
              '@slot' => $op[2],
            ])
              : $this->t('Moved it to day @day, slot @slot. The next timetable run keeps it there.', [
                '@day' => $op[1],
                '@slot' => $op[2],
              ])),
            'change' => (int) $change->id(),
          ]);
          $form_state->setRedirectUrl($this->url($placement, [], 'ancora-move-feedback'));
          return;
      }
    }
    catch (PlacementModificationException $e) {
      $this->store()->set('feedback:' . $placement->id(), [
        'type' => 'error',
        'message' => (string) $this->modification->message($e),
      ]);
      $form_state->setRedirectUrl($this->url($placement, $form_state->get('check') ? ['check' => $form_state->get('check')] : [], 'ancora-move-feedback'));
    }
  }

  /**
   * The placement: what it is, where it is now.
   */
  private function summary(Placement $placement): array {
    $label = static fn (string $field): string => implode(', ', array_map(static fn ($e): string => (string) $e->label(), $placement->get($field)->referencedEntities()));
    $template = $placement->getTemplate();
    return [
      '#theme' => 'item_list',
      '#attributes' => ['class' => ['ancora-move__summary']],
      '#items' => [
        $this->t('Activity: @activity', ['@activity' => $template?->label() ?? '?']),
        $this->t('Group: @group', ['@group' => $label('student_group')]),
        $this->t('Teacher: @teacher', ['@teacher' => $label('teacher') ?: $this->t('none')]),
        $this->t('Room: @room', ['@room' => $label('room') ?: $this->t('none')]),
        $this->t('Other resources: @resources', ['@resources' => $label('other_resources') ?: $this->t('none')]),
        $this->t('Now: day @day, slot @slot', [
          '@day' => $placement->get('day')->value,
          '@slot' => $placement->get('slot')->value,
        ]),
      ],
      '#suffix' => '<p class="ancora-move__keep">' . $this->t('Only the day and the first slot change: the group, the teacher, the room and the other resources stay.') . '</p>',
    ];
  }

  /**
   * The destination grid: one button per cell, disabled with its reasons.
   */
  private function grid(Placement $placement, array $check, ?PlacementChange $undo): array {
    $cells = $check['result']['cells'];
    $days = array_values(array_unique(array_column($cells, 'day')));
    $slots = array_values(array_unique(array_column($cells, 'slot')));
    $from = $check['result']['from'];
    $valid = count(array_filter($cells, static fn (array $c): bool => $c['ok'] && [$c['day'], $c['slot']] !== [
      $from['day'],
      $from['slot'],
    ]));
    $grid = [
      '#type' => 'container',
      '#attributes' => ['class' => ['ancora-move__grid-wrapper'], 'id' => 'ancora-move-grid', 'tabindex' => '-1'],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $undo ? $this->t('Undo the last move') : $this->t('Destinations in this period'),
      ],
      'help' => [
        '#markup' => '<p>' . ($undo
          ? $this->t('An undo returns the activity to day @day, slot @slot, where it was before its last move, if that cell is still valid.', [
            '@day' => $undo->get('from_day')->value,
            '@slot' => $undo->get('from_slot')->value,
          ])
          : $this->formatPlural($valid, 'One cell is a valid destination. Choose it to see the move before it is made.', '@count cells are valid destinations. Choose one to see the move before it is made.')) . '</p>',
      ],
      'drag' => [
        '#markup' => '<p class="ancora-move__drag" draggable="true" data-ancora-move-drag="1" hidden>' . $this->t('Or drag this activity onto a valid cell.') . '</p>',
      ],
      'table' => [
        '#type' => 'table',
        '#caption' => $this->t('Day by slot. A disabled cell says why it is not a destination.'),
        '#header' => [$this->t('Slot'), ...array_map(fn (int $d) => $this->t('Day @day', ['@day' => $d]), $days)],
        '#attributes' => ['class' => ['ancora-move__grid']],
      ],
    ];
    foreach ($slots as $slot) {
      $row = ['slot' => ['#markup' => (string) $slot, '#wrapper_attributes' => ['scope' => 'row']]];
      foreach ($days as $day) {
        $cell = PlacementModification::cell($check, $day, $slot);
        $here = [$day, $slot] === [$from['day'], $from['slot']];
        $allowed = $cell['ok'] && !$here && ($undo === NULL || [$day, $slot] === [
          (int) $undo->get('from_day')->value,
          (int) $undo->get('from_slot')->value,
        ]);
        $id = "ancora-move-cell-$day-$slot";
        $why = match (TRUE) {
          $here => [$this->t('it is here now')],
          !$cell['ok'] => array_map($this->modification->label(...), $cell['reasons']),
          !$allowed => [$this->t('an undo goes back to where it was')],
          default => [],
        };
        $warnings = array_map($this->modification->label(...), $cell['warnings']);
        $row[$id] = [
          '#wrapper_attributes' => [
            'data-day' => $day,
            'data-slot' => $slot,
            'class' => [$allowed ? 'is-valid' : 'is-invalid'],
          ],
          'button' => $this->button($this->t('Day @day, slot @slot', ['@day' => $day, '@slot' => $slot]), 'preview', $day, $slot) + [
            '#disabled' => !$allowed,
            '#attributes' => [
              'aria-describedby' => "$id-why",
              'data-ancora-move-cell' => $allowed ? '1' : '0',
              'class' => ['ancora-move__cell'],
            ],
          ],
          'why' => [
            '#markup' => '<span class="ancora-move__why" id="' . $id . '-why">'
            . ($why === [] ? $this->t('Valid.') : $this->t('Not a destination: @why.', ['@why' => implode('; ', array_map('strval', $why))]))
            . ($warnings === [] ? '' : ' <span class="ancora-move__warning">' . $this->t('Warning: @warnings.', ['@warnings' => implode('; ', array_map('strval', $warnings))]) . '</span>')
            . '</span>',
          ],
        ];
      }
      $grid['table'][$slot] = $row;
    }
    return $grid;
  }

  /**
   * The preview of one move, with the confirmation.
   */
  private function preview(Placement $placement, array $check, int $day, int $slot, ?PlacementChange $undo): array {
    $cell = PlacementModification::cell($check, $day, $slot);
    $from = $check['result']['from'];
    $warnings = array_map($this->modification->label(...), $cell['warnings'] ?? []);
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ancora-move__preview'],
        'id' => 'ancora-move-preview',
        'role' => 'group',
        'aria-labelledby' => 'ancora-move-preview-title',
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Check the move'),
        '#attributes' => ['id' => 'ancora-move-preview-title'],
      ],
      'what' => [
        '#markup' => '<p>' . $this->t('Move %activity of %group from day @fd, slot @fs to <strong>day @day, slot @slot</strong>.', [
          '%activity' => $placement->getTemplate()?->label() ?? '?',
          '%group' => $placement->get('student_group')->entity?->label() ?? '?',
          '@fd' => $from['day'],
          '@fs' => $from['slot'],
          '@day' => $day,
          '@slot' => $slot,
        ]) . '</p>',
      ],
      'warnings' => $warnings === [] ? [] : [
        '#markup' => '<p class="ancora-move__warning" role="note">' . $this->t('Warning: @warnings. The scheduler would avoid this, but the move is allowed.', ['@warnings' => implode('; ', array_map('strval', $warnings))]) . '</p>',
      ],
      'scope' => [
        '#markup' => '<p>' . $this->t('Valid within this period; not checked against other plans whose dates overlap.') . '</p>',
      ],
      'confirm' => $this->button($undo ? $this->t('Move it back here') : $this->t('Move here'), 'commit', $day, $slot) + [
        '#attributes' => ['class' => ['button--primary'], 'autofocus' => 'autofocus'],
      ],
      'cancel' => $this->button($this->t('Choose another cell'), 'cancel'),
    ];
  }

  /**
   * The placement's moves, newest first, with the undo of the last one.
   */
  private function history(Placement $placement): array {
    $changes = $this->modification->history($placement);
    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['ancora-move__history']],
      'title' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('Moves of this activity')],
    ];
    if ($changes === []) {
      $build['none'] = ['#markup' => '<p>' . $this->t('It was not moved by hand: it is where the scheduler placed it.') . '</p>'];
      return $build;
    }
    $items = [];
    foreach ($changes as $change) {
      $items[] = $this->t('@when, @who: day @fd, slot @fs → day @td, slot @ts@undo', [
        '@when' => $this->dateFormatter->format((int) $change->get('created')->value, 'short'),
        '@who' => $change->get('uid')->entity?->getDisplayName() ?? '?',
        '@fd' => $change->get('from_day')->value,
        '@fs' => $change->get('from_slot')->value,
        '@td' => $change->get('to_day')->value,
        '@ts' => $change->get('to_slot')->value,
        '@undo' => $change->get('reason')->value === 'undo' ? ' (' . $this->t('undo') . ')' : '',
      ]);
    }
    $build['list'] = ['#theme' => 'item_list', '#items' => $items];
    $build['undo'] = $this->button($this->t('Undo the last move'), 'undo');
    return $build;
  }

  /**
   * The one-time feedback of the last action.
   */
  private function feedback(int $placement): array {
    $feedback = $this->store()->get('feedback:' . $placement);
    $this->store()->delete('feedback:' . $placement);
    $build = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ancora-move-feedback', 'tabindex' => '-1', 'class' => ['ancora-move__feedback']],
    ];
    if (is_array($feedback)) {
      $error = $feedback['type'] === 'error';
      $build['#attributes']['role'] = $error ? 'alert' : 'status';
      $build['#attributes']['class'][] = $error ? 'ancora-move__feedback--error' : 'ancora-move__feedback--status';
      $build['message'] = ['#plain_text' => $feedback['message']];
    }
    return $build;
  }

  /**
   * The change an undo goes back from, if it is the placement's last.
   */
  private function undoTarget(Placement $placement, string $id): ?PlacementChange {
    if (!ctype_digit($id)) {
      return NULL;
    }
    $last = $this->modification->lastChange($placement);
    return $last !== NULL && (int) $last->id() === (int) $id ? $last : NULL;
  }

  /**
   * A submit button of one step, without validation of other fields.
   */
  private function button(mixed $title, string $op, int ...$cell): array {
    return [
      '#type' => 'submit',
      '#value' => $title,
      '#name' => 'op_' . $op . ($cell === [] ? '' : '_' . implode('_', $cell)),
      '#ancora_move' => [$op, ...$cell],
      '#limit_validation_errors' => [],
      '#submit' => ['::submitForm'],
    ];
  }

  /**
   * The move page with a query and a fragment.
   */
  private function url(Placement $placement, array $query = [], ?string $fragment = NULL): Url {
    $options = ['query' => array_filter($query, static fn ($v): bool => $v !== NULL)];
    if ($fragment !== NULL) {
      $options['fragment'] = $fragment;
    }
    return Url::fromRoute('ancora_scheduling.placement_move', ['ancora_placement' => $placement->id()], $options);
  }

  /**
   * The feedback store of the current user.
   */
  private function store() {
    return $this->tempStoreFactory->get('ancora_scheduling_placement_move');
  }

}
