<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Workspace;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Vary: X-Ancora-Regions on the workspace routes (#141 S1b).
 *
 * The workspace page answers with the full page, or with its regions only
 * when the workspace script sends "X-Ancora-Regions: 1" (a move step in
 * place). Both are uncached (the route is no_cache and records the Resume
 * preference), and every response of the page and of the regions endpoint
 * also says it varies by that header, so no cache between the browser and
 * Drupal can mix the two. The header is appended: an existing Vary (for
 * example Cookie) is kept.
 */
final class WorkspaceVarySubscriber implements EventSubscriberInterface {

  /**
   * The request header that selects the regions fragment.
   */
  public const HEADER = 'X-Ancora-Regions';

  /**
   * The routes whose responses vary by the header.
   */
  private const ROUTES = ['ancora_scheduling.plan_workspace', 'ancora_scheduling.plan_workspace.regions'];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // After FinishResponseSubscriber (priority 0), which may set Vary too.
    return [KernelEvents::RESPONSE => ['onResponse', -10]];
  }

  /**
   * Appends the header to the response's Vary.
   */
  public function onResponse(ResponseEvent $event): void {
    if (in_array($event->getRequest()->attributes->get('_route'), self::ROUTES, TRUE)) {
      self::addVary($event->getResponse());
    }
  }

  /**
   * Appends X-Ancora-Regions to a response's Vary, keeping the others.
   */
  public static function addVary(Response $response): void {
    if (!in_array(strtolower(self::HEADER), array_map('strtolower', $response->getVary()), TRUE)) {
      $response->setVary(self::HEADER, FALSE);
    }
  }

}
