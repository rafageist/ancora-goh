<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Form;

use Drupal\ancora_academic\Entity\Level;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Specialty;
use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Requests a timetable: plan, period, specialty and level.
 *
 * The form only queues a job (with the records as its source). Nothing is
 * exported, synced or generated inside the request; the engine worker does
 * all of that in the background.
 */
final class RequestRunForm extends FormBase {

  use AutowireTrait;

  /**
   * The record types the form picks, by form key.
   */
  private const TYPES = [
    'period' => 'ancora_period',
    'specialty' => 'ancora_specialty',
    'level' => 'ancora_level',
  ];

  public function __construct(
    #[Autowire(service: 'ancora_engine.job_repository')]
    private readonly JobRepository $jobs,
    #[Autowire(service: 'ancora_engine.settings')]
    private readonly EngineSettings $settings,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_scheduling_request_run';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $plans = $this->viewable('ancora_plan');
    $form['intro'] = [
      '#markup' => '<p>' . $this->t('The engine worker generates the timetable in the background. The planning data of the whole plan is read when the run starts.') . '</p>',
    ];
    if ($plans === []) {
      $form['empty'] = ['#markup' => '<p>' . $this->t('There is no plan yet.') . '</p>'];
      return $form;
    }
    $requested = (int) $this->getRequest()->query->get('plan');
    $form['plan'] = [
      '#type' => 'select',
      '#title' => $this->t('Plan'),
      '#options' => array_map(static fn (Plan $plan): string => $plan->label() . ' (' . $plan->getCode() . ')', $plans),
      '#default_value' => isset($plans[$requested]) ? $requested : array_key_first($plans),
      '#required' => TRUE,
    ];
    $titles = [
      'period' => $this->t('Period'),
      'specialty' => $this->t('Specialty'),
      'level' => $this->t('Level'),
    ];
    foreach (self::TYPES as $key => $entity_type_id) {
      // Periods are grouped by plan; specialties and levels are the user's
      // own (an administrator sees every user's, grouped by owner). The
      // validation refuses a period of another plan, or a specialty or
      // level of another user than the plan's creator.
      $options = [];
      foreach ($this->viewable($entity_type_id) as $id => $record) {
        if ($key === 'period') {
          $plan = $plans[$record->getPlanId()] ?? NULL;
          $group = $plan === NULL ? NULL : (string) $plan->label();
        }
        else {
          $group = (string) ($record->getOwner()?->getDisplayName() ?? '');
        }
        if ($group !== NULL) {
          $options[$group][$id] = $record->label() . ' (' . $record->getCode() . ')';
        }
      }
      $form[$key] = [
        '#type' => 'select',
        '#title' => $titles[$key],
        '#options' => count($options) === 1 ? reset($options) : $options,
        '#required' => TRUE,
      ];
    }
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Request the timetable'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $records = $this->records($form_state);
    if ($records === NULL) {
      $form_state->setErrorByName('plan', $this->t('Pick a plan, a period, a specialty and a level.'));
      return;
    }
    [$plan, $period, $specialty, $level] = $records;
    if ($period->getPlanId() !== (int) $plan->id()) {
      $form_state->setErrorByName('period', $this->t('%record is not part of plan %plan.', [
        '%record' => $period->label(),
        '%plan' => $plan->label(),
      ]));
      return;
    }
    foreach (['specialty' => $specialty, 'level' => $level] as $key => $record) {
      if ($record->getCreatorId() !== $plan->getCreatorId()) {
        $form_state->setErrorByName($key, $this->t('%record does not belong to the creator of plan %plan.', [
          '%record' => $record->label(),
          '%plan' => $plan->label(),
        ]));
        return;
      }
    }
    $scope = ScopeMapper::scope($plan, $period, $specialty, $level);
    $active = $this->jobs->activeForScope($scope);
    if ($active !== NULL) {
      $form_state->setErrorByName('plan', $this->t('A run for this period, specialty and level is already @state (job @id).', [
        '@state' => $active->state->value,
        '@id' => $active->id,
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    [$plan, $period, $specialty, $level] = $this->records($form_state);
    $id = $this->jobs->enqueue(
      ScopeMapper::scope($plan, $period, $specialty, $level),
      new JobOptions(),
      $this->settings->timeLimit(),
      (int) $this->currentUser()->id(),
      NULL,
      ScopeMapper::source($plan, $period, $specialty, $level),
    );
    $this->messenger()->addStatus($this->t('Timetable requested (job @id). The engine worker runs it in the background.', ['@id' => $id]));
    $form_state->setRedirect('ancora_scheduling.job', ['job' => $id]);
  }

  /**
   * The picked records, each one viewable by the user, or NULL.
   *
   * @return array{0: \Drupal\ancora_academic\Entity\Plan, 1: \Drupal\ancora_academic\Entity\Period, 2: \Drupal\ancora_academic\Entity\Specialty, 3: \Drupal\ancora_academic\Entity\Level}|null
   *   The plan, period, specialty and level.
   */
  private function records(FormStateInterface $form_state): ?array {
    $storage = fn (string $type) => $this->entityTypeManager->getStorage($type);
    $ids = ['plan' => 'ancora_plan'] + self::TYPES;
    $records = [];
    foreach ($ids as $key => $entity_type_id) {
      $value = (string) $form_state->getValue($key);
      $record = ctype_digit($value) ? $storage($entity_type_id)->load((int) $value) : NULL;
      if ($record === NULL || !$record->access('view')) {
        return NULL;
      }
      $records[] = $record;
    }
    [$plan, $period, $specialty, $level] = $records;
    if (!$plan instanceof Plan || !$period instanceof Period || !$specialty instanceof Specialty || !$level instanceof Level) {
      return NULL;
    }
    return $records;
  }

  /**
   * The records of a type the user may view, by id, sorted by label.
   *
   * @return \Drupal\ancora_academic\AcademicEntityInterface[]
   *   The records.
   */
  private function viewable(string $entityTypeId): array {
    $storage = $this->entityTypeManager->getStorage($entityTypeId);
    $ids = $storage->getQuery()->accessCheck(TRUE)->sort('label')->execute();
    return array_filter($storage->loadMultiple($ids), static fn ($record): bool => $record->access('view'));
  }

}
