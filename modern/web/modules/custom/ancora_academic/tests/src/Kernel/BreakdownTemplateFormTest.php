<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\Breakdown;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\Form\AcademicEntityForm;
use Drupal\Core\Form\FormState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * A template added from its breakdown equals one added with the plain form.
 *
 * The legacy "+" of the breakdown browser (desglose_detalles.tpl:36,
 * get_form_new_plantilla.php:13-29) takes the subject and period from the
 * breakdown and appends the template (order = count + 1). The add form with
 * ?breakdown=<ID> (FC-17, Phase 3 slice B) only prefills and locks the
 * plan, subject and period: the record, its order and the legacy rows that
 * reach the engine are those of the plain form with the same values.
 */
#[CoversClass(AcademicEntityForm::class)]
#[Group('ancora')]
final class BreakdownTemplateFormTest extends AcademicKernelTestBase {

  /**
   * Submits the template add form with a query string; returns the template.
   *
   * @param array<string, string> $query
   *   The query arguments of the request.
   * @param array<string, mixed> $values
   *   The submitted values.
   */
  private function submit(array $query, array $values): ActivityTemplate {
    $request_stack = $this->container->get('request_stack');
    $request = Request::create('/admin/ancora/templates/add' . ($query === [] ? '' : '?' . http_build_query($query)), 'POST');
    $request->setSession($request_stack->getCurrentRequest()->getSession());
    $request_stack->push($request);
    try {
      $entity_type_manager = $this->container->get('entity_type.manager');
      $form_object = $entity_type_manager->getFormObject('ancora_activity_template', 'add');
      $form_object->setEntity($entity_type_manager->getStorage('ancora_activity_template')->create());
      $form_state = (new FormState())->setValues($values + ['op' => 'Save']);
      $this->container->get('form_builder')->submitForm($form_object, $form_state);
      $this->assertSame([], array_map('strval', $form_state->getErrors()));
    }
    finally {
      $request_stack->pop();
    }
    $template = $form_object->getEntity();
    $this->assertInstanceOf(ActivityTemplate::class, $template);
    $this->assertFalse($template->isNew());
    return $template;
  }

  /**
   * The stored values of a template that matter to the engine.
   *
   * @return array<string, mixed>
   *   Field => value.
   */
  private function snapshot(ActivityTemplate $template): array {
    $template = $this->container->get('entity_type.manager')->getStorage('ancora_activity_template')->loadUnchanged($template->id());
    assert($template instanceof ActivityTemplate);
    return [
      'plan' => (int) $template->get('plan')->target_id,
      'subject' => (int) $template->get('subject')->target_id,
      'period' => (int) $template->get('period')->target_id,
      'sequence' => $template->getSequence(),
      'activity_type' => (int) $template->get('activity_type')->target_id,
      'requires_teacher' => $template->requiresTeacher(),
      'requires_room' => $template->requiresRoom(),
    ];
  }

  /**
   * The legacy rows of the plan, as the engine receives them.
   *
   * @return array<string, mixed>
   *   Table => rows.
   */
  private function legacyRows(Plan $plan, Period $period): array {
    $export = $this->container->get(AcademicExport::class)->export($plan, $period);
    return $this->container->get(LegacyRows::class)->build($export);
  }

  /**
   * Same record, same order, same breakdown, same engine rows.
   */
  public function testSameAsPlainForm(): void {
    $plan = $this->loadFixture();
    $period = $this->byCode('ancora_period', 'PER', $plan);
    $this->assertInstanceOf(Period::class, $period);
    $alpha = $this->byCode('ancora_subject', 'A');
    $beta = $this->byCode('ancora_subject', 'B');
    $type = $this->byCode('ancora_activity_type', 'C1');
    $breakdowns = $this->container->get('entity_type.manager')->getStorage('ancora_breakdown')
      ->loadByProperties(['subject' => $alpha->id(), 'period' => $period->id()]);
    $this->assertCount(1, $breakdowns);
    $breakdown = reset($breakdowns);
    $this->assertInstanceOf(Breakdown::class, $breakdown);
    $breakdown_count = $this->recordCount('ancora_breakdown');
    $common = [
      'activity_type' => $type->id(),
      'requires_teacher' => ['value' => 1],
      'requires_room' => ['value' => NULL],
    ];

    // From the breakdown: only the activity type and the two options are
    // chosen. Values posted for the locked fields (another subject) are
    // ignored, as for any disabled form element.
    $from_breakdown = $this->submit(['breakdown' => (string) $breakdown->id()], $common + ['subject' => $beta->id()]);
    $expected = [
      'plan' => (int) $plan->id(),
      'subject' => (int) $alpha->id(),
      'period' => (int) $period->id(),
      'sequence' => 3,
      'activity_type' => (int) $type->id(),
      'requires_teacher' => TRUE,
      'requires_room' => FALSE,
    ];
    $this->assertSame($expected, $this->snapshot($from_breakdown));
    $rows = $this->legacyRows($plan, $period);
    // The breakdown relationship: the template is the third of the
    // breakdown's desglose row, and no breakdown was created or changed.
    $desglose = $rows['plantilla_de_actividad'][0]['desglose'];
    $this->assertSame([1, 2, 3], array_column(array_filter($rows['plantilla_de_actividad'], static fn (array $r): bool => $r['desglose'] === $desglose), 'orden'));
    $this->assertCount(5, $rows['plantilla_de_actividad']);
    $this->assertSame($breakdown_count, $this->recordCount('ancora_breakdown'));
    $this->assertSame($breakdown->legacyOptions(), $this->container->get('entity_type.manager')->getStorage('ancora_breakdown')->loadUnchanged($breakdown->id())->legacyOptions());
    $from_breakdown->delete();

    // The plain form with the same values.
    $plain = $this->submit([], $common + [
      'plan' => $plan->id(),
      'subject' => $alpha->id(),
      'period' => $period->id(),
    ]);
    $this->assertSame($expected, $this->snapshot($plain));
    $this->assertSame($rows, $this->legacyRows($plan, $period));
  }

}
