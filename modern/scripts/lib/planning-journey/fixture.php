<?php

/**
 * @file
 * The planning journey's synthetic fixture and its server-side reads.
 *
 * Usage (scripts/planning-journey.sh, on a DISPOSABLE site only):
 *   drush php:script scripts/lib/planning-journey/fixture.php -- <command> ...
 *
 * Every record is synthetic, belongs to the account "planner", is created
 * as that account and is validated before it is saved (the constraints of
 * the forms apply). The browser journey (dev/browser/tests/
 * planning-journey.spec.js) enters part of the dataset through the UI; this
 * script enters the rest and refuses to run when a record that the UI was
 * meant to create is missing, so the split stays visible. Records are found
 * by code (catalogue, groups) or by subject and period (templates), so a
 * phase never duplicates what the UI entered.
 *
 * The dataset (plan J, period JW: 5 days x 6 one-hour slots from 08:00,
 * plan day 1, a 0.5 h break after slot 3; the UI creates the plan, the
 * period and the break):
 * - activity types LEC (1 slot) and LAB (2 slots; UI);
 * - specialties CS and MA, levels Y1 and Y2; cohorts C1A, C1B (CS, Y1) and
 *   M2A, M2B (MA, Y2);
 * - groups per plan: GCL (CS lecture, C1A + C1B; UI), G1A, G1B (CS labs),
 *   GML (MA lecture, M2A + M2B), G2A, G2B (MA labs);
 * - subjects PRG and DSC (CS, Y1), ALG, CAL and STA (MA, Y2); PRG's
 *   templates (LEC, LEC, LAB) and their assignments come from the UI;
 * - teachers ANA, BEN, CAR, DIA and EVA (UI); rooms AUD, R101, LABA and
 *   LABB (UI); other resource PRJ (UI), needed by PRG's first lecture (UI)
 *   and STA's first lab;
 * - a teacher restriction (DIA, UI), a period and a room restriction, a
 *   teacher daily load limit (BEN, UI) and a cohort one (M2A).
 * Scopes: (JW, CS, Y1), 8 activities, and (JW, MA, Y2), 14 activities.
 *
 * Commands:
 * - catalogue: the owner's catalogue;
 * - structure: plan J's groups and templates;
 * - assignments: teachers, rooms, other resources, restrictions and limits;
 * - second-plan: plan K, dated over the same week, with its own specialty
 *   INT, cohort I1A and subject WSH, whose only activity is taught by ANA
 *   in room R101 (cross-plan warnings);
 * - inspect <plan code>: JSON of the plan's runs, placements, derived
 *   occupancy collisions, day overflows, break crossings, restriction and
 *   load-limit observations, and manual changes.
 */

declare(strict_types=1);

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\TemplateAssignments;

// phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UndefinedVariable
[$command, $arg] = $extra + [NULL, NULL];
$etm = \Drupal::entityTypeManager();
$planner = current($etm->getStorage('user')->loadByProperties(['name' => 'planner']));
if (!$planner) {
  throw new \RuntimeException('No account "planner".');
}
$uid = (int) $planner->id();
\Drupal::service('account_switcher')->switchTo($planner);

/**
 * Loads the single record of a type with the given properties, or NULL.
 */
$find = static function (string $type, array $properties) use ($etm, $uid) {
  if (AcademicEntityTypes::isOwned($type)) {
    $properties['uid'] = $uid;
  }
  $found = $etm->getStorage($type)->loadByProperties($properties);
  if (count($found) > 1) {
    throw new \RuntimeException("more than one $type for " . json_encode($properties));
  }
  return $found ? current($found) : NULL;
};
/**
 * The record that the UI step was meant to create; refuses when missing.
 */
$ui = static function (string $type, array $properties) use ($find) {
  $record = $find($type, $properties);
  if ($record === NULL) {
    throw new \RuntimeException("The UI step did not create $type " . json_encode($properties) . '.');
  }
  return $record;
};
/**
 * Creates, validates and saves a record.
 */
$create = static function (string $type, array $values) use ($etm, $uid) {
  if (AcademicEntityTypes::isOwned($type)) {
    $values['uid'] = $uid;
  }
  $record = $etm->getStorage($type)->create($values);
  $violations = $record->validate();
  if ($violations->count() > 0) {
    $messages = [];
    foreach ($violations as $violation) {
      $messages[] = $violation->getPropertyPath() . ': ' . strip_tags((string) $violation->getMessage());
    }
    throw new \RuntimeException("Invalid $type: " . implode('; ', $messages));
  }
  $record->save();
  return $record;
};
/**
 * Finds a record by its properties or creates it with extra values.
 */
$ensure = static function (string $type, array $key, array $values = []) use ($find, $create) {
  return $find($type, $key) ?? $create($type, $key + $values);
};
$plan_of = static fn (string $code) => $ui('ancora_plan', ['code' => $code]);
$id = static fn ($record): int => (int) $record->id();
/**
 * The templates of a subject in a period, in order.
 */
