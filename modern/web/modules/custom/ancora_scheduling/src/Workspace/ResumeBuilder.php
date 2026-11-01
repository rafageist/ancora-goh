<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Workspace;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * The Overview's "Resume <last plan>" action (#141): a lazy builder.
 *
 * The record lives in user.data, which has no cache tag, so the action is
 * rendered on every request (a placeholder, max-age 0) and the rest of the
 * Overview stays cacheable. The plan's access is checked again first
 * (ResumeStore::resume()): a deleted or no longer visible plan is simply
 * not offered.
 */
final class ResumeBuilder implements TrustedCallbackInterface {

  use StringTranslationTrait;

  public function __construct(
    private readonly ResumeStore $store,
    private readonly AccountInterface $currentUser,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['build'];
  }

  /**
   * The resume action, or nothing.
   */
  public function build(): array {
    $build = ['#cache' => ['max-age' => 0]];
    $resume = $this->store->resume($this->currentUser);
    if ($resume === NULL) {
      return $build;
    }
    $query = $resume['period'] === NULL ? [] : ['period' => $resume['period']->id()];
    $url = Url::fromRoute('ancora_scheduling.plan_workspace', ['ancora_plan' => $resume['plan']->id()], ['query' => $query]);
    $label = $resume['period'] === NULL
      ? $this->t('Resume @plan', ['@plan' => $resume['plan']->label()])
      : $this->t('Resume @plan, @period', ['@plan' => $resume['plan']->label(), '@period' => $resume['period']->label()]);
    $build['resume'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['ancora-resume']],
      'link' => [
        '#type' => 'link',
        '#title' => $label,
        '#url' => $url,
        '#attributes' => ['class' => ['button', 'button--primary', 'ancora-resume__link'], 'data-ancora-resume' => ''],
      ],
      'note' => [
        '#markup' => '<p class="ancora-resume__note">' . $this->t('Opens the planning workspace where you left it.') . '</p>',
      ],
    ];
    return $build;
  }

}
