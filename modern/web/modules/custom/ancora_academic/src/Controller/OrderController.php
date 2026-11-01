<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Controller;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\ActivityType;
use Drupal\ancora_academic\OrderUpkeep;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Moves a classification or a template up or down (D13).
 *
 * The legacy arrows: set_ClasifAct_subeprioridad / _bajaprioridad and
 * custom_SubePlantilla / custom_BajaPlantilla (OrderUpkeep). A link with a
 * CSRF token, as the legacy arrows were one click.
 */
final class OrderController extends ControllerBase {

  /**
   * Path segment => entity type.
   */
  public const TYPES = [
    'activity-type' => 'ancora_activity_type',
    'template' => 'ancora_activity_template',
  ];

  public function __construct(
    private readonly OrderUpkeep $order,
    private readonly RequestStack $requestStack,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get(OrderUpkeep::class), $container->get('request_stack'));
  }

  /**
   * Moves the record and returns to the list (or ?destination=).
   */
  public function move(string $type, string $id, string $direction): RedirectResponse {
    $entity_type_id = self::TYPES[$type] ?? NULL;
    $entity = $entity_type_id === NULL ? NULL : $this->entityTypeManager()->getStorage($entity_type_id)->load((int) $id);
    if ($entity === NULL) {
      throw new NotFoundHttpException();
    }
    if (!$entity->access('update')) {
      throw new AccessDeniedHttpException();
    }
    $step = $direction === 'up' ? -1 : 1;
    $moved = match (TRUE) {
      $entity instanceof ActivityType => $this->order->moveActivityType($entity, $step),
      $entity instanceof ActivityTemplate => $this->order->moveTemplate($entity, $step),
      default => FALSE,
    };
    $arguments = ['%label' => $entity->label(), '@position' => $step < 0 ? $this->t('first') : $this->t('last')];
    $this->messenger()->addStatus($moved
      ? $this->t('Moved %label.', $arguments)
      : $this->t('%label is already the @position.', $arguments));
    $url = $entity->toUrl('collection');
    if ($entity instanceof ActivityTemplate) {
      $url->setOption('query', [
        'subject' => $entity->get('subject')->target_id,
        'period' => $entity->get('period')->target_id,
      ]);
    }
    $destination = $this->getDestination();
    return new RedirectResponse($destination ?? $url->setAbsolute()->toString());
  }

  /**
   * A local destination from the query string, or NULL.
   */
  private function getDestination(): ?string {
    $destination = $this->requestStack->getCurrentRequest()?->query->get('destination');
    if (!is_string($destination) || $destination === '' || !str_starts_with($destination, '/') || str_starts_with($destination, '//')) {
      return NULL;
    }
    return Url::fromUserInput($destination)->setAbsolute()->toString();
  }

}
