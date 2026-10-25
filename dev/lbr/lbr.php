<?php

/**
 * Legacy Bytes Remanufacturing (LBR) inventory and report (issues #38, #67, #77).
 *
 * Method: https://github.com/rafageist/blog-reflections/wiki/2026.014.-Legacy-Bytes-Remanufacturing
 *
 * Each baseline is a named, independent SET in dev/lbr/sets/NAME/:
 *   set.json          baseline commit (and optional origin commit), description
 *   rules.tsv         classification of every file at the baseline commit
 *   baseline.tsv      the frozen inventory (written once by `freeze`)
 *   retirements.tsv   verified retirement records
 * Sets are never combined into one percentage.
 *
 *   php dev/lbr/lbr.php preview   --set=S [--at=REV]   inventory of REV under S's rules (not frozen)
 *   php dev/lbr/lbr.php freeze    --set=S --at=REV     freeze S at REV (once)
 *   php dev/lbr/lbr.php verify    [--set=S]            the frozen baseline reproduces from git
 *   php dev/lbr/lbr.php perimeter [--set=S]            included and excluded bytes, by reason
 *   php dev/lbr/lbr.php check     [--set=S]            validate the retirement records against HEAD
 *   php dev/lbr/lbr.php report    [--set=S] [--json]   B, C, retired, % by stream/component
 *
 * B = bytes of the included baseline files (frozen); C = baseline bytes not yet
 * retired with verified evidence; Remaining % = 100 C / B; Completion % = 100 (B - C) / B.
 *
 * Only a "verified" record lowers C. Deleting, moving or renaming a baseline
 * file retires nothing. dev/lbr/relocations.tsv maps moved directories (for
 * example the move into legacy/, #68) so a relocated file is still found at
 * HEAD and still counted. Everything is read from git objects.
 * Exit status: 0 ok, 1 validation failure, 2 usage error.
 */

declare(strict_types=1);

$dir = __DIR__;
$repo = dirname($dir, 2);

function fail(string $msg, int $code = 1): never
{
    fwrite(STDERR, "lbr: $msg\n");
    exit($code);
}

function git(string $repo, array $args): string
{
    $cmd = 'git -C ' . escapeshellarg($repo);
    foreach ($args as $a) {
        $cmd .= ' ' . escapeshellarg($a);
    }
    $out = [];
    $status = 0;
    exec($cmd . ' 2>/dev/null', $out, $status);
    if ($status !== 0) {
        throw new RuntimeException('git ' . implode(' ', $args) . " failed ($status)");
    }
    return implode("\n", $out);
}

/** @return array<string, array{blob: string, bytes: int}> path => entry, regular files */
function tree(string $repo, string $commit): array
{
    $raw = shell_exec('git -C ' . escapeshellarg($repo) . ' ls-tree -r -l -z --full-tree ' . escapeshellarg($commit) . ' 2>/dev/null');
    if (!is_string($raw) || $raw === '') {
        throw new RuntimeException("cannot list $commit");
    }
    $files = [];
    foreach (explode("\0", $raw) as $rec) {
        if ($rec === '') {
            continue;
        }
        [$meta, $path] = explode("\t", $rec, 2);
        $f = preg_split('/\s+/', trim($meta));
        if ($f[1] !== 'blob') {
            continue;
        }
        $files[$path] = ['blob' => $f[2], 'bytes' => (int) $f[3]];
    }
    ksort($files, SORT_STRING);
    return $files;
}

function globRegex(string $glob): string
{
    $re = '';
    $n = strlen($glob);
    for ($i = 0; $i < $n; $i++) {
        $c = $glob[$i];
        if ($c === '*' && $i + 1 < $n && $glob[$i + 1] === '*') {
            $re .= '.*';
            $i++;
        } elseif ($c === '*') {
            $re .= '[^/]*';
        } elseif ($c === '?') {
            $re .= '[^/]';
        } else {
            $re .= preg_quote($c, '#');
        }
    }
    return '#^' . $re . '$#';
}

