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
 * The integrated planning workspace of a plan (#141, S1a).
 *
 * Read-only for academic and scheduling data: nothing here writes a plan,
 * a placement or any other academic record. Both GET routes write ONE
 * per-user preference, deliberately: the plan and period the account is
 * looking at, for the Overview's "Resume" (ResumeStore, user.data). It is
 * recorded only after the URL state was validated (a foreign or forged value
 * is a 404 and records nothing), and access is checked again whenever it is
 * read, so a forged link can at most point an account's Resume at a plan it
 * may already view.
 *
 * The page: the whole workspace, one full page. The regions endpoint: the
 * same regions as an HTML fragment for the workspace script, with the same
 * access and the same query validation. Neither response is cached (they
 * record the preference); both carry the metadata of their content.
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
  public function page(Plan $ancora_plan): array|CacheableResponse {
    $request = $this->requestStack->getCurrentRequest();
    // A POST is a step of the move form in the inspector (S1b): the form
    // processes it while the inspector is built, and redirects back here.
    $state = $this->workspace->state($ancora_plan, $this->currentUser(), $request->query, $request->isMethod('POST') ? $request->request : NULL);
    $this->resume->record($this->currentUser(), $ancora_plan, $state['period']);
    $regions = $this->workspace->regions($state);
    // The workspace script submits the move steps in place and asks for the
    // regions only (the header is kept across the step's redirect); the
    // route is not cached, so the page and the fragment never mix.
    if ($request->headers->get('X-Ancora-Regions') === '1') {
      return $this->fragment($ancora_plan, $regions);
    }
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
   * The regions of the workspace as an HTML fragment (GET).
   *
   * It records the Resume preference like the page, so that an in-place
   * change of period (or Back and Forward) is what Resume offers next.
   */
  public function regions(Plan $ancora_plan): CacheableResponse {
    $state = $this->workspace->state($ancora_plan, $this->currentUser(), $this->requestStack->getCurrentRequest()->query);
    $this->resume->record($this->currentUser(), $ancora_plan, $state['period']);
    return $this->fragment($ancora_plan, $this->workspace->regions($state));
  }

  /**
   * Regions as an HTML fragment, never cached.
   */
  private function fragment(Plan $plan, array $build): CacheableResponse {
    $this->workspace->cacheability($plan)->applyTo($build);
    $html = (string) $this->renderer->renderInIsolation($build);
    $response = new CacheableResponse($html, 200, [
      'Content-Type' => 'text/html; charset=utf-8',
      'X-Robots-Tag' => 'noindex',
    ]);
    // Never served from a cache: every request records the preference.
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($build)->setCacheMaxAge(0));
    return $response;
  }

  /**
   * Title of the workspace page.
   */
  public function title(Plan $ancora_plan): TranslatableMarkup {
    return $this->t('Workspace: @plan', ['@plan' => $ancora_plan->label()]);
  }

}