$templates_of = static function ($subject, $period) use ($etm): array {
  $found = $etm->getStorage('ancora_activity_template')->loadByProperties([
    'subject' => $subject->id(),
    'period' => $period->id(),
  ]);
  uasort($found, static fn ($a, $b): int => $a->getSequence() <=> $b->getSequence());
  return array_values($found);
};
$assignments = \Drupal::service(TemplateAssignments::class);

// The non-UI templates: subject => activity type codes, in order.
$plan_templates = [
  'DSC' => ['LEC', 'LEC', 'LAB'],
  'ALG' => ['LEC', 'LEC', 'LEC', 'LAB'],
  'CAL' => ['LEC', 'LEC', 'LEC'],
  'STA' => ['LEC', 'LEC', 'LAB', 'LAB'],
];
// Subject => activity type => [teacher => priority].
$teaching = [
  'DSC' => ['LEC' => ['CAR' => 1], 'LAB' => ['CAR' => 1, 'ANA' => 2]],
  'ALG' => ['LEC' => ['DIA' => 1, 'EVA' => 2], 'LAB' => ['EVA' => 1]],
  'CAL' => ['LEC' => ['DIA' => 1, 'ANA' => 2]],
  'STA' => ['LEC' => ['EVA' => 1, 'BEN' => 2], 'LAB' => ['BEN' => 1, 'EVA' => 2]],
];
$rooms_for = ['LEC' => ['AUD' => 1, 'R101' => 2], 'LAB' => ['LABA' => 1, 'LABB' => 2]];