/** @return array{name: string, dir: string, baseline_commit: ?string, origin_commit: ?string, description: string} */
function loadSet(string $dir, string $name): array
{
    if (!preg_match('/^[a-z0-9][a-z0-9-]*$/', $name) || !is_file("$dir/sets/$name/set.json")) {
        fail("unknown set '$name'");
    }
    $c = json_decode((string) file_get_contents("$dir/sets/$name/set.json"), true);
    if (!is_array($c)) {
        fail("sets/$name/set.json is not valid JSON");
    }
    return ['name' => $name, 'dir' => "$dir/sets/$name", 'baseline_commit' => $c['baseline_commit'] ?? null,
        'origin_commit' => $c['origin_commit'] ?? null, 'description' => (string) ($c['description'] ?? '')];
}

/** @return list<string> */
function setNames(string $dir): array
{
    $names = [];
    foreach (glob("$dir/sets/*/set.json") as $f) {
        $names[] = basename(dirname($f));
    }
    sort($names);
    return $names;
}

/** @return list<array{re: string, action: string, stream: string, component: string, reason: string}> */
function rules(string $file): array
{
    if (!is_file($file)) {
        fail("missing $file");
    }
    $rules = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) as $i => $line) {
        if (trim($line) === '' || $line[0] === '#') {
            continue;
        }
        $c = explode("\t", $line);
        if (count($c) !== 5 || !in_array($c[1], ['include', 'exclude'], true)) {
            fail(basename(dirname($file)) . "/rules.tsv line " . ($i + 1) . ": expected 5 tab-separated columns with include|exclude");
        }
        if ($c[1] === 'include' && ($c[2] === '-' || $c[3] === '-')) {
            fail(basename(dirname($file)) . "/rules.tsv line " . ($i + 1) . ": an include rule needs a stream and a component");
        }
        $rules[] = ['re' => globRegex($c[0]), 'action' => $c[1], 'stream' => $c[2], 'component' => $c[3], 'reason' => $c[4]];
    }
    return $rules;
}

/** Classify every file of $commit with the set's rules (and origin filter, if any). */
function inventory(string $repo, array $set, string $commit): array
{
    $rules = rules($set['dir'] . '/rules.tsv');
    $base = tree($repo, $commit);
    $origin = $set['origin_commit'] === null ? null : tree($repo, $set['origin_commit']);
    $included = [];
    $excluded = [];
    $unmatched = [];
    foreach ($base as $path => $e) {
        $rule = null;
        foreach ($rules as $r) {
            if (preg_match($r['re'], $path)) {
                $rule = $r;
                break;
            }
        }
        if ($rule === null) {
            $unmatched[] = $path;
            continue;
        }
        $reason = $rule['reason'];
        if ($rule['action'] === 'include' && $origin !== null && !isset($origin[$path])) {
            $reason = 'added after the historical import (recovery work), not legacy';
        } elseif ($rule['action'] === 'include') {
            $included[$path] = $e + ['stream' => $rule['stream'], 'component' => $rule['component']];
            continue;
        }
        $excluded[$reason] ??= ['bytes' => 0, 'files' => 0];
        $excluded[$reason]['bytes'] += $e['bytes'];
        $excluded[$reason]['files']++;
    }
    ksort($excluded, SORT_STRING);
    return ['included' => $included, 'excluded' => $excluded, 'unmatched' => $unmatched];
}

function baselineText(string $repo, array $set, string $sha, array $inv, int $revision): string
{
    $b = array_sum(array_column($inv['included'], 'bytes'));
    $out = "# LBR frozen baseline, set {$set['name']} (issues #38, #67). Generated by `php dev/lbr/lbr.php freeze`; do not edit.\n"
        . "# revision=$revision\n"
        . "# baseline_commit=$sha\n"
        . ($set['origin_commit'] !== null ? '# origin_commit=' . git($repo, ['rev-parse', $set['origin_commit'] . '^{commit}']) . "\n" : '')
        . '# rules_sha256=' . hash_file('sha256', $set['dir'] . '/rules.tsv') . "\n"
        . '# files=' . count($inv['included']) . "\n"
        . "# B=$b\n"
        . "# path\tstream\tcomponent\tbytes\tblob\n";
    foreach ($inv['included'] as $path => $e) {
        $out .= "$path\t{$e['stream']}\t{$e['component']}\t{$e['bytes']}\t{$e['blob']}\n";
    }
    return $out;
}

