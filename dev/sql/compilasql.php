<?php
/**
 * Áncora GOH SQL bundler (issue #9).
 *
 * Reads deploy.priority and writes one SQL script with every listed source
 * after its declared dependencies. Replaces the 2010 bundler, which matched
 * dependency names loosely and silently ignored unknown ones.
 *
 *   php compilasql.php [--with-data] [--out FILE]      (default output: stdout)
 *
 * Manifest lines:  type: package.file @ description @ dep, dep, ...
 *   package.file is the path under dev/sql/ with "/" written as "." and no ".sql".
 *   A dependency is a full entry key, the last segment of exactly one key, or
 *   a "prefix*" pattern that matches at least one key.
 *
 * The build fails (exit 1) when a dependency is unknown, ambiguous or matches
 * nothing; when dependencies form a cycle; when an entry has no source file or
 * a maintained source file is not listed; or when an entry is listed twice.
 *
 * Output is deterministic: entries keep manifest order, except that
 * dependencies come first (depth-first, in declared order). UTF-8 BOMs are
 * stripped, every file ends with a newline, and there are no timestamps.
 * Entries in tools/known-broken.txt are wrapped in check_function_bodies = off,
 * exactly as the 2010 pg_dump loaded them. "data:" entries are included only
 * with --with-data.
 */

const SOURCE_EXCLUDES = [
    '#^trash/#',                 // superseded objects
    '#^tools/#',                 // build and check tooling
    '#^tests/#',                 // characterization suite (fixtures and cases)
    '#^generador/trash\.sql$#',  // ad-hoc scratch script, not an object
    '#^result\.sql$#',           // former committed bundle output (now a build artifact)
    '#^tabla\.sql$#',            // former tables-only bundle output
];

function fail(string $msg): never
{
    fwrite(STDERR, "[FAIL] $msg\n");
    exit(1);
}

$opts = getopt('', ['with-data', 'out:']);
$withData = isset($opts['with-data']);
$out = $opts['out'] ?? null;
$dir = __DIR__;

// --- manifest -----------------------------------------------------------
$entries = [];   // key => [type, deps[], line]
foreach (file("$dir/deploy.priority") as $n => $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    $p = strpos($line, ':');
    if ($p === false) {
        fail("deploy.priority:" . ($n + 1) . ": missing 'type:'");
    }
    $type = trim(substr($line, 0, $p));
    $parts = explode('@', substr($line, $p + 1));
    $key = trim($parts[0]);
    $deps = isset($parts[2]) ? array_map('trim', explode(',', $parts[2])) : [];
    if (isset($entries[$key])) {
        fail("deploy.priority:" . ($n + 1) . ": '$key' listed twice");
    }
    $entries[$key] = ['type' => $type, 'deps' => array_values(array_filter($deps, 'strlen')), 'line' => $n + 1];
}

$fileOf = fn(string $key): string => str_replace('.', '/', $key) . '.sql';

// --- every entry has a file, every maintained file has an entry ----------
$listed = [];
foreach ($entries as $key => $e) {
    $f = $fileOf($key);
    if (!is_file("$dir/$f")) {
        fail("deploy.priority:{$e['line']}: '$key' has no file $f");
    }
    $listed[$f] = true;
}
$unlisted = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    $rel = substr($file->getPathname(), strlen($dir) + 1);
    if (!str_ends_with($rel, '.sql') || isset($listed[$rel])) {
        continue;
    }
    foreach (SOURCE_EXCLUDES as $rx) {
        if (preg_match($rx, $rel)) {
            continue 2;
        }
    }
    $unlisted[] = $rel;
}
if ($unlisted) {
    sort($unlisted);
    fail("source files not listed in deploy.priority:\n  " . implode("\n  ", $unlisted));
}

// --- strict dependency resolution ----------------------------------------
$resolve = function (string $dep, string $from) use ($entries): array {
    if (isset($entries[$dep])) {
        return [$dep];
    }
    $hits = [];
    if (str_ends_with($dep, '*')) {
        $prefix = substr($dep, 0, -1);
        foreach (array_keys($entries) as $k) {
            if (str_starts_with($k, $prefix)) {
                $hits[] = $k;
            }
        }
        if (!$hits) {
            fail("'$from' depends on '$dep', which matches no entry");
        }
        return $hits;
    }
    foreach (array_keys($entries) as $k) {
        if (substr($k, strrpos($k, '.') + 1) === $dep) {
            $hits[] = $k;
        }
    }
    if (count($hits) !== 1) {
        fail("'$from' depends on '$dep', which is " . ($hits ? 'ambiguous: ' . implode(', ', $hits) : 'unknown'));
    }
    return $hits;
};
foreach ($entries as $key => &$e) {
    $r = [];
    foreach ($e['deps'] as $d) {
        foreach ($resolve($d, $key) as $k) {
            if ($k !== $key) {
                $r[] = $k;
            }
        }
    }
    $e['deps'] = $r;
}
unset($e);

// --- order -----------------------------------------------------------------
$order = [];
$state = [];    // key => 1 visiting, 2 done
$visit = function (string $key, array $path) use (&$visit, &$order, &$state, $entries): void {
    if (($state[$key] ?? 0) === 2) {
        return;
    }
    if (($state[$key] ?? 0) === 1) {
        fail('dependency cycle: ' . implode(' -> ', [...$path, $key]));
    }
    $state[$key] = 1;
    foreach ($entries[$key]['deps'] as $d) {
        $visit($d, [...$path, $key]);
    }
    $state[$key] = 2;
    $order[] = $key;
};
foreach (array_keys($entries) as $key) {
    $visit($key, []);
}

// --- emit --------------------------------------------------------------------
$known = [];
foreach (file("$dir/tools/known-broken.txt", FILE_IGNORE_NEW_LINES) as $l) {
    if ($l !== '' && $l[0] !== '#') {
        $known[explode("\t", $l)[0]] = true;
    }
}

$sql = "-- Áncora GOH database bundle, generated by dev/sql/compilasql.php from\n"
     . "-- dev/sql/deploy.priority" . ($withData ? ' (with data)' : ' (schema only)') . ". Do not edit; rebuild instead.\n"
     . "-- For an empty database only: many sources start with DROP ... CASCADE.\n\n";
$count = 0;
foreach ($order as $key) {
    if ($entries[$key]['type'] === 'data' && !$withData) {
        continue;
    }
    $f = $fileOf($key);
    $body = file_get_contents("$dir/$f");
    if (str_starts_with($body, "\xEF\xBB\xBF")) {
        $body = substr($body, 3);
    }
    $body = rtrim($body) . "\n";
    $sql .= "-- >>> $key ($f)\n";
    if (isset($known[$f])) {
        $sql .= "-- known-broken (tools/known-broken.txt): loaded without body checks, as in the 2010 dump\n"
              . "SET check_function_bodies = off;\n" . $body . ";\nRESET check_function_bodies;\n";
    } else {
        $sql .= $body;
    }
    $sql .= "-- <<< $key\n\n";
    $count++;
}

if ($out === null) {
    fwrite(STDOUT, $sql);
} elseif (file_put_contents($out, $sql) === false) {
    fail("cannot write $out");
}
fwrite(STDERR, "[INFO] $count entries bundled" . ($out ? " into $out" : '') . "\n");
