<?php

/**
 * Legacy Bytes Remanufacturing (LBR) inventory and report (issue #38).
 *
 * Method: https://github.com/rafageist/blog-reflections/wiki/2026.014.-Legacy-Bytes-Remanufacturing
 *
 *   php dev/lbr/lbr.php freeze    write dev/lbr/baseline.tsv from the baseline commit (once)
 *   php dev/lbr/lbr.php verify    recompute the inventory; it must equal baseline.tsv
 *   php dev/lbr/lbr.php perimeter  included and excluded bytes at the baseline, by reason
 *   php dev/lbr/lbr.php check     validate dev/lbr/retirements.tsv against the baseline and HEAD
 *   php dev/lbr/lbr.php report [--json]   B, C, retired bytes, percentages by stream/component
 *
 * B  = bytes of the included baseline files (frozen).
 * C  = baseline bytes not yet retired with verified evidence.
 * Remaining % = 100 C / B; Completion % = 100 (B - C) / B.
 *
 * Only a "verified" retirement record lowers C. Deleting, moving or renaming
 * a baseline file without a record does not: such files are listed as
 * "missing without retirement" and stay in C. Everything is read from git
 * objects, so the result does not depend on the working tree.
 * Exit status: 0 ok, 1 validation failure, 2 usage error.
 */

declare(strict_types=1);

const BASELINE_COMMIT = 'eca8aa7';   // recovery + containerization merge (#33), pre-remanufacturing
const ORIGIN_COMMIT = 'b42aa03';     // historical import: July 2010 snapshot

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

/** @return array<string, array{blob: string, bytes: int}> path => entry, for regular files */
function tree(string $repo, string $commit): array
{
    $raw = shell_exec('git -C ' . escapeshellarg($repo) . ' ls-tree -r -l -z --full-tree ' . escapeshellarg($commit));
    if (!is_string($raw)) {
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
            continue; // submodules
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

/** @return list<array{pattern: string, re: string, action: string, stream: string, component: string, reason: string, line: int}> */
function rules(string $file): array
{
    $rules = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) as $i => $line) {
        if (trim($line) === '' || $line[0] === '#') {
            continue;
        }
        $c = explode("\t", $line);
        if (count($c) !== 5 || !in_array($c[1], ['include', 'exclude'], true)) {
            fail("rules.tsv line " . ($i + 1) . ": expected 5 tab-separated columns with include|exclude");
        }
        if ($c[1] === 'include' && ($c[2] === '-' || $c[3] === '-')) {
            fail("rules.tsv line " . ($i + 1) . ": an include rule needs a stream and a component");
        }
        $rules[] = ['pattern' => $c[0], 're' => globRegex($c[0]), 'action' => $c[1],
            'stream' => $c[2], 'component' => $c[3], 'reason' => $c[4], 'line' => $i + 1];
    }
    return $rules;
}

/**
 * Classify every file of the baseline commit.
 * @return array{included: array<string, array>, excluded: array<string, array{bytes: int, files: int}>, unmatched: list<string>}
 */