function loadBaseline(array $set, string $name = 'baseline.tsv'): array
{
    $file = $set['dir'] . '/' . $name;
    if (!is_file($file)) {
        return ['meta' => [], 'files' => [], 'frozen' => false];
    }
    $meta = [];
    $files = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
        if (preg_match('/^# ([a-z_0-9A-Z]+)=(.*)$/', $line, $m)) {
            $meta[$m[1]] = $m[2];
            continue;
        }
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        [$path, $stream, $component, $bytes, $blob] = explode("\t", $line);
        $files[$path] = ['stream' => $stream, 'component' => $component, 'bytes' => (int) $bytes, 'blob' => $blob];
    }
    return ['meta' => $meta, 'files' => $files, 'frozen' => true];
}

const RETIREMENT_COLUMNS = ['id', 'path', 'status', 'responsibility', 'replacement', 'evidence', 'pr', 'commit', 'reviewed_by'];

function loadRetirements(array $set): array
{
    $rows = [];
    $file = $set['dir'] . '/retirements.tsv';
    foreach (is_file($file) ? file($file, FILE_IGNORE_NEW_LINES) : [] as $i => $line) {
        if (trim($line) === '' || $line[0] === '#') {
            continue;
        }
        $c = explode("\t", $line);
        if (count($c) !== count(RETIREMENT_COLUMNS)) {
            fail("{$set['name']}/retirements.tsv line " . ($i + 1) . ': expected ' . count(RETIREMENT_COLUMNS) . ' tab-separated columns');
        }
        $rows[] = array_combine(RETIREMENT_COLUMNS, $c) + ['line' => (string) ($i + 1)];
    }
    return $rows;
}

/** @return list<array{0: string, 1: string}> old prefix => new prefix */
function relocations(string $dir): array
{
    $map = [];
    $file = "$dir/relocations.tsv";
    foreach (is_file($file) ? file($file, FILE_IGNORE_NEW_LINES) : [] as $i => $line) {
        if (trim($line) === '' || $line[0] === '#') {
            continue;
        }
        $c = explode("\t", $line);
        if (count($c) !== 2 || $c[0] === '' || $c[1] === '') {
            fail('relocations.tsv line ' . ($i + 1) . ': expected "old/prefix<TAB>new/prefix"');
        }
        $map[] = [$c[0], $c[1]];
    }
    return $map;
}

/** Where a baseline path may be at HEAD: itself and every relocation of it. @return list<string> */
function locations(string $path, array $map): array
{
    $paths = [$path];
    foreach ($map as [$old, $new]) {
        if (str_starts_with($path, $old)) {
            $paths[] = $new . substr($path, strlen($old));
        }
    }
    return $paths;
}

function covered(array $files, string $path): array
{
    if (str_ends_with($path, '/')) {
        return array_values(array_filter(array_keys($files), fn ($p) => str_starts_with($p, $path)));
    }
    return isset($files[$path]) ? [$path] : [];
}

/** @return array{0: list<string>, 1: array<string, string>, 2: array<string, string>} errors, retired, in transition */
function checkRetirements(string $repo, string $dir, array $set, array $baseline, array $head): array
{
    $files = $baseline['files'];
    $map = relocations($dir);
    $headBlobs = [];
    foreach ($head as $p => $e) {
        $headBlobs[$e['blob']][] = $p;
    }
    $errors = [];
    $retired = [];
    $transition = [];
    $ids = [];
    foreach (loadRetirements($set) as $r) {
        $at = "{$set['name']}/retirements.tsv line {$r['line']} ({$r['id']})";
        if (!preg_match('/^LBR-[0-9]{3,}$/', $r['id']) || isset($ids[$r['id']])) {
            $errors[] = "$at: id must be unique and look like LBR-001";
        }
        $ids[$r['id']] = true;
        $paths = covered($files, $r['path']);
        if ($paths === []) {
            $errors[] = "$at: '{$r['path']}' is not in the frozen baseline";
            continue;
        }
        foreach ($paths as $p) {
            if (isset($retired[$p]) || isset($transition[$p])) {
                $errors[] = "$at: $p is already covered by another record";
            }
        }
        if ($r['status'] === 'in-transition') {
            foreach ($paths as $p) {
                $transition[$p] = $r['id'];
            }
            continue;
        }
        if ($r['status'] !== 'verified') {
            $errors[] = "$at: status must be in-transition or verified";
            continue;
        }
        foreach (['responsibility', 'replacement', 'evidence', 'pr', 'commit', 'reviewed_by'] as $k) {
            if (trim($r[$k]) === '' || $r[$k] === '-') {
                $errors[] = "$at: a verified retirement needs '$k'";
            }
        }
        if (!preg_match('/^#[0-9]+$/', $r['pr'])) {
            $errors[] = "$at: pr must look like #123";
        }
        try {
            git($repo, ['merge-base', '--is-ancestor', $r['commit'], 'HEAD']);
        } catch (RuntimeException) {
            $errors[] = "$at: commit '{$r['commit']}' is not an ancestor of HEAD";
        }
        foreach ($paths as $p) {
            foreach (locations($p, $map) as $loc) {
                if (isset($head[$loc])) {
                    $errors[] = "$at: $p is still present at HEAD" . ($loc === $p ? '' : " as $loc (relocated)") . '; the old implementation must be gone';
                }
            }
            if (isset($headBlobs[$files[$p]['blob']])) {
                $errors[] = "$at: the content of $p still exists at HEAD as " . implode(', ', $headBlobs[$files[$p]['blob']]) . ' (a move is not a retirement)';
            }
            $retired[$p] = $r['id'];
        }
    }
    return [$errors, $retired, $transition];
}