$out = NULL;
switch ($command) {
  case 'catalogue':
    $ui('ancora_activity_type', ['code' => 'LAB']);
    $ui('ancora_teacher', ['code' => 'EVA']);
    $ui('ancora_room', ['code' => 'LABB']);
    $ui('ancora_other_resource', ['code' => 'PRJ']);
    $ensure('ancora_activity_type', ['code' => 'LEC'], [
      'label' => 'Lecture',
      'duration_slots' => 1,
      'priority' => 1,
      'cost' => 1,
    ]);
    $cs = $ensure('ancora_specialty', ['code' => 'CS'], ['label' => 'Computing']);
    $ma = $ensure('ancora_specialty', ['code' => 'MA'], ['label' => 'Mathematics']);
    $y1 = $ensure('ancora_level', ['code' => 'Y1'], ['label' => 'Year 1']);
    $y2 = $ensure('ancora_level', ['code' => 'Y2'], ['label' => 'Year 2']);
    $csy1 = ['specialty' => $id($cs), 'level' => $id($y1)];
    $may2 = ['specialty' => $id($ma), 'level' => $id($y2)];
    $cohorts = [
      'C1A' => [$csy1, 25],
      'C1B' => [$csy1, 25],
      'M2A' => [$may2, 20],
      'M2B' => [$may2, 20],
    ];
    foreach ($cohorts as $code => [$of, $n]) {
      $ensure('ancora_cohort', ['code' => $code], $of + ['label' => "Cohort $code", 'enrollment' => $n]);
    }
    foreach ([
      'PRG' => ['Programming', $csy1],
      'DSC' => ['Discrete structures', $csy1],
      'ALG' => ['Linear algebra', $may2],
      'CAL' => ['Calculus II', $may2],
      'STA' => ['Statistics', $may2],
    ] as $code => [$label, $of]) {
      $ensure('ancora_subject', ['code' => $code], $of + ['label' => $label]);
    }
    foreach (['ANA' => 'Ana Example', 'BEN' => 'Ben Example', 'CAR' => 'Carla Example', 'DIA' => 'Diego Example'] as $code => $label) {
      $ensure('ancora_teacher', ['code' => $code], ['label' => $label]);
    }
    $rooms = [
      'AUD' => ['Auditorium', 60],
      'R101' => ['Room 101', 60],
      'LABA' => ['Lab A', 30],
    ];
    foreach ($rooms as $code => [$label, $capacity]) {
      $ensure('ancora_room', ['code' => $code], ['label' => $label, 'capacity' => $capacity]);
    }
    break;

  case 'structure':
    $plan = $plan_of('J');
    $period = $ui('ancora_period', ['code' => 'JW', 'plan' => $id($plan)]);
    $in = ['plan' => $id($plan)];
    $ui('ancora_student_group', ['code' => 'GCL', 'plan' => $id($plan)]);
    $type = static fn (string $code): int => $id($find('ancora_activity_type', ['code' => $code]));
    $cohort = static fn (string $code): int => $id($find('ancora_cohort', ['code' => $code]));
    $of = static fn (string $s, string $l): array => [
      'specialty' => $id($find('ancora_specialty', ['code' => $s])),
      'level' => $id($find('ancora_level', ['code' => $l])),
    ];
    foreach ([
      'G1A' => ['Lab group C1A', ['CS', 'Y1'], 'LAB', ['C1A']],
      'G1B' => ['Lab group C1B', ['CS', 'Y1'], 'LAB', ['C1B']],
      'GML' => ['MA lecture group', ['MA', 'Y2'], 'LEC', ['M2A', 'M2B']],
      'G2A' => ['Lab group M2A', ['MA', 'Y2'], 'LAB', ['M2A']],
      'G2B' => ['Lab group M2B', ['MA', 'Y2'], 'LAB', ['M2B']],
    ] as $code => [$label, $sl, $t, $cohorts]) {
      $ensure('ancora_student_group', ['code' => $code, 'plan' => $id($plan)], $of(...$sl) + [
        'label' => $label,
        'activity_type' => $type($t),
        'cohorts' => array_map($cohort, $cohorts),
      ]);
    }
    if (count($templates_of($find('ancora_subject', ['code' => 'PRG']), $period)) < 3) {
      throw new \RuntimeException('The UI step did not create the three templates of PRG.');
    }
    foreach ($plan_templates as $subject_code => $types) {
      $subject = $find('ancora_subject', ['code' => $subject_code]);
      if ($templates_of($subject, $period) !== []) {
        continue;
      }
      foreach ($types as $t) {
        $create('ancora_activity_template', $in + [
          'subject' => $id($subject),
          'period' => $id($period),
          'activity_type' => $type($t),
        ]);
      }
    }
    break;

  case 'assignments':
    $plan = $plan_of('J');
    $period = $ui('ancora_period', ['code' => 'JW', 'plan' => $id($plan)]);
    $in = ['plan' => $id($plan)];
    $teacher = static fn (string $code) => $find('ancora_teacher', ['code' => $code]);
    $room = static fn (string $code) => $find('ancora_room', ['code' => $code]);
    // The UI's part: PRG's assignments, DIA's restriction, BEN's limit.
    foreach ($templates_of($find('ancora_subject', ['code' => 'PRG']), $period) as $template) {
      if ($assignments->assignments($template, 'teacher') === [] || $assignments->assignments($template, 'room') === []) {
        throw new \RuntimeException('The UI step did not assign teachers and rooms to ' . $template->label() . '.');
      }
    }
    $dia = [
      'kind' => 'teacher',
      'teacher' => $id($teacher('DIA')),
      'period' => $id($period),
    ];
    if (!$etm->getStorage('ancora_time_restriction')->loadByProperties($dia)) {
      throw new \RuntimeException('The UI step did not restrict teacher DIA.');
    }
    $ui('ancora_load_limit', ['kind' => 'teacher', 'teacher' => $id($teacher('BEN')), 'period' => $id($period)]);
    foreach ($teaching as $subject_code => $by_type) {
      foreach ($templates_of($find('ancora_subject', ['code' => $subject_code]), $period) as $template) {
        $t = $template->getActivityType()->get('code')->value;
        $groups = array_keys($assignments->groups($template));
        foreach ($by_type[$t] as $code => $priority) {
          $ensure('ancora_teacher_assignment', ['template' => $id($template), 'teacher' => $id($teacher($code))], $in + [
            'priority' => $priority,
            'groups' => $groups,
          ]);
        }
        foreach ($rooms_for[$t] as $code => $priority) {
          $ensure('ancora_room_assignment', ['template' => $id($template), 'room' => $id($room($code))], $in + ['priority' => $priority]);
        }
      }
    }
    // STA's first lab needs the projector too (the UI gave it to PRG's
    // first lecture).
    $sta_lab = current(array_filter($templates_of($find('ancora_subject', ['code' => 'STA']), $period), static fn ($t): bool => $t->getActivityType()->get('code')->value === 'LAB'));
    if (!$find('ancora_resource_group', ['template' => $id($sta_lab)])) {
      $group = $create('ancora_resource_group', $in + [
        'template' => $id($sta_lab),
        'priority' => 1,
        'needed' => 1,
        'essential' => TRUE,
      ]);
      $create('ancora_resource_member', $in + [
        'resource_group' => $id($group),
        'other_resource' => $id($find('ancora_other_resource', ['code' => 'PRJ'])),
        'priority' => 1,
      ]);
    }
    $ensure('ancora_time_restriction', ['kind' => 'period', 'period' => $id($period), 'day' => 5, 'slot' => 6], $in);
    $ensure('ancora_time_restriction', [
      'kind' => 'room',
      'room' => $id($room('AUD')),
      'period' => $id($period),
      'day' => 3,
      'slot' => 6,
    ], $in);
    $ensure('ancora_load_limit', [
      'kind' => 'cohort',
      'cohort' => $id($find('ancora_cohort', ['code' => 'M2A'])),
      'period' => $id($period),
    ], $in + ['max_daily_load' => 4]);
    break;

  case 'second-plan':
    // Its own specialty and subject: a scope subject without a template in
    // the period fails the whole run (2202E, engine-dependency-closure.md
    // quirk 2, case 127 F2), and plan K's period gets a breakdown for every
    // subject of the owner. The shared teacher is what matters here.
    $int = $ensure('ancora_specialty', ['code' => 'INT'], ['label' => 'Intensive courses']);
    $y1 = $find('ancora_level', ['code' => 'Y1']);
    $of = ['specialty' => $id($int), 'level' => $id($y1)];
    $cohort = $ensure('ancora_cohort', ['code' => 'I1A'], $of + ['label' => 'Cohort I1A', 'enrollment' => 20]);
    $subject = $ensure('ancora_subject', ['code' => 'WSH'], $of + ['label' => 'Workshop']);
    $plan = $ensure('ancora_plan', ['code' => 'K'], [
      'label' => 'Journey intensive K',
      'starts_on' => '2027-02-01',
      'ends_on' => '2027-02-05',
    ]);
    $in = ['plan' => $id($plan)];
    $period = $ensure('ancora_period', ['code' => 'KW', 'plan' => $id($plan)], [
      'label' => 'Intensive week',
      'days' => 5,
      'slots_per_day' => 6,
      'start_hour' => 8,
      'slot_duration' => 1,
      'plan_days' => [1],
    ]);
    $group = $ensure('ancora_student_group', ['code' => 'GKL', 'plan' => $id($plan)], $of + [
      'label' => 'Intensive lecture group',
      'activity_type' => $id($find('ancora_activity_type', ['code' => 'LEC'])),
      'cohorts' => [$id($cohort)],
    ]);
    $template = $templates_of($subject, $period)[0] ?? $create('ancora_activity_template', $in + [
      'subject' => $id($subject),
      'period' => $id($period),
      'activity_type' => $id($find('ancora_activity_type', ['code' => 'LEC'])),
    ]);
    $ensure('ancora_teacher_assignment', [
      'template' => $id($template),
      'teacher' => $id($find('ancora_teacher', ['code' => 'ANA'])),
    ], $in + [
      'priority' => 1,
      'groups' => [$id($group)],
    ]);
    $ensure('ancora_room_assignment', [
      'template' => $id($template),
      'room' => $id($find('ancora_room', ['code' => 'R101'])),
    ], $in + ['priority' => 1]);
    break;

  case 'positive':
    // The positive-path dataset (plan P): ordinary academic configuration
    // in which the unchanged generator places every demand of both scopes.
    // Independent of plan J: its own specialties, levels, cohorts, subjects,
    // teachers, rooms and other resources, and later dates; only the
    // activity types LEC and LAB are shared (found or created). Three
    // choices exist ONLY to stay clear of legacy defects, and are
    // documented as such (docs/ux/planning-journey.md, "Positive path"):
    // - LAB (2 slots) is restricted at the last slot of every day (an
    //   activity-type restriction, the ordinary "no lab in the last hour"
    //   rule). Without it a lab may start at slot 6 and overflow the day
    //   (6.17), and the run then fails with 2202E in dia_minimo_impacto()
    //   (the characterization of plan J, scope MA/Y2).
    // - The analytical chemistry lab has TWO resource groups: the
    //   spectrometer (group 1) and the fume hood (group 2). generar() takes
    //   resources from every group but the template's last one (case 142,
    //   Q-31), so with one group no resource would be placed; with two, the
    //   spectrometer is placed and the fume hood never is.
    // - No lab room is shared by two activities that can run at the same
    //   time: one lab group per scope (all its cohorts), and each scope its
    //   own lab rooms. lugar_permanece_en() checks a room only at the
    //   activity's first slot, so a 2-slot lab can be given a room that is
    //   booked at its second slot, and the run fails with 23505 on
    //   restriccion_de_lugar (observed while building this dataset, with
    //   shared lab rooms and with two lab groups per scope).
    $lec = $ensure('ancora_activity_type', ['code' => 'LEC'], [
      'label' => 'Lecture',
      'duration_slots' => 1,
      'priority' => 1,
      'cost' => 1,
    ]);
    $lab = $ensure('ancora_activity_type', ['code' => 'LAB'], [
      'label' => 'Laboratory',
      'duration_slots' => 2,
      'cost' => 1,
    ]);
    $ph = $ensure('ancora_specialty', ['code' => 'PH'], ['label' => 'Physics']);
    $ch = $ensure('ancora_specialty', ['code' => 'CH'], ['label' => 'Chemistry']);
    $y3 = $ensure('ancora_level', ['code' => 'Y3'], ['label' => 'Year 3']);
    $y4 = $ensure('ancora_level', ['code' => 'Y4'], ['label' => 'Year 4']);
    $phy3 = ['specialty' => $id($ph), 'level' => $id($y3)];
    $chy4 = ['specialty' => $id($ch), 'level' => $id($y4)];
    $cohorts = [];
    foreach (['P3A' => [$phy3, 24], 'P3B' => [$phy3, 24], 'H4A' => [$chy4, 28]] as $code => [$of, $n]) {
      $cohorts[$code] = $id($ensure('ancora_cohort', ['code' => $code], $of + ['label' => "Cohort $code", 'enrollment' => $n]));
    }
    $subjects = [];
    foreach ([
      'MEC' => ['Mechanics', $phy3],
      'OPT' => ['Optics', $phy3],
      'ORG' => ['Organic chemistry', $chy4],
      'THE' => ['Thermodynamics', $phy3],
      'ANC' => ['Analytical chemistry', $chy4],
    ] as $code => [$label, $of]) {
      $subjects[$code] = $ensure('ancora_subject', ['code' => $code], $of + ['label' => $label]);
    }
    $teachers = [];
    foreach (['FAY' => 'Fay Example', 'GUS' => 'Gus Example', 'HAL' => 'Hal Example', 'IDA' => 'Ida Example'] as $code => $label) {
      $teachers[$code] = $id($ensure('ancora_teacher', ['code' => $code], ['label' => $label]));
    }
    $rooms = [];
    foreach (['HALL' => ['Lecture hall', 80], 'R201' => ['Room 201', 60], 'LABC' => ['Physics lab C', 50], 'LABD' => ['Physics lab D', 50], 'LABE' => ['Chemistry lab', 30]] as $code => [$label, $capacity]) {
      $rooms[$code] = $id($ensure('ancora_room', ['code' => $code], ['label' => $label, 'capacity' => $capacity]));
    }
    $spc = $ensure('ancora_other_resource', ['code' => 'SPC'], ['label' => 'Spectrometer']);
    $fum = $ensure('ancora_other_resource', ['code' => 'FUM'], ['label' => 'Fume hood']);
    $plan = $ensure('ancora_plan', ['code' => 'P'], [
      'label' => 'Positive spring 2027',
      'starts_on' => '2027-03-01',
      'ends_on' => '2027-03-26',
    ]);
    $in = ['plan' => $id($plan)];
    $period = $ensure('ancora_period', ['code' => 'PW', 'plan' => $id($plan)], [
      'label' => 'Positive week',
      'days' => 5,
      'slots_per_day' => 6,
      'start_hour' => 8,
      'slot_duration' => 1,
      'plan_days' => [1],
    ]);
    $groups = [];
    foreach ([
      'GPL' => ['Physics lecture group', $phy3, $lec, ['P3A', 'P3B']],
      'GPX' => ['Physics lab group', $phy3, $lab, ['P3A', 'P3B']],
      'GHL' => ['Chemistry lecture group', $chy4, $lec, ['H4A']],
      'GH4A' => ['Chemistry lab group H4A', $chy4, $lab, ['H4A']],
    ] as $code => [$label, $of, $type, $members]) {
      $groups[$code] = $id($ensure('ancora_student_group', ['code' => $code, 'plan' => $id($plan)], $of + [
        'label' => $label,
        'activity_type' => $id($type),
        'cohorts' => array_map(static fn (string $c): int => $cohorts[$c], $members),
      ]));
    }
    // Subject => [activity type, [teacher => priority], groups] per
    // template, in order. Rooms: lectures HALL then R201, labs LABC then
    // LABD. HAL teaches in both scopes.
    $types = ['LEC' => $lec, 'LAB' => $lab];
    $layout = [
      'MEC' => [
        ['LEC', ['FAY' => 1, 'GUS' => 2], ['GPL']],
        ['LEC', ['FAY' => 1, 'GUS' => 2], ['GPL']],
        ['LAB', ['GUS' => 1, 'FAY' => 2], ['GPX']],
      ],
      'OPT' => [
        ['LEC', ['HAL' => 1, 'FAY' => 2], ['GPL']],
        ['LAB', ['HAL' => 1, 'GUS' => 2], ['GPX']],
      ],
      'THE' => [
        ['LEC', ['GUS' => 1, 'HAL' => 2], ['GPL']],
        ['LEC', ['GUS' => 1, 'HAL' => 2], ['GPL']],
        ['LAB', ['FAY' => 1, 'GUS' => 2], ['GPX']],
      ],
      'ORG' => [
        ['LEC', ['IDA' => 1, 'HAL' => 2], ['GHL']],
        ['LEC', ['IDA' => 1, 'HAL' => 2], ['GHL']],
        ['LAB', ['IDA' => 1, 'HAL' => 2], ['GH4A']],
      ],
      'ANC' => [
        ['LEC', ['HAL' => 1, 'IDA' => 2], ['GHL']],
        ['LAB', ['HAL' => 1, 'IDA' => 2], ['GH4A']],
      ],
    ];
    $room_layout = [
      'PH' => ['LEC' => ['HALL' => 1, 'R201' => 2], 'LAB' => ['LABC' => 1, 'LABD' => 2]],
      'CH' => ['LEC' => ['HALL' => 1, 'R201' => 2], 'LAB' => ['LABE' => 1]],
    ];
    $specialty_of = ['MEC' => 'PH', 'OPT' => 'PH', 'THE' => 'PH', 'ORG' => 'CH', 'ANC' => 'CH'];
    foreach ($layout as $subject_code => $rows) {
      $existing = $templates_of($subjects[$subject_code], $period);
      foreach ($rows as $n => [$t, $by_teacher, $group_codes]) {
        $template = $existing[$n] ?? $create('ancora_activity_template', $in + [
          'subject' => $id($subjects[$subject_code]),
          'period' => $id($period),
          'activity_type' => $id($types[$t]),
        ]);
        foreach ($by_teacher as $code => $priority) {
          $ensure('ancora_teacher_assignment', ['template' => $id($template), 'teacher' => $teachers[$code]], $in + [
            'priority' => $priority,
            'groups' => array_map(static fn (string $g): int => $groups[$g], $group_codes),
          ]);
        }
        foreach ($room_layout[$specialty_of[$subject_code]][$t] as $code => $priority) {
          $ensure('ancora_room_assignment', ['template' => $id($template), 'room' => $rooms[$code]], $in + ['priority' => $priority]);
        }
        if ($subject_code === 'ANC' && $t === 'LAB' && !$find('ancora_resource_group', ['template' => $id($template)])) {
          foreach ([[1, $spc], [2, $fum]] as [$priority, $resource]) {
            $group = $create('ancora_resource_group', $in + [
              'template' => $id($template),
              'priority' => $priority,
              'needed' => 1,
              'essential' => TRUE,
            ]);
            $create('ancora_resource_member', $in + [
              'resource_group' => $id($group),
              'other_resource' => $id($resource),
              'priority' => 1,
            ]);
          }
        }
      }
    }
    // No lab in the last hour of the day (see above), and an ordinary
    // teacher restriction: IDA is not available on day 2, slots 1 and 2.
    for ($day = 1; $day <= 5; $day++) {
      $ensure('ancora_time_restriction', [
        'kind' => 'activity_type',
        'activity_type' => $id($lab),
        'period' => $id($period),
        'day' => $day,
        'slot' => 6,
      ], $in);
    }
    foreach ([1, 2] as $slot) {
      $ensure('ancora_time_restriction', [
        'kind' => 'teacher',
        'teacher' => $teachers['IDA'],
        'period' => $id($period),
        'day' => 2,
        'slot' => $slot,
      ], $in);
    }
    $out = ['plan' => $id($plan), 'period' => $id($period)];
    break;

  case 'show':
    // One record's stored field values (UI coverage checks): scalar values
    // and target ids (integers and booleans as numbers), multi-valued
    // fields as lists.
    [, , $entity_id] = $extra + [NULL, NULL, NULL];
    $storage = $etm->getStorage($arg);
    $storage->resetCache();
    $record = $storage->load($entity_id);
    if ($record === NULL) {
      throw new \RuntimeException("No $arg $entity_id.");
    }
    $out = ['id' => (int) $record->id(), 'label' => $record->label()];
    foreach ($record->getFields(FALSE) as $name => $items) {
      if (in_array($name, ['uuid', 'created', 'changed'], TRUE)) {
        continue;
      }
      $values = [];
      foreach ($items as $item) {
        $main = $item->mainPropertyName();
        $value = $item->get($main)->getValue();
        // Integers (ids, numbers, booleans as 0/1) as numbers.
        $values[] = is_string($value) && preg_match('/^-?\d+$/', $value) ? (int) $value : (is_bool($value) ? (int) $value : $value);
      }
      $out[$name] = count($values) === 1 && $items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() === 1 ? $values[0] : $values;
    }
    break;

  case 'find':
    // The id of the planner's record of a type with the given code (UI
    // coverage checks), or NULL.
    $record = $find($arg, ['code' => $extra[2] ?? '']);
    $out = ['id' => $record === NULL ? NULL : (int) $record->id()];
    break;

  case 'inspect':
    $plan = $plan_of($arg ?? 'J');
    $out = inspect_plan($plan, $etm, $assignments);
    break;

  case 'ids':
    // Code => id of the planner's records, for the form selects; templates
    // by label.
    $out = [];
    foreach ([
      'ancora_plan' => 'plan',
      'ancora_activity_type' => 'type',
      'ancora_specialty' => 'specialty',
      'ancora_level' => 'level',
      'ancora_cohort' => 'cohort',
      'ancora_subject' => 'subject',
      'ancora_teacher' => 'teacher',
      'ancora_room' => 'room',
      'ancora_other_resource' => 'resource',
    ] as $type => $key) {
      $out[$key] = [];
      foreach ($etm->getStorage($type)->loadByProperties(['uid' => $uid]) as $record) {
        $out[$key][$record->get('code')->value] = (int) $record->id();
      }
    }
    $plans = array_values($out['plan']);
    foreach (['ancora_period' => 'period', 'ancora_student_group' => 'group'] as $type => $key) {
      $out[$key] = [];
      foreach ($plans === [] ? [] : $etm->getStorage($type)->loadByProperties(['plan' => $plans]) as $record) {
        $out[$key][$record->get('code')->value] = (int) $record->id();
      }
    }
    $out['template'] = [];
    foreach ($plans === [] ? [] : $etm->getStorage('ancora_activity_template')->loadByProperties(['plan' => $plans]) as $record) {
      $out['template'][$record->label()] = (int) $record->id();
    }
    break;

  default:
    throw new \RuntimeException("Unknown command '$command'.");
}
\Drupal::service('account_switcher')->switchBack();
echo json_encode($out ?? ['ok' => $command], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";

/**
 * The plan's runs, placements and the integrity observations, as an array.
 */
function inspect_plan($plan, $etm, TemplateAssignments $assignments): array {
  $code = static fn ($e): ?string => $e?->get('code')->value;
  $plan_id = (int) $plan->id();
  $periods = [];
  foreach ($etm->getStorage('ancora_period')->loadByProperties(['plan' => $plan_id]) as $period) {
    $breaks = [];
    foreach ($etm->getStorage('ancora_break')->loadByProperties(['period' => $period->id()]) as $break) {
      $breaks[] = $break->getAfterSlot();
    }
    $periods[(int) $period->id()] = [
      'code' => $code($period),
      'days' => $period->getDays(),
      'slots' => $period->getSlotsPerDay(),
      'breaks_after' => $breaks,
    ];
  }

  // The runs of the plan, oldest first.
  $jobs = [];
  $rows = \Drupal::database()->select('ancora_engine_job', 'j')
    ->fields('j', [
      'id', 'state', 'period_id', 'specialty_id', 'level_id', 'result',
      'error', 'created', 'started', 'finished',
    ])
    ->condition('plan_id', $plan_id)->orderBy('id')->execute()->fetchAll();
  foreach ($rows as $row) {
    $result = json_decode((string) $row->result, TRUE) ?: [];
    $jobs[] = [
      'id' => (int) $row->id,
      'state' => $row->state,
      'period' => $periods[(int) $row->period_id]['code'] ?? NULL,
      'specialty' => $code($etm->getStorage('ancora_specialty')->load($row->specialty_id)),
      'level' => $code($etm->getStorage('ancora_level')->load($row->level_id)),
      'queued_s' => $row->started ? (int) $row->started - (int) $row->created : NULL,
      'run_s' => $row->finished && $row->started ? (int) $row->finished - (int) $row->started : NULL,
      'error' => $row->error === NULL ? NULL : mb_substr((string) $row->error, 0, 500),
      'metrics' => array_intersect_key($result, array_flip([
        'demands', 'placed', 'unplaced', 'collisions', 'overflow',
        'duration_ms', 'partial', 'kept', 'input_hash', 'mode',
      ])),
    ];
  }

  // The demands of the plan by scope: template x eligible group with a
  // teacher assigned to it (TemplateAssignments::groups, the editor's view).
  $demands = [];
  foreach ($etm->getStorage('ancora_activity_template')->loadByProperties(['plan' => $plan_id]) as $template) {
    $subject = $template->getSubject();
    $scope = $code($template->getPeriod()) . '/' . $code($subject?->get('specialty')->entity) . '/' . $code($subject?->get('level')->entity);
    $demands[$scope] = ($demands[$scope] ?? 0) + count($assignments->groups($template));
  }

  // Placements and the cells each occupies.
  $placements = [];
  $occupancy = [];
  $type_cells = [];
  $overflow = [];
  $crossing = [];
  $storage = $etm->getStorage('ancora_placement');
  $storage->resetCache();
  $ids = $storage->getQuery()->accessCheck(FALSE)->condition('plan', $plan_id)->sort('id')->execute();
  foreach ($storage->loadMultiple($ids) as $p) {
    $template = $p->get('template')->entity;
    $group = $p->get('student_group')->entity;
    $period_id = (int) $p->get('period')->target_id;
    $length = (int) ($template?->getActivityType()?->get('duration_slots')->value ?? 1);
    $day = (int) $p->get('day')->value;
    $slot = (int) $p->get('slot')->value;
    $resources = array_map($code, $p->get('other_resources')->referencedEntities());
    $cohorts = $group ? array_map($code, $etm->getStorage('ancora_cohort')->loadMultiple($group->getCohortIds())) : [];
    $row = [
      'id' => (int) $p->id(),
      'template' => $template?->label(),
      'subject' => $code($template?->getSubject()),
      'type' => $code($template?->getActivityType()),
      'length' => $length,
      'group' => $code($group),
      'cohorts' => array_values($cohorts),
      'teacher' => $code($p->get('teacher')->entity),
      'room' => $code($p->get('room')->entity),
      'resources' => array_values($resources),
      'period' => $periods[$period_id]['code'] ?? NULL,
      'day' => $day,
      'slot' => $slot,
      'job' => (int) $p->get('job')->value,
    ];
    $placements[] = $row;
    $slots = $periods[$period_id]['slots'] ?? 0;
    if ($slot + $length - 1 > $slots) {
      $overflow[] = $row['id'];
    }
    foreach ($periods[$period_id]['breaks_after'] ?? [] as $after) {
      if ($slot <= $after && $after < $slot + $length - 1) {
        $crossing[] = $row['id'];
      }
    }
    $owners = [
      'teacher' => [$row['teacher']],
      'room' => [$row['room']],
      'group' => [$row['group']],
      'cohort' => $row['cohorts'],
      'resource' => $row['resources'],
    ];
    for ($k = 0; $k < $length; $k++) {
      // The cells of each activity type (restriction checks only; two
      // activities of one type in a cell are not a collision).
      $type_cells[$row['type']]["$period_id.$day." . ($slot + $k)][] = $row['id'];
      foreach ($owners as $kind => $codes) {
        foreach (array_filter($codes) as $c) {
          $occupancy["$kind:$c"]["$period_id.$day." . ($slot + $k)][] = $row['id'];
        }
      }
    }
  }
  $collisions = [];
  foreach ($occupancy as $who => $cells) {
    foreach ($cells as $cell => $by) {
      if (count($by) > 1) {
        $collisions[] = ['who' => $who, 'cell' => $cell, 'placements' => $by];
      }
    }
  }

  // Restricted cells that a placement occupies (reported, not judged).
  $restricted = [];
  $kinds = ['teacher' => 'teacher', 'room' => 'room', 'cohort' => 'cohort'];
  foreach ($etm->getStorage('ancora_time_restriction')->loadByProperties(['plan' => $plan_id]) as $r) {
    $kind = $r->get('kind')->value;
    $cell = $r->get('period')->target_id . '.' . $r->get('day')->value . '.' . $r->get('slot')->value;
    $who = $kind === 'period' ? NULL : (isset($kinds[$kind]) ? $kind . ':' . $code($r->get($kind)->entity) : NULL);
    $hits = [];
    if ($who !== NULL) {
      $hits = $occupancy[$who][$cell] ?? [];
    }
    elseif ($kind === 'activity_type') {
      $who = 'activity_type:' . $code($r->get('activity_type')->entity);
      $hits = $type_cells[$code($r->get('activity_type')->entity)][$cell] ?? [];
    }
    elseif ($kind === 'period') {
      foreach ($occupancy as $w => $cells) {
        if (str_starts_with($w, 'group:')) {
          $hits = [...$hits, ...($cells[$cell] ?? [])];
        }
      }
    }
    $restricted[] = [
      'kind' => $kind,
      'who' => $who,
      'cell' => $cell,
      'occupied_by' => array_values(array_unique($hits)),
    ];
  }

  // Activities per day of the objects with a daily load limit (reported).
  $loads = [];
  foreach ($etm->getStorage('ancora_load_limit')->loadByProperties(['plan' => $plan_id]) as $limit) {
    $kind = $limit->get('kind')->value;
    $object = $code($limit->get($kind)->entity);
    $per_day = [];
    foreach ($placements as $row) {
      $mine = $kind === 'teacher' ? $row['teacher'] === $object : in_array($object, $row['cohorts'], TRUE);
      if ($mine) {
        $per_day[$row['day']] = ($per_day[$row['day']] ?? 0) + 1;
      }
    }
    ksort($per_day);
    $loads[] = [
      'kind' => $kind,
      'object' => $object,
      'max_daily_load' => (int) $limit->get('max_daily_load')->value,
      'activities_per_day' => $per_day,
    ];
  }

  // Teachers (code:priority:groups) and rooms (code:priority) by template.
  $eligibility = [];
  foreach ($etm->getStorage('ancora_teacher_assignment')->loadByProperties(['plan' => $plan_id]) as $a) {
    $groups = array_map($code, $etm->getStorage('ancora_student_group')->loadMultiple($a->getGroupIds()));
    $eligibility[$a->get('template')->entity?->label()]['teachers'][] = $code($a->get('teacher')->entity) . ':' . $a->get('priority')->value . ':' . implode('+', $groups);
  }
  foreach ($etm->getStorage('ancora_room_assignment')->loadByProperties(['plan' => $plan_id]) as $a) {
    $eligibility[$a->get('template')->entity?->label()]['rooms'][] = $code($a->get('room')->entity) . ':' . $a->get('priority')->value;
  }
  ksort($eligibility);
  foreach ($eligibility as &$lists) {
    foreach ($lists as &$list) {
      sort($list);
    }
  }
  unset($lists, $list);

  // The other-resource groups of the plan's templates.
  $resource_groups = [];
  foreach ($etm->getStorage('ancora_resource_group')->loadByProperties(['plan' => $plan_id]) as $g) {
    $members = $etm->getStorage('ancora_resource_member')->loadByProperties(['resource_group' => $g->id()]);
    $resource_groups[] = [
      'template' => $g->get('template')->entity?->label(),
      'needed' => (int) $g->get('needed')->value,
      'essential' => (bool) $g->get('essential')->value,
      'members' => array_values(array_map(static fn ($m): ?string => $code($m->get('other_resource')->entity), $members)),
    ];
  }

  $changes = [];
  $change_storage = $etm->getStorage('ancora_placement_change');
  $change_storage->resetCache();
  foreach ($change_storage->loadByProperties(['plan' => $plan_id]) as $c) {
    $changes[] = [
      'id' => (int) $c->id(),
      'placement' => (int) $c->get('placement')->target_id,
      'reason' => $c->get('reason')->value,
      'reverts' => $c->get('reverts')->target_id === NULL ? NULL : (int) $c->get('reverts')->target_id,
      'from' => $c->get('from_day')->value . '.' . $c->get('from_slot')->value,
      'to' => $c->get('to_day')->value . '.' . $c->get('to_slot')->value,
      'uid' => (int) $c->get('uid')->target_id,
    ];
  }

  return [
    'plan' => $plan_id,
    'starts_on' => $plan->get('starts_on')->value,
    'ends_on' => $plan->get('ends_on')->value,
    'periods' => $periods,
    'jobs' => $jobs,
    'demands' => $demands,
    'count' => count($placements),
    'placements' => $placements,
    'collisions' => $collisions,
    'overflow' => $overflow,
    'break_crossing' => $crossing,
    'restricted_cells' => $restricted,
    'loads' => $loads,
    'eligibility' => $eligibility,
    'resource_groups' => $resource_groups,
    'changes' => $changes,
  ];
}
