<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Form;

use Drupal\ancora_engine\JobRepository;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Requests the cancellation of a job.
 *
 * Inside the web request this only flags the job in the site's database; the
 * worker forwards the request to the engine (a generador.cancelar row) within
 * one poll interval, and escalates to pg_cancel_backend after the grace time.
 */
final class CancelJobForm extends ConfirmFormBase {

  use AutowireTrait;

  /**
   * The job id.
   */
  private int $jobId = 0;

  public function __construct(
    #[Autowire(service: 'ancora_engine.job_repository')]
    private readonly JobRepository $jobs,
  ) {}

  /**
   * Access: the requester of the job, or an engine administrator.
   */
  public static function access(AccountInterface $account, string|int $job): AccessResultInterface {
    $entry = \Drupal::service('ancora_engine.job_repository')->load((int) $job);
    if ($entry === NULL || $entry->state->isTerminal()) {
      return AccessResult::forbidden()->setCacheMaxAge(0);
    }
    if ($account->hasPermission('administer ancora engine')) {
      return AccessResult::allowed()->setCacheMaxAge(0);
    }
    $own = $account->isAuthenticated() && (int) $account->id() === $entry->requestedBy;
    return AccessResult::allowedIf($own && $account->hasPermission('request ancora engine jobs'))->setCacheMaxAge(0);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_engine_cancel_job';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string|int|null $job = NULL): array {
    $this->jobId = (int) $job;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Cancel generation job @id?', ['@id' => $this->jobId]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): TranslatableMarkup {
    return $this->t('A queued job never runs. A running job stops at its next check and keeps the placements made so far as a draft, never as a final timetable.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText(): TranslatableMarkup {
    return $this->t('Cancel the job');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return Url::fromRoute('ancora_engine.job', ['job' => $this->jobId]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {
      $this->jobs->requestCancel($this->jobId, (int) $this->currentUser()->id());
      $this->messenger()->addStatus($this->t('Cancellation of job @id requested.', ['@id' => $this->jobId]));
    }
    catch (\InvalidArgumentException) {
      $this->messenger()->addWarning($this->t('Job @id has already ended.', ['@id' => $this->jobId]));
    }
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