function pct(int $part, int $total): string
{
    return $total > 0 ? sprintf('%.2f', 100 * $part / $total) : 'n/a';
}

function perimeterTable(array $inv, string $at): string
{
    $inc = $inv['included'];
    $out = "| Perimeter at $at | Files | Bytes |\n| --- | ---: | ---: |\n";
    $out .= '| **included (B)** | ' . count($inc) . ' | ' . number_format(array_sum(array_column($inc, 'bytes'))) . " |\n";
    $streams = [];
    foreach ($inc as $e) {
        $streams[$e['stream']] ??= [0, 0];
        $streams[$e['stream']][0]++;
        $streams[$e['stream']][1] += $e['bytes'];
    }
    ksort($streams);
    foreach ($streams as $s => [$n, $b]) {
        $out .= "| &nbsp;&nbsp;included: $s | $n | " . number_format($b) . " |\n";
    }
    foreach ($inv['excluded'] as $reason => $a) {
        $out .= "| excluded: $reason | {$a['files']} | " . number_format($a['bytes']) . " |\n";
    }
    return $out;
}

/** @return array<string, mixed> */
function setReport(string $repo, string $dir, array $set, array $head): array
{
    $baseline = loadBaseline($set);
    if (!$baseline['frozen']) {
        return ['set' => $set['name'], 'frozen' => false, 'description' => $set['description']];
    }
    [$errors, $retired, $transition] = checkRetirements($repo, $dir, $set, $baseline, $head);
    $map = relocations($dir);
    $B = 0;
    $C = 0;
    $transitionBytes = 0;
    $relocated = 0;
    $streams = [];
    $components = [];
    $missing = [];
    foreach ($baseline['files'] as $p => $e) {
        $B += $e['bytes'];
        $isRetired = isset($retired[$p]) && $errors === [];
        foreach ([[&$streams, $e['stream']], [&$components, $e['stream'] . ' / ' . $e['component']]] as [&$agg, $key]) {
            $agg[$key] ??= ['B' => 0, 'C' => 0, 'files' => 0];
            $agg[$key]['B'] += $e['bytes'];
            $agg[$key]['files']++;
            if (!$isRetired) {
                $agg[$key]['C'] += $e['bytes'];
            }
        }
        unset($agg);
        if ($isRetired) {
            continue;
        }
        $C += $e['bytes'];
        $where = array_values(array_filter(locations($p, $map), fn ($l) => isset($head[$l])));
        if ($where === []) {
            $missing[] = $p;
        } elseif ($where[0] !== $p) {
            $relocated++;
        }
        if (isset($transition[$p])) {
            $transitionBytes += $e['bytes'];
        }
    }
    ksort($streams, SORT_STRING);
    ksort($components, SORT_STRING);
    return ['set' => $set['name'], 'frozen' => true, 'description' => $set['description'],
        'baseline_commit' => $baseline['meta']['baseline_commit'] ?? '', 'revision' => $baseline['meta']['revision'] ?? '',
        'B' => $B, 'C' => $C, 'retired' => $B - $C, 'remaining_pct' => pct($C, $B), 'completion_pct' => pct($B - $C, $B),
        'in_transition_bytes' => $transitionBytes, 'relocated_files' => $relocated, 'streams' => $streams,
        'components' => $components, 'missing_without_retirement' => $missing, 'record_errors' => $errors];
}

