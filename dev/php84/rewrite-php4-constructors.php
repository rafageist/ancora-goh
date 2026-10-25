<?php
/**
 * One-off mechanical rewrite used for issue #13 (PHP 8 compatibility).
 *
 * Renames PHP 4 style constructors (a method named like its class, in a
 * class without __construct) to __construct. Token-based: only the method
 * name token is replaced, every other byte of the file is kept. Aborts
 * without writing anything if a legacy constructor is also called
 * explicitly by its old name (->Name(), ::Name()), because then an alias
 * method would be needed to keep behaviour identical.
 *
 * Usage: php dev/php84/rewrite-php4-constructors.php [--dry-run] FILE...
 * Prints one line per renamed constructor.
 */

$dry = false;
$files = array();
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--dry-run') $dry = true; else $files[] = $a;
}

function sig($t, $i, $dir) {
    for ($j = $i + $dir; isset($t[$j]); $j += $dir) {
        if (is_array($t[$j]) && in_array($t[$j][0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true)) continue;
        return $j;
    }
    return null;
}

$plan = array();      // file => [token index => line]
$legacyNames = array();
$calls = array();     // lowercased method names called via -> or ::
$tokensByFile = array();

foreach ($files as $f) {
    $t = token_get_all(file_get_contents($f));
    $tokensByFile[$f] = $t;
    $depth = 0;
    $stack = array(); // [name, depth, hasConstruct, legacyTokenIndex]
    $n = count($t);
    for ($i = 0; $i < $n; $i++) {
        $tok = $t[$i];
        $id = is_array($tok) ? $tok[0] : $tok;
        if ($id === '{' || $id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES) { $depth++; continue; }
        if ($id === '}') {
            $depth--;
            if ($stack && end($stack)[1] === $depth) {
                $c = array_pop($stack);
                if ($c[3] !== null && !$c[2]) {
                    $plan[$f][$c[3]] = array($c[0], $t[$c[3]][2]);
                    $legacyNames[strtolower($c[0])] = $c[0];
                }
            }
            continue;
        }
        if ($id === T_CLASS) {
            $p = sig($t, $i, -1);
            if ($p !== null && is_array($t[$p]) && $t[$p][0] === T_DOUBLE_COLON) continue;
            $j = sig($t, $i, 1);
            if ($j !== null && is_array($t[$j]) && $t[$j][0] === T_STRING) $stack[] = array($t[$j][1], $depth, false, null);
            continue;
        }
        if ($id === T_FUNCTION && $stack) {
            $c =& $stack[count($stack) - 1];
            if ($depth === $c[1] + 1) {
                $j = sig($t, $i, 1);
                if ($j !== null && $t[$j] === '&') $j = sig($t, $j, 1);
                if ($j !== null && is_array($t[$j]) && $t[$j][0] === T_STRING) {
                    if (strcasecmp($t[$j][1], '__construct') === 0) $c[2] = true;
                    if (strcasecmp($t[$j][1], $c[0]) === 0) $c[3] = $j;
                }
            }
            unset($c);
            continue;
        }
        if (($id === T_OBJECT_OPERATOR || $id === T_DOUBLE_COLON)) {
            $j = sig($t, $i, 1);
            $k = $j === null ? null : sig($t, $j, 1);
            if ($j !== null && is_array($t[$j]) && $t[$j][0] === T_STRING && $k !== null && $t[$k] === '(') {
                $calls[strtolower($t[$j][1])][] = "$f:" . $t[$j][2];
            }
        }
    }
}

$conflicts = array_intersect_key($calls, $legacyNames);
if ($conflicts) {
    foreach ($conflicts as $name => $where) fwrite(STDERR, "explicit call to legacy constructor $name at " . implode(', ', $where) . "\n");
    fwrite(STDERR, "aborting: add alias methods by hand for these classes\n");
    exit(2);
}

$count = 0;
foreach ($plan as $f => $items) {
    $t = $tokensByFile[$f];
    foreach ($items as $idx => $info) {
        echo "$f:{$info[1]}: {$info[0]}() -> __construct()\n";
        $t[$idx][1] = '__construct';
        $count++;
    }
    if (!$dry) {
        $out = '';
        foreach ($t as $tok) $out .= is_array($tok) ? $tok[1] : $tok;
        file_put_contents($f, $out);
    }
}
fwrite(STDERR, "renamed $count constructor(s)" . ($dry ? " (dry run)" : "") . "\n");
