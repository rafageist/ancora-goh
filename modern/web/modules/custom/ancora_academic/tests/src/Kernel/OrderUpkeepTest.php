<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\ActivityType;
use Drupal\ancora_academic\Hook\OrderHooks;
use Drupal\ancora_academic\OrderUpkeep;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Classification priority and template order upkeep (D13).
 *
 * Legacy (legacy-parity-inventory.md PAR-019, PAR-020, PAR-035; cases 111,
 * 112, 144).
 */
#[CoversClass(OrderUpkeep::class)]
#[CoversClass(OrderHooks::class)]
#[Group('ancora')]
final class OrderUpkeepTest extends AcademicKernelTestBase {

  /**
   * Code => priority of the owner's activity types.
   */
  private function priorities(?int $owner = NULL): string {
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_activity_type');
    $storage->resetCache();
    $out = [];
    foreach ($storage->loadByProperties(['uid' => $owner ?? $this->owner->id()]) as $type) {
      $out[(int) $type->get('priority')->value] = $type->getCode();
    }
    ksort($out);
    return implode(' ', array_map(static fn (int $p, string $c): string => "$c=$p", array_keys($out), $out));
  }

  /**
   * A new activity type of the current user, without a priority.
   */
  private function type(string $code): ActivityType {
    $values = ['label' => "Type $code", 'code' => $code, 'duration_slots' => 1, 'cost' => 1];
    $type = $this->newRecord('ancora_activity_type', $values);
    $this->assertSame([], $this->violationFields($type));
    $type->save();
    assert($type instanceof ActivityType);
    return $type;
  }

  /**
   * Count + 1, kept on edit, moves, renumbered on delete, per owner.
   */
  public function testActivityTypePriorities(): void {
    $this->loadFixture();
    $order = $this->container->get(OrderUpkeep::class);
    $c2 = $this->type('C2');
    $c3 = $this->type('C3');
    $this->assertSame('C1=1 C2=2 C3=3', $this->priorities(), 'count + 1');
    $c2->set('label', 'Renamed')->save();
    $this->assertSame('C1=1 C2=2 C3=3', $this->priorities(), 'an edit keeps the priority');

    $this->assertFalse($order->moveActivityType($this->byCode('ancora_activity_type', 'C1'), -1), 'the first does not move up');
    $this->assertTrue($order->moveActivityType($this->byCode('ancora_activity_type', 'C1'), 1));
    $this->assertSame('C2=1 C1=2 C3=3', $this->priorities());
    $this->assertFalse($order->moveActivityType($this->byCode('ancora_activity_type', 'C3'), 1), 'the last does not move down');
    $this->assertTrue($order->moveActivityType($this->byCode('ancora_activity_type', 'C3'), -1));
    $this->assertSame('C2=1 C3=2 C1=3', $this->priorities());

    // Another planner's types: their own count, untouched by these moves.
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->setCurrentUser($other);
    $this->type('Q1');
    $this->type('Q2');
    $this->assertSame('Q1=1 Q2=2', $this->priorities((int) $other->id()));
    $this->setCurrentUser($this->owner);
    $this->assertFalse($order->moveActivityType($this->byCode('ancora_activity_type', 'C1'), 1), 'no neighbour of the creator (legacy fails, Q-34)');

    // A delete renumbers the creator's types only.
    $c3->delete();
    $this->assertSame('C2=1 C1=2', $this->priorities());
    $this->assertSame('Q1=1 Q2=2', $this->priorities((int) $other->id()));
  }

  /**
   * Append, renumber after insert and delete, moves; no gaps.
   */
  public function testTemplateOrder(): void {
    $plan = $this->loadFixture();
    $order = $this->container->get(OrderUpkeep::class);
    $etm = $this->container->get('entity_type.manager');
    $subject = $this->byCode('ancora_subject', 'A');
    $period = $this->byCode('ancora_period', 'PER', $plan);
    $type = $this->byCode('ancora_activity_type', 'C1');
    $new = function (?int $sequence = NULL) use ($plan, $subject, $period, $type): ActivityTemplate {
      $values = [
        'plan' => $plan->id(),
        'subject' => $subject->id(),
        'period' => $period->id(),
        'activity_type' => $type->id(),
      ];
      $template = $this->newRecord('ancora_activity_template', $values + ($sequence === NULL ? [] : ['sequence' => $sequence]));
      $this->assertSame([], $this->violationFields($template));
      $template->save();
      assert($template instanceof ActivityTemplate);
      return $template;
    };
    $orders = function () use ($etm, $subject, $period): array {
      $storage = $etm->getStorage('ancora_activity_template');
      $storage->resetCache();
      $out = [];
      foreach ($storage->loadByProperties(['subject' => $subject->id(), 'period' => $period->id()]) as $id => $t) {
        $out[(int) $t->get('sequence')->value] = (int) $id;
      }
      ksort($out);
      return $out;
    };
    [$a1, $a2] = array_values($orders());
    $a3 = (int) $new()->id();
    $this->assertSame([1 => $a1, 2 => $a2, 3 => $a3], $orders(), 'appended: count + 1');
    $a4 = (int) $new(7)->id();
    $this->assertSame([1 => $a1, 2 => $a2, 3 => $a3, 4 => $a4], $orders(), 'a free order 7 is renumbered to 4');

    $load = fn (int $id): ActivityTemplate => $etm->getStorage('ancora_activity_template')->load($id);
    $this->assertFalse($order->moveTemplate($load($a1), -1));
    $this->assertTrue($order->moveTemplate($load($a3), -1));
    $this->assertSame([1 => $a1, 2 => $a3, 3 => $a2, 4 => $a4], $orders());
    $this->assertTrue($order->moveTemplate($load($a1), 1));
    $this->assertSame([1 => $a3, 2 => $a1, 3 => $a2, 4 => $a4], $orders());
    $this->assertFalse($order->moveTemplate($load($a4), 1));

    // Deleting the second (its assignments first, the academic guard)
    // closes the gap.
    foreach (['ancora_teacher_assignment', 'ancora_room_assignment'] as $assignment) {
      $storage = $etm->getStorage($assignment);
      $storage->delete($storage->loadByProperties(['template' => $a1]));
    }
    $load($a1)->delete();
    $this->assertSame([1 => $a3, 2 => $a2, 3 => $a4], $orders());
  }

  /**
   * The update renumbers the gaps and repeats of earlier free numbers.
   */
  public function testUpdateRenumbers(): void {
    $this->loadFixture();
    $c2 = $this->type('C2');
    $database = $this->container->get('database');
    $database->update('ancora_activity_type')->fields(['priority' => 5])->condition('id', $c2->id())->execute();
    $database->update('ancora_activity_template')->fields(['sequence' => 9])->condition('sequence', 2)->execute();
    $this->container->get('entity_type.manager')->getStorage('ancora_activity_type')->resetCache();
    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $this->assertSame('1 activity type priorities and 2 template orders were renumbered without gaps.', ancora_academic_update_11009());
    $this->assertSame('C1=1 C2=2', $this->priorities());
    $this->assertSame('0 activity type priorities and 0 template orders were renumbered without gaps.', ancora_academic_update_11009());
  }

}