function markdown(array $d): string
{
    if (!$d['frozen']) {
        return "## LBR set `{$d['set']}`\n\n{$d['description']}\n\nNot frozen yet: no metric is reported until its cut commit is frozen.\n";
    }
    $row = fn ($name, $a) => sprintf("| %s | %d | %s | %s | %s | %s |\n", $name, $a['files'], number_format($a['B']),
        number_format($a['C']), number_format($a['B'] - $a['C']), pct($a['B'] - $a['C'], $a['B']));
    $o = "## LBR set `{$d['set']}`\n\n{$d['description']} Baseline `" . substr($d['baseline_commit'], 0, 10)
        . "` (revision {$d['revision']}). Progress metric only: it does not measure security, quality or technical debt.\n\n";
    $o .= "| | Bytes |\n| --- | ---: |\n| B (baseline) | " . number_format($d['B']) . " |\n| C (remaining) | " . number_format($d['C'])
        . " |\n| Retired (B − C) | " . number_format($d['retired']) . " |\n| Remaining | {$d['remaining_pct']} % |\n| Completion | {$d['completion_pct']} % |\n"
        . '| In transition (still counted in C) | ' . number_format($d['in_transition_bytes']) . " |\n"
        . "| Relocated files (still counted in C) | {$d['relocated_files']} |\n\n";
    $o .= "| Stream | Files | B | C | Retired | Completion % |\n| --- | ---: | ---: | ---: | ---: | ---: |\n";
    foreach ($d['streams'] as $k => $a) {
        $o .= $row($k, $a);
    }
    $o .= "\n<details><summary>By component</summary>\n\n| Component | Files | B | C | Retired | Completion % |\n| --- | ---: | ---: | ---: | ---: | ---: |\n";
    foreach ($d['components'] as $k => $a) {
        $o .= $row($k, $a);
    }
    $o .= "\n</details>\n";
    if ($d['missing_without_retirement'] !== []) {
        $o .= "\n**" . count($d['missing_without_retirement']) . " baseline file(s) are gone from HEAD without a verified retirement record; they stay in C:**\n\n";
        foreach ($d['missing_without_retirement'] as $p) {
            $o .= "- `$p`\n";
        }
    }
    if ($d['record_errors'] !== []) {
        $o .= "\n**Invalid retirement records (nothing is credited until fixed):**\n\n";
        foreach ($d['record_errors'] as $e) {
            $o .= "- $e\n";
        }
    }
    return $o;
}

// ------------------------------------------------------------------ main
$cmd = $argv[1] ?? 'help';
$opts = [];
foreach (array_slice($argv, 2) as $a) {
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $m)) {
        $opts[$m[1]] = $m[2] ?? '1';
    } else {
        fail("unexpected argument '$a'", 2);
    }
}
$sets = isset($opts['set']) ? [loadSet($dir, $opts['set'])] : array_map(fn ($n) => loadSet($dir, $n), setNames($dir));