function inventory(string $repo, string $dir): array
{
    $rules = rules("$dir/rules.tsv");
    $base = tree($repo, BASELINE_COMMIT);
    $origin = tree($repo, ORIGIN_COMMIT);
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
        if ($rule['action'] === 'include' && !isset($origin[$path])) {
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

function baselineText(string $repo, string $dir, array $inv): string
{
    $sha = git($repo, ['rev-parse', BASELINE_COMMIT . '^{commit}']);
    $origin = git($repo, ['rev-parse', ORIGIN_COMMIT . '^{commit}']);
    $b = array_sum(array_column($inv['included'], 'bytes'));
    $out = "# LBR frozen baseline (issue #38). Generated by `php dev/lbr/lbr.php freeze`; do not edit.\n"
        . "# revision=1\n"
        . "# baseline_commit=$sha\n"
        . "# origin_commit=$origin\n"
        . '# rules_sha256=' . hash_file('sha256', "$dir/rules.tsv") . "\n"
        . '# files=' . count($inv['included']) . "\n"
        . "# B=$b\n"
        . "# path\tstream\tcomponent\tbytes\tblob\n";
    foreach ($inv['included'] as $path => $e) {
        $out .= "$path\t{$e['stream']}\t{$e['component']}\t{$e['bytes']}\t{$e['blob']}\n";
    }
    return $out;
}

/** @return array{meta: array<string, string>, files: array<string, array>} */
function loadBaseline(string $dir): array
{
    $file = "$dir/baseline.tsv";
    if (!is_file($file)) {
        fail('dev/lbr/baseline.tsv is missing: run `php dev/lbr/lbr.php freeze` once');
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
    return ['meta' => $meta, 'files' => $files];
}

const RETIREMENT_COLUMNS = ['id', 'path', 'status', 'responsibility', 'replacement', 'evidence', 'pr', 'commit', 'reviewed_by'];

/** @return list<array<string, string>> */
function loadRetirements(string $dir): array
{
    $rows = [];
    foreach (file("$dir/retirements.tsv", FILE_IGNORE_NEW_LINES) as $i => $line) {
        if (trim($line) === '' || $line[0] === '#') {
            continue;
        }
        $c = explode("\t", $line);
        if (count($c) !== count(RETIREMENT_COLUMNS)) {
            fail('retirements.tsv line ' . ($i + 1) . ': expected ' . count(RETIREMENT_COLUMNS) . ' tab-separated columns');
        }
        $rows[] = array_combine(RETIREMENT_COLUMNS, $c) + ['line' => (string) ($i + 1)];
    }
    return $rows;
}

/** Baseline paths covered by a record: one file, or every file under "dir/". */
function covered(array $files, string $path): array
{
    if (str_ends_with($path, '/')) {
        return array_values(array_filter(array_keys($files), fn ($p) => str_starts_with($p, $path)));
    }
    return isset($files[$path]) ? [$path] : [];
}

/**
 * Validate the retirement records. Returns [errors, retired path => record id].
 * @return array{0: list<string>, 1: array<string, string>, 2: array<string, string>}
 */
function checkRetirements(string $repo, array $baseline, array $records): array
{
    $files = $baseline['files'];
    $head = tree($repo, 'HEAD');
    $headBlobs = [];
    foreach ($head as $p => $e) {
        $headBlobs[$e['blob']][] = $p;
    }
    $errors = [];
    $retired = [];
    $transition = [];
    $ids = [];
    foreach ($records as $r) {
        $at = "retirements.tsv line {$r['line']} ({$r['id']})";
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
            if (isset($head[$p])) {
                $errors[] = "$at: $p is still present at HEAD; the old implementation must be gone";
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

$cmd = $argv[1] ?? 'help';
try {
    switch ($cmd) {
        case 'freeze':
            if (is_file("$dir/baseline.tsv") && !in_array('--force-revision', $argv, true)) {
                fail('baseline.tsv already exists; the baseline is frozen (a revision needs --force-revision and a documented reason)');
            }
            $inv = inventory($repo, $dir);
            if ($inv['unmatched'] !== []) {
                fail("unclassified files at the baseline:\n  " . implode("\n  ", $inv['unmatched']));
            }
            file_put_contents("$dir/baseline.tsv", baselineText($repo, $dir, $inv));
            echo 'frozen ' . count($inv['included']) . ' files, B=' . array_sum(array_column($inv['included'], 'bytes')) . "\n";
            break;

        case 'verify':
            $inv = inventory($repo, $dir);
            if ($inv['unmatched'] !== []) {
                fail("unclassified files at the baseline:\n  " . implode("\n  ", $inv['unmatched']));
            }
            if (baselineText($repo, $dir, $inv) !== file_get_contents("$dir/baseline.tsv")) {
                fail('baseline.tsv differs from the inventory recomputed from ' . BASELINE_COMMIT . ' and rules.tsv (the baseline is frozen)');
            }
            echo 'verify: baseline reproduced from ' . BASELINE_COMMIT . ' (' . count($inv['included']) . " files)\n";
            break;

        case 'perimeter':
            $inv = inventory($repo, $dir);
            $inc = $inv['included'];
            echo "| Perimeter at " . BASELINE_COMMIT . " | Files | Bytes |\n| --- | ---: | ---: |\n";
            echo '| **included (B)** | ' . count($inc) . ' | ' . number_format(array_sum(array_column($inc, 'bytes'))) . " |\n";
            foreach ($inv['excluded'] as $reason => $a) {
                echo "| excluded: $reason | {$a['files']} | " . number_format($a['bytes']) . " |\n";
            }
            break;

        case 'check':
            [$errors] = checkRetirements($repo, loadBaseline($dir), loadRetirements($dir));
            if ($errors !== []) {
                fail("invalid retirement records:\n  " . implode("\n  ", $errors));
            }
            echo "check: retirement records valid\n";
            break;

        case 'report':
            $baseline = loadBaseline($dir);
            [$errors, $retired, $transition] = checkRetirements($repo, $baseline, loadRetirements($dir));
            $head = tree($repo, 'HEAD');
            $B = 0;
            $C = 0;
            $byStream = [];
            $byComponent = [];
            $missing = [];
            $transitionBytes = 0;
            foreach ($baseline['files'] as $p => $e) {
                $B += $e['bytes'];
                $isRetired = isset($retired[$p]) && $errors === [];
                foreach ([[&$byStream, $e['stream']], [&$byComponent, $e['stream'] . ' / ' . $e['component']]] as [&$agg, $key]) {
                    $agg[$key] ??= ['B' => 0, 'C' => 0, 'files' => 0];
                    $agg[$key]['B'] += $e['bytes'];
                    $agg[$key]['files']++;
                    if (!$isRetired) {
                        $agg[$key]['C'] += $e['bytes'];
                    }
                }
                unset($agg);
                if (!$isRetired) {
                    $C += $e['bytes'];
                    if (!isset($head[$p])) {
                        $missing[] = $p;
                    }
                }
                if (isset($transition[$p])) {
                    $transitionBytes += $e['bytes'];
                }
            }
            ksort($byStream, SORT_STRING);
            ksort($byComponent, SORT_STRING);
            $data = ['baseline_commit' => $baseline['meta']['baseline_commit'] ?? '', 'revision' => $baseline['meta']['revision'] ?? '',
                'B' => $B, 'C' => $C, 'retired' => $B - $C, 'remaining_pct' => pct($C, $B), 'completion_pct' => pct($B - $C, $B),
                'in_transition_bytes' => $transitionBytes, 'streams' => $byStream, 'components' => $byComponent,
                'missing_without_retirement' => $missing, 'record_errors' => $errors];
            if (in_array('--json', $argv, true)) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
                break;
            }
            $row = fn ($name, $a) => sprintf("| %s | %d | %s | %s | %s | %s |\n", $name, $a['files'], number_format($a['B']),
                number_format($a['C']), number_format($a['B'] - $a['C']), pct($a['B'] - $a['C'], $a['B']));
            echo "## Legacy Bytes Remanufacturing\n\n";
            echo "Baseline `" . substr($data['baseline_commit'], 0, 7) . "` (revision {$data['revision']}). Progress metric only: it does not measure security, quality or technical debt.\n\n";
            echo "| | Bytes |\n| --- | ---: |\n";
            echo '| B (baseline) | ' . number_format($B) . " |\n| C (remaining) | " . number_format($C) . " |\n| Retired (B − C) | " . number_format($B - $C) . " |\n";
            echo "| Remaining | {$data['remaining_pct']} % |\n| Completion | {$data['completion_pct']} % |\n";
            echo '| In transition (still counted in C) | ' . number_format($transitionBytes) . " |\n\n";
            echo "| Stream | Files | B | C | Retired | Completion % |\n| --- | ---: | ---: | ---: | ---: | ---: |\n";
            foreach ($byStream as $k => $a) {
                echo $row($k, $a);
            }
            echo "\n<details><summary>By component</summary>\n\n| Component | Files | B | C | Retired | Completion % |\n| --- | ---: | ---: | ---: | ---: | ---: |\n";
            foreach ($byComponent as $k => $a) {
                echo $row($k, $a);
            }
            echo "\n</details>\n";
            if ($missing !== []) {
                echo "\n**" . count($missing) . " baseline file(s) are gone from HEAD without a verified retirement record; they stay in C:**\n\n";
                foreach ($missing as $p) {
                    echo "- `$p`\n";
                }
            }
            if ($errors !== []) {
                echo "\n**Invalid retirement records (nothing is credited until fixed):**\n\n";
                foreach ($errors as $e) {
                    echo "- $e\n";
                }
            }
            break;

        default:
            fwrite(STDERR, "usage: php dev/lbr/lbr.php freeze|verify|check|report [--json]\n");
            exit(2);
    }
} catch (RuntimeException $e) {
    fail($e->getMessage());
}
