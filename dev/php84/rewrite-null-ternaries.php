<?php
/**
 * One-off mechanical rewrite used for issue #13 (PHP 8 compatibility).
 *
 * The generated models' add() methods contain expressions such as
 *
 *     (descripcion==null?'null':"'".$descripcion."'")     (quoted column)
 *     (dia==null?'null':$dia)                             (unquoted column)
 *
 * where the left operand is a bare identifier (a missing "$"). PHP 5 and 7
 * evaluated the undefined constant as the string 'descripcion', so the
 * comparison was always false and the expression always produced the
 * else-branch. PHP 8 throws Error ("Undefined constant") instead.
 *
 * To keep the historical runtime behaviour byte-for-byte, each expression
 * is replaced by its else-branch:
 *
 *     ("'".$descripcion."'")
 *     ($dia)
 *
 * Only expressions whose identifier equals the variable name are touched.
 *
 * Usage: php dev/php84/rewrite-null-ternaries.php [--dry-run] FILE...
 */

$dry = false;
$files = array();
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--dry-run') $dry = true; else $files[] = $a;
}

$patterns = array(
    // (name==null?'null':"'".$name."'")  ->  ("'".$name."'")
    '/\(([A-Za-z_][A-Za-z0-9_]*)==null\?\'null\':("\'"\.\$\1\."\'")\)/' => '($2)',
    // (name==null?'null':$name)          ->  ($name)
    '/\(([A-Za-z_][A-Za-z0-9_]*)==null\?\'null\':(\$\1)\)/' => '($2)',
);

$total = 0;
foreach ($files as $f) {
    $src = file_get_contents($f);
    $out = $src;
    $n = 0;
    foreach ($patterns as $re => $rep) {
        $out = preg_replace($re, $rep, $out, -1, $c);
        $n += $c;
    }
    if ($n) {
        echo "$f: $n expression(s)\n";
        $total += $n;
        if (!$dry) file_put_contents($f, $out);
    }
}
fwrite(STDERR, "rewrote $total expression(s)" . ($dry ? " (dry run)" : "") . "\n");