try {
    switch ($cmd) {
        case 'preview':
            if (!isset($opts['set'])) {
                fail('preview needs --set', 2);
            }
            $set = $sets[0];
            $at = $opts['at'] ?? 'HEAD';
            $inv = inventory($repo, $set, $at);
            if ($inv['unmatched'] !== []) {
                fail("unclassified files at $at:\n  " . implode("\n  ", $inv['unmatched']));
            }
            echo perimeterTable($inv, $at . ' (' . substr(git($repo, ['rev-parse', $at . '^{commit}']), 0, 10) . ', preview, not frozen)');
            break;

        case 'freeze':
            if (!isset($opts['set'], $opts['at'])) {
                fail('freeze needs --set and --at', 2);
            }
            $set = $sets[0];
            $old = loadBaseline($set);
            if ($old['frozen'] && !isset($opts['force-revision'])) {
                fail("set {$set['name']} is already frozen (a revision needs --force-revision and a documented reason)");
            }
            $sha = git($repo, ['rev-parse', $opts['at'] . '^{commit}']);
            $inv = inventory($repo, $set, $sha);
            if ($inv['unmatched'] !== []) {
                fail("unclassified files at {$opts['at']}:\n  " . implode("\n  ", $inv['unmatched']));
            }
            $config = json_decode((string) file_get_contents($set['dir'] . '/set.json'), true);
            $config['baseline_commit'] = $sha;
            file_put_contents($set['dir'] . '/set.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
            $revision = $old['frozen'] ? ((int) ($old['meta']['revision'] ?? 1)) + 1 : 1;
            file_put_contents($set['dir'] . '/baseline.tsv', baselineText($repo, $set, $sha, $inv, $revision));
            echo "frozen {$set['name']} at " . substr($sha, 0, 10) . ': ' . count($inv['included']) . ' files, B=' . array_sum(array_column($inv['included'], 'bytes')) . "\n";
            break;

        case 'verify':
            foreach ($sets as $set) {
                $baseline = loadBaseline($set);
                if (!$baseline['frozen']) {
                    echo "verify: {$set['name']} not frozen yet (skipped)\n";
                    continue;
                }
                if (($set['baseline_commit'] ?? '') !== ($baseline['meta']['baseline_commit'] ?? '')) {
                    fail("{$set['name']}: set.json baseline_commit differs from baseline.tsv");
                }
                $inv = inventory($repo, $set, $set['baseline_commit']);
                if ($inv['unmatched'] !== []) {
                    fail("{$set['name']}: unclassified files at the baseline:\n  " . implode("\n  ", $inv['unmatched']));
                }
                // compare the frozen data (commits, rules hash, counts, every row), not the comment text
                $tmp = tempnam(sys_get_temp_dir(), 'lbr');
                file_put_contents($tmp, baselineText($repo, $set, $set['baseline_commit'], $inv, (int) ($baseline['meta']['revision'] ?? 1)));
                $expected = loadBaseline(['dir' => dirname($tmp), 'name' => $set['name']] + $set, basename($tmp));
                unlink($tmp);
                if ($expected['meta'] != $baseline['meta'] || $expected['files'] !== $baseline['files']) {
                    fail("{$set['name']}: baseline.tsv differs from the inventory recomputed from its commit and rules.tsv (the baseline is frozen)");
                }
                echo "verify: {$set['name']} reproduced from " . substr($set['baseline_commit'], 0, 10) . ' (' . count($inv['included']) . " files)\n";
            }
            relocations($dir);
            break;

        case 'perimeter':
            foreach ($sets as $set) {
                if ($set['baseline_commit'] === null) {
                    echo "## {$set['name']}: not frozen (use preview)\n";
                    continue;
                }
                echo "## {$set['name']}\n\n" . perimeterTable(inventory($repo, $set, $set['baseline_commit']), substr($set['baseline_commit'], 0, 10)) . "\n";
            }
            break;

        case 'check':
            $head = tree($repo, 'HEAD');
            foreach ($sets as $set) {
                $baseline = loadBaseline($set);
                if (!$baseline['frozen']) {
                    if (loadRetirements($set) !== []) {
                        fail("{$set['name']}: retirement records exist for a set that is not frozen");
                    }
                    continue;
                }
                [$errors] = checkRetirements($repo, $dir, $set, $baseline, $head);
                if ($errors !== []) {
                    fail("{$set['name']}: invalid retirement records:\n  " . implode("\n  ", $errors));
                }
                echo "check: {$set['name']} retirement records valid\n";
            }
            break;

        case 'report':
            $head = tree($repo, 'HEAD');
            $all = array_map(fn ($s) => setReport($repo, $dir, $s, $head), $sets);
            if (isset($opts['json'])) {
                echo json_encode(isset($opts['set']) ? $all[0] : $all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
                break;
            }
            echo "# Legacy Bytes Remanufacturing\n\nEach set is an independent baseline; their percentages are never combined.\n\n";
            foreach ($all as $d) {
                echo markdown($d) . "\n";
            }
            break;

        default:
            fwrite(STDERR, "usage: php dev/lbr/lbr.php preview|freeze|verify|perimeter|check|report [--set=S] [--at=REV] [--json]\n");
            exit(2);
    }
} catch (RuntimeException $e) {
    fail($e->getMessage());
}
