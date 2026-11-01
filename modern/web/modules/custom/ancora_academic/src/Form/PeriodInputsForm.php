<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\PeriodInputs;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The editors of the other engine inputs of a period (D9b).
 *
 * One form, three editors (the route default "input"):
 * - breaks: a length per slot after which a break comes (legacy receso;
 *   no legacy editor, A-4/P-2);
 * - zones: the legacy priority zone grid of an activity type in a period
 *   (form_zpriori.tpl): one select 1..days x slots per cell; cells of zone
 *   1 are not stored (custom_set_zpriori.php);
 * - limits: the maximum daily load of each teacher and cohort (legacy
 *   higiene_para_*; no legacy editor, A-4/P-2); empty means no limit.
 * The period (and, for zones, the activity type) is in the query.
 */
final class PeriodInputsForm extends FormBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly PeriodInputs $inputs,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('entity_type.manager'), $container->get(PeriodInputs::class));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_academic_period_inputs';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $input = 'breaks'): array {
    $query = $this->getRequest()->query;
    $period = $this->editable('ancora_period', (string) $query->get('period', ''));
    $type = $input === 'zones' ? $this->editable('ancora_activity_type', (string) $query->get('activity_type', '')) : NULL;
    $form_state->set('input', $input);
    $form['#cache']['contexts'] = ['url.query_args', 'user'];
    $intro = [
      'breaks' => $this->t('A break after a slot moves the start hour of every later slot by its length. It changes the hours shown, never where the activities go.'),
      'zones' => $this->t('The priority zone of each cell for an activity type (1 is the first zone). The engine reads the zones only with a run option that the planner screens do not send, as in the legacy application.'),
      'limits' => $this->t('The largest total activity cost a teacher or cohort may have in one day of the period. Leave a value empty for no limit.'),
    ];
    $form['intro'] = ['#markup' => '<p>' . $intro[$input] . '</p>'];
    $form['choose'] = [
      '#type' => 'details',
      '#title' => $this->t('Period'),
      '#open' => $period === NULL || ($input === 'zones' && $type === NULL),
    ];
    $form['choose']['period'] = [
      '#type' => 'select',
      '#title' => $this->t('Period'),
      '#options' => $this->options('ancora_period', TRUE),
      '#empty_option' => $this->t('- Choose a period -'),
      '#default_value' => $period?->id(),
    ];
    if ($input === 'zones') {
      $form['choose']['activity_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Activity type'),
        '#options' => $this->options('ancora_activity_type'),
        '#empty_option' => $this->t('- Choose -'),
        '#default_value' => $type?->id(),
      ];
    }
    $form['choose']['show'] = [
      '#type' => 'submit',
      '#value' => $this->t('Show'),
      '#submit' => ['::show'],
      '#limit_validation_errors' => [['period'], ['activity_type']],
    ];
    if (!$period instanceof Period || ($input === 'zones' && $type === NULL)) {
      return $form;
    }
    $form_state->set('selection', [(int) $period->id(), $type?->id()]);
    match ($input) {
      'breaks' => $this->breaksTable($form, $period),
      'zones' => $this->zonesGrid($form, $period, (int) $type->id()),
      default => $this->limitsTables($form, $period),
    };
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['save'] = ['#type' => 'submit', '#value' => $this->t('Save'), '#button_type' => 'primary'];
    return $form;
  }

  /**
   * The breaks of a period: one length per slot.
   */
  private function breaksTable(array &$form, Period $period): void {
    $breaks = $this->inputs->breaks($period->id());
    $form['breaks'] = [
      '#type' => 'table',
      '#caption' => $this->t('Breaks of %period', ['%period' => $period->label()]),
      '#header' => [$this->t('After slot'), $this->t('Length in hours')],
    ];
    for ($slot = 1; $slot < $period->getSlotsPerDay(); $slot++) {
      $form['breaks'][$slot]['slot'] = ['#markup' => (string) $slot];
      $form['breaks'][$slot]['length'] = [
        '#type' => 'number',
        '#title' => $this->t('Break after slot @slot, in hours', ['@slot' => $slot]),
        '#title_display' => 'invisible',
        '#step' => 0.01,
        '#default_value' => isset($breaks[$slot]) ? $breaks[$slot]->getLength() : NULL,
      ];
    }
  }

  /**
   * The zone grid of an activity type in a period.
   */
  private function zonesGrid(array &$form, Period $period, int $type_id): void {
    $zones = $this->inputs->zones($type_id, $period->id());
    $days = $period->getDays();
    $slots = $period->getSlotsPerDay();
    $options = array_combine(range(1, max(1, $days * $slots)), range(1, max(1, $days * $slots)));
    $header = [$this->t('Slot')];
    for ($day = 1; $day <= $days; $day++) {
      $header[] = $this->t('Day @day', ['@day' => $day]);
    }
    $form['zones'] = [
      '#type' => 'table',
      '#caption' => $this->t('Priority zones in %period', ['%period' => $period->label()]),
      '#header' => $header,
    ];
    for ($slot = 1; $slot <= $slots; $slot++) {
      $form['zones'][$slot]['slot'] = ['#markup' => (string) $slot];
      for ($day = 1; $day <= $days; $day++) {
        $form['zones'][$slot]["d$day"] = [
          '#type' => 'select',
          '#title' => $this->t('Zone of day @day, slot @slot', ['@day' => $day, '@slot' => $slot]),
          '#title_display' => 'invisible',
          '#options' => $options,
          '#default_value' => $zones["$day:$slot"] ?? 1,
        ];
      }
    }
  }

  /**
   * The daily load limits of the teachers and cohorts.
   */
  private function limitsTables(array &$form, Period $period): void {
    foreach (['teacher' => $this->t('Teachers'), 'cohort' => $this->t('Cohorts')] as $kind => $caption) {
      $limits = $this->inputs->limits($period->id(), $kind);
      $form["limits_$kind"] = [
        '#type' => 'table',
        '#caption' => $caption,
        '#header' => [$this->t('Name'), $this->t('Maximum daily load')],
      ];
      foreach ($this->options("ancora_$kind", FALSE, TRUE) as $id => $label) {
        $form["limits_$kind"][$id]['name'] = ['#markup' => $label];
        $form["limits_$kind"][$id]['limit'] = [
          '#type' => 'number',
          '#title' => $this->t('Maximum daily load of @name', ['@name' => $label]),
          '#title_display' => 'invisible',
          '#step' => 1,
          '#default_value' => $limits[$id] ?? NULL,
        ];
      }
    }
  }

  /**
   * Submit handler of "Show": the choice goes into the query.
   */
  public function show(array &$form, FormStateInterface $form_state): void {
    $query = array_filter([
      'period' => $form_state->getValue('period'),
      'activity_type' => $form_state->getValue('activity_type'),
    ], static fn ($v): bool => $v !== NULL && $v !== '');
    $form_state->setRedirectUrl(Url::fromRoute('ancora_academic.period_inputs.' . $form_state->get('input'), [], ['query' => $query]));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    [$period_id, $type_id] = $form_state->get('selection') ?? [NULL, NULL];
    $period = $period_id === NULL ? NULL : $this->entityTypeManager->getStorage('ancora_period')->load($period_id);
    if (!$period instanceof Period) {
      return;
    }
    $number = static fn ($v) => $v === NULL || $v === '' ? NULL : $v;
    $messages = [];
    switch ($form_state->get('input')) {
      case 'breaks':
        $lengths = [];
        foreach ((array) $form_state->getValue('breaks') as $slot => $row) {
          $lengths[(int) $slot] = $number($row['length'] ?? NULL) === NULL ? NULL : (float) $row['length'];
        }
        $messages = $this->inputs->saveBreaks($period, $lengths);
        break;

      case 'zones':
        $zones = [];
        foreach ((array) $form_state->getValue('zones') as $slot => $row) {
          foreach ((array) $row as $key => $zone) {
            if (preg_match('/^d(\d+)$/', (string) $key, $m)) {
              $zones[$m[1] . ':' . $slot] = (int) $zone;
            }
          }
        }
        $messages = $this->inputs->saveZones($type_id, $period, $zones);
        break;

      default:
        foreach (['teacher', 'cohort'] as $kind) {
          $limits = [];
          foreach ((array) $form_state->getValue("limits_$kind") as $id => $row) {
            $limits[(int) $id] = $number($row['limit'] ?? NULL) === NULL ? NULL : (int) $row['limit'];
          }
          $messages = [...$messages, ...$this->inputs->saveLimits($period, $kind, $limits)];
        }
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
   * The user's records of a type.
   *
   * @return array<int, string>
   *   Id => label (periods: "plan: period").
   */
  private function options(string $entity_type_id, bool $with_plan = FALSE, bool $editable = FALSE): array {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $options = [];
    foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(TRUE)->sort('code')->execute()) as $id => $entity) {
      if ($editable && !$entity->access('update')) {
        continue;
      }
      $plan = $with_plan ? $entity->get('plan')->entity : NULL;
      $options[$id] = ($plan ? $plan->label() . ': ' : '') . $entity->label();
    }
    return $options;
  }

}
