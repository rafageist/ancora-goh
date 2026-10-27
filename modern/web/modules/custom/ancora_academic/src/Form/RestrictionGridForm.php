<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\TimeRestriction;
use Drupal\ancora_academic\TimeRestrictionGrid;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The day x slot restriction grid of one object in one period (D9a).
 *
 * The legacy period grid (form_periodo_restricciones.tpl): one toggle per
 * day and slot; saving restricts the checked cells and frees the others
 * (custom_set_Restricciones_Periodo.php). Here the same grid also serves
 * activity types, subjects, cohorts, teachers and rooms, whose legacy
 * tables had no editor (A-4; proposal P-2, approved with D9). The choice of
 * period, kind and object is in the query:
 * ?period=<id>&kind=<kind>&target=<id>.
 */
final class RestrictionGridForm extends FormBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly TimeRestrictionGrid $grid,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('entity_type.manager'), $container->get(TimeRestrictionGrid::class));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_academic_restriction_grid';
  }

  /**
   * The labels of the kinds.
   *
   * @return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup>
   *   Kind => label.
   */
  public static function kinds(): array {
    return [
      'period' => new TranslatableMarkup('The period itself'),
      'activity_type' => new TranslatableMarkup('Activity type'),
      'subject' => new TranslatableMarkup('Subject'),
      'cohort' => new TranslatableMarkup('Cohort'),
      'teacher' => new TranslatableMarkup('Teacher'),
      'room' => new TranslatableMarkup('Room'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $query = $this->getRequest()->query;
    $kind = (string) $query->get('kind', 'period');
    $kind = array_key_exists($kind, TimeRestriction::KINDS) ? $kind : 'period';
    $period = $this->editablePeriod((string) $query->get('period', ''));
    $target = $kind === 'period' ? NULL : $this->editableTarget($kind, (string) $query->get('target', ''));

    $form['#cache']['contexts'] = ['url.query_args', 'user'];
    $form['intro'] = [
      '#markup' => '<p>' . $this->t('A restricted cell is never used for the activities of the restricted object: the period itself, an activity type, a subject, a cohort, a teacher or a room. Choose the period and the object, then check the restricted cells.') . '</p>',
    ];
    $form['choose'] = [
      '#type' => 'details',
      '#title' => $this->t('Period and object'),
      '#open' => $period === NULL || ($kind !== 'period' && $target === NULL),
    ];
    $form['choose']['period'] = [
      '#type' => 'select',
      '#title' => $this->t('Period'),
      '#options' => $this->periodOptions(),
      '#empty_option' => $this->t('- Choose a period -'),
      '#default_value' => $period?->id(),
    ];
    $form['choose']['kind'] = [
      '#type' => 'select',
      '#title' => $this->t('Restricted object'),
      '#options' => self::kinds(),
      '#default_value' => $kind,
    ];
    if ($kind !== 'period') {
      $form['choose']['target'] = [
        '#type' => 'select',
        '#title' => self::kinds()[$kind],
        '#options' => $this->targetOptions($kind),
        '#empty_option' => $this->t('- Choose -'),
        '#default_value' => $target?->id(),
        '#description' => $this->t('After changing the kind, press "Show" to list its records.'),
      ];
    }
    $form['choose']['show'] = [
      '#type' => 'submit',
      '#value' => $this->t('Show'),
      '#submit' => ['::show'],
      '#limit_validation_errors' => [['period'], ['kind'], ['target']],
    ];

    if ($period === NULL || ($kind !== 'period' && $target === NULL)) {
      return $form;
    }

    $existing = $this->grid->load($kind, $target === NULL ? NULL : (int) $target->id(), (int) $period->id());
    $days = $period->getDays();
    $slots = $period->getSlotsPerDay();
    $header = [$this->t('Slot'), $this->t('Hours')];
    for ($day = 1; $day <= $days; $day++) {
      $header[] = $this->t('Day @day', ['@day' => $day]);
    }
    $form['grid'] = [
      '#type' => 'table',
      '#caption' => $this->t('Restricted cells of %object in %period', [
        '%object' => $target === NULL ? $this->t('the period') : $target->label(),
        '%period' => $period->label(),
      ]),
      '#header' => $header,
      '#attributes' => ['class' => ['ancora-restriction-grid']],
    ];
    $start = $period->getStartHour();
    $length = $period->getSlotDuration();
    for ($slot = 1; $slot <= $slots; $slot++) {
      $form['grid'][$slot]['slot'] = ['#markup' => (string) $slot];
      $form['grid'][$slot]['hours'] = ['#markup' => self::hour($start + ($slot - 1) * $length) . '–' . self::hour($start + $slot * $length)];
      for ($day = 1; $day <= $days; $day++) {
        $form['grid'][$slot]["d$day"] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Day @day, slot @slot restricted', ['@day' => $day, '@slot' => $slot]),
          '#title_display' => 'invisible',
          '#default_value' => isset($existing["$day:$slot"]),
        ];
      }
    }
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save restrictions'),
      '#button_type' => 'primary',
    ];
    $form_state->set('selection', [$kind, $target?->id(), $period->id()]);
    return $form;
  }

  /**
   * Submit handler of "Show": the choice goes into the query.
   */
  public function show(array &$form, FormStateInterface $form_state): void {
    $query = array_filter([
      'period' => $form_state->getValue('period'),
      'kind' => $form_state->getValue('kind'),
      'target' => $form_state->getValue('target'),
    ], static fn ($v): bool => $v !== NULL && $v !== '');
    $form_state->setRedirectUrl(Url::fromRoute('ancora_academic.restrictions', [], ['query' => $query]));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    [$kind, $target_id, $period_id] = $form_state->get('selection') ?? [NULL, NULL, NULL];
    $period = $period_id === NULL ? NULL : $this->entityTypeManager->getStorage('ancora_period')->load($period_id);
    if (!$period instanceof Period) {
      return;
    }
    $cells = [];
    foreach ((array) $form_state->getValue('grid') as $slot => $row) {
      foreach ((array) $row as $key => $checked) {
        if ($checked && preg_match('/^d(\d+)$/', (string) $key, $m)) {
          $cells[] = [(int) $m[1], (int) $slot];
        }
      }
    }
    $messages = $this->grid->save($kind, $target_id === NULL ? NULL : (int) $target_id, $period, $cells);
    if ($messages !== []) {
      foreach ($messages as $message) {
        $this->messenger()->addError($message);
      }
      $this->messenger()->addError($this->t('Nothing was saved.'));
      $form_state->setRebuild();
      return;
    }
    $this->messenger()->addStatus($this->t('Saved the restrictions: @count restricted cells.', ['@count' => count($cells)]));
  }

  /**
   * A period the user may edit, or NULL.
   */
  private function editablePeriod(string $id): ?Period {
    $period = ctype_digit($id) ? $this->entityTypeManager->getStorage('ancora_period')->load($id) : NULL;
    return $period instanceof Period && $period->access('update') ? $period : NULL;
  }

  /**
   * A restricted object the user may edit, or NULL.
   */
  private function editableTarget(string $kind, string $id): ?AcademicEntityInterface {
    $entity = ctype_digit($id) ? $this->entityTypeManager->getStorage(TimeRestriction::TARGET_TYPES[$kind])->load($id) : NULL;
    return $entity instanceof AcademicEntityInterface && $entity->access('update') ? $entity : NULL;
  }

  /**
   * The user's periods, labelled with their plan.
   *
   * @return array<int, string>
   *   Period id => label.
   */
  private function periodOptions(): array {
    $storage = $this->entityTypeManager->getStorage('ancora_period');
    $options = [];
    foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(TRUE)->sort('plan')->sort('code')->execute()) as $id => $period) {
      assert($period instanceof Period);
      $plan = $period->get('plan')->entity;
      $options[$id] = ($plan ? $plan->label() . ': ' : '') . $period->label();
    }
    return $options;
  }

  /**
   * The user's records of a kind.
   *
   * @return array<int, string>
   *   Id => label.
   */
  private function targetOptions(string $kind): array {
    $storage = $this->entityTypeManager->getStorage(TimeRestriction::TARGET_TYPES[$kind]);
    $options = [];
    foreach ($storage->loadMultiple($storage->getQuery()->accessCheck(TRUE)->sort('code')->execute()) as $id => $entity) {
      $options[$id] = (string) $entity->label();
    }
    return $options;
  }

  /**
   * An hour as HH:MM.
   */
  private static function hour(float $hour): string {
    return sprintf('%02d:%02d', (int) floor($hour), (int) round(fmod($hour, 1) * 60));
  }

}
