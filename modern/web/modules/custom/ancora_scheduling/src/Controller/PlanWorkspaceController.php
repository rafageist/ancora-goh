<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Controller;

use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_scheduling\Workspace\PlanWorkspace;
use Drupal\ancora_scheduling\Workspace\ResumeStore;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The integrated planning workspace of a plan (#141, S1a): read-only.
 *
 * The page: the whole workspace, one full page; it records the plan for the
 * Overview's "Resume" (user.data, ResumeStore), so it is not cached.
 * The regions endpoint: the same regions as an HTML fragment for the workspace
 * script, with the same access, the same query validation and the cache
 * metadata of its content (user, permissions, query arguments, tags).
 */
final class PlanWorkspaceController extends ControllerBase {

  use AutowireTrait;

  public function __construct(
    private readonly PlanWorkspace $workspace,
    private readonly ResumeStore $resume,
    private readonly RequestStack $requestStack,
    private readonly RendererInterface $renderer,
  ) {}

  /**
   * The workspace page.
   */
  public function page(Plan $ancora_plan): array {
    $state = $this->workspace->state($ancora_plan, $this->currentUser(), $this->requestStack->getCurrentRequest()->query);
    $this->resume->record($this->currentUser(), $ancora_plan, $state['period']);
    $regions = $this->workspace->regions($state);
    $build = [
      '#theme' => 'ancora_plan_workspace',
      '#bar' => $regions['bar'],
      '#nav' => $regions['nav'],
      '#canvas' => $regions['canvas'],
      '#inspector' => $regions['inspector'],
      '#status' => $regions['canvas']['#status'] ?? $regions['canvas']['#heading'],
      '#regions_url' => Url::fromRoute('ancora_scheduling.plan_workspace.regions', ['ancora_plan' => $ancora_plan->id()])->toString(),
      '#attached' => ['library' => ['ancora_scheduling/plan_workspace']],
    ];
    $this->workspace->cacheability($ancora_plan)->applyTo($build);
    return $build;
  }

  /**
   * The regions of the workspace as an HTML fragment (GET, read-only).
   */
  public function regions(Plan $ancora_plan): CacheableResponse {
    $state = $this->workspace->state($ancora_plan, $this->currentUser(), $this->requestStack->getCurrentRequest()->query);
    $build = $this->workspace->regions($state);
    $this->workspace->cacheability($ancora_plan)->applyTo($build);
    $html = (string) $this->renderer->renderInIsolation($build);
    $response = new CacheableResponse($html, 200, [
      'Content-Type' => 'text/html; charset=utf-8',
      'X-Robots-Tag' => 'noindex',
    ]);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($build));
    return $response;
  }

  /**
   * Title of the workspace page.
   */
  public function title(Plan $ancora_plan): TranslatableMarkup {
    return $this->t('Workspace: @plan', ['@plan' => $ancora_plan->label()]);
  }

}
