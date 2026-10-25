<?php
/**
 * Constructor smoke test (issue #13). No database, no web server.
 *
 * Declares every class of the generated data layer (model/data,
 * model/security, model/generator, model/util) and checks that the
 * renamed constructors really run on this PHP version:
 *   - every *Model / *Functions / *DatabaseViews class (PartModel
 *     subclasses) stores the connection object passed to it in ->db;
 *   - every *Collection class builds its ->data model from that object.
 * Only constructors that touch no database are exercised; the stub
 * connection has no methods, so any query attempt fails loudly.
 *
 * Usage: php dev/ci/php84-constructor-smoke.php   (from the repo root)
 */
error_reporting(E_ALL);
set_error_handler(function ($no, $str, $file, $line) {
    // PHP 8.2+ dynamic-property deprecations are expected in this legacy
    // code and are not what this test is about.
    if ($no === E_DEPRECATED && strpos($str, 'dynamic property') !== false) return true;
    fwrite(STDERR, "PHP error: $str at $file:$line\n");
    exit(1);
});

function import($packages) {}   // declarations only, see autoloader below

$dirs = array('model/data', 'model/data/views', 'model/data/services', 'model/security', 'model/generator', 'model/util');
$map = array();
foreach ($dirs as $d) {
    foreach (glob("$d/*.php") as $f) $map[strtolower(basename($f, '.php'))] = $f;
}
spl_autoload_register(function ($cls) use ($map) {
    $k = strtolower($cls);
    if (isset($map[$k])) {
        ob_start();                 // discard stray output after closing tags
        require_once $map[$k];
        ob_end_clean();
    }
});

final class StubConnection {}
$db = new StubConnection();

$checked = 0;
$fail = 0;
foreach ($map as $key => $file) {
    $src = file_get_contents($file);
    if (!preg_match('/^\s*class\s+(\w+)(\s+extends\s+(\w+))?/mi', $src, $m)) continue;
    $cls = $m[1];
    if (!class_exists($cls)) { echo "FAIL $cls: not declared ($file)\n"; $fail++; continue; }
    $isPart = is_subclass_of($cls, 'PartModel') || $cls === 'PartModel';
    $isCollection = substr($cls, -10) === 'Collection';
    if (!$isPart && !$isCollection) continue;
    $ctor = (new ReflectionClass($cls))->getConstructor();
    if ($ctor === null) { echo "FAIL $cls: no constructor\n"; $fail++; continue; }
    $o = new $cls($db);
    $checked++;
    if ($isPart && $o->db !== $db) { echo "FAIL $cls: constructor did not set ->db\n"; $fail++; }
    if ($isCollection && !(is_object($o->data) && $o->data->db === $db)) { echo "FAIL $cls: constructor did not build ->data\n"; $fail++; }
}
printf("php84-constructor-smoke: %d class(es) instantiated, %d failure(s)\n", $checked, $fail);
exit($fail || !$checked ? 1 : 0);
