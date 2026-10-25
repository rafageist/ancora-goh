<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Form;

use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\ancora_engine\JobRepository;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Queues a timetable generation. Nothing runs inside this request.
 */
final class EnqueueJobForm extends FormBase {

  use AutowireTrait;

  public function __construct(
    #[Autowire(service: 'ancora_engine.job_repository')]
    private readonly JobRepository $jobs,
    #[Autowire(service: 'ancora_engine.settings')]
    private readonly EngineSettings $settings,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_engine_enqueue_job';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $fields = [
      'specialty' => $this->t('Specialty id'),
      'level' => $this->t('Level id'),
      'period' => $this->t('Period id'),
    ];
    foreach ($fields as $key => $title) {
      $form[$key] = [
        '#type' => 'textfield',
        '#title' => $title,
        '#required' => TRUE,
        '#maxlength' => JobScope::MAX_LENGTH,
      ];
    }
    $form['forma'] = [
      '#type' => 'radios',
      '#title' => $this->t('Prioritize by'),
      '#options' => [
        1 => $this->t('Order of the activity classes'),
        2 => $this->t('Number of activities of the subjects'),
      ],
      '#default_value' => 1,
      '#required' => TRUE,
    ];
    $form['compactar'] = [
      '#type' => 'number',
      '#title' => $this->t('Compaction towards the start of the period'),
      '#min' => 0,
      '#max' => 10,
      '#step' => 1,
      '#default_value' => 0,
      '#required' => TRUE,
    ];
    $form['parche'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Group activities by class'),
    ];
    $form['parche1'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Maximize the combination of activities per day'),
    ];
    $form['buscalugar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Prefer the most used place (instead of the nearest)'),
    ];
    $form['time_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Time limit (seconds)'),
      '#min' => 1,
      '#max' => $this->settings->maxTimeLimit(),
      '#default_value' => $this->settings->timeLimit(),
      '#required' => TRUE,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Queue'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    try {
      new JobScope(
        (string) $form_state->getValue('specialty'),
        (string) $form_state->getValue('level'),
        (string) $form_state->getValue('period'),
      );
    }
    catch (\InvalidArgumentException $e) {
      $form_state->setErrorByName('specialty', $e->getMessage());
    }
    try {
      JobOptions::fromArray($this->optionValues($form_state));
    }
    catch (\InvalidArgumentException $e) {
      $form_state->setErrorByName('forma', $e->getMessage());
    }
    $limit = (string) $form_state->getValue('time_limit');
    if (!ctype_digit($limit) || (int) $limit < 1 || (int) $limit > $this->settings->maxTimeLimit()) {
      $form_state->setErrorByName('time_limit', $this->t('The time limit must be between 1 and @max seconds.', ['@max' => $this->settings->maxTimeLimit()]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $id = $this->jobs->enqueue(
      new JobScope(
        (string) $form_state->getValue('specialty'),
        (string) $form_state->getValue('level'),
        (string) $form_state->getValue('period'),
      ),
      JobOptions::fromArray($this->optionValues($form_state)),
      (int) $form_state->getValue('time_limit'),
      (int) $this->currentUser()->id(),
    );
    $this->messenger()->addStatus($this->t('Job @id queued. The engine worker runs it in the background.', ['@id' => $id]));
    $form_state->setRedirect('ancora_engine.job', ['job' => $id]);
  }

  /**
   * The generar() options from the submitted values.
   */
  private function optionValues(FormStateInterface $form_state): array {
    return [
      'forma' => (string) $form_state->getValue('forma'),
      'compactar' => (string) $form_state->getValue('compactar'),
      'parche' => (bool) $form_state->getValue('parche'),
      'parche1' => (bool) $form_state->getValue('parche1'),
      'buscalugar' => (bool) $form_state->getValue('buscalugar'),
    ];
  }

}
