<?php
/**
 * PHP 8 compatibility static check for the Áncora GOH legacy code base (#13).
 *
 * Token-based (no code is executed, nothing is included). Fails (exit 1) if
 * any of the following PHP 8 incompatibilities is found in the given files:
 *
 *   php4-ctor   A class method named like its class and no __construct().
 *               Since PHP 8.0 such a method is a plain method: the
 *               constructor silently does not run.
 *   const       A bare identifier used as a value that is not a known PHP
 *               constant nor defined in the scanned files (PHP 5/7 treated
 *               it as the string 'name'; PHP 8 throws Error).
 *   removed-fn  A call to a function removed in PHP 7/8 (each, ereg*,
 *               split, create_function, mysql_*, money_format, ...).
 *   static-call Class::method() where method is a non-static method of a
 *               class declared in the scanned files and the call is not a
 *               parent/self-style call from an instance method of that
 *               class or a subclass (PHP 8: Error).
 *
 * Usage: php dev/ci/php84-static-check.php [--allow=rule:path ...] FILE...
 *        php dev/ci/php84-static-check.php --self-test
 *
 * --allow=rule:path suppresses one rule for a file or directory (prefix).
 * Limits: heuristic, token level only; it does not resolve includes,
 * namespaces or dynamic calls, and it does not prove runtime correctness.
 */

$removed = array(
    'each', 'create_function', 'ereg', 'eregi', 'ereg_replace', 'eregi_replace',
    'split', 'spliti', 'sql_regcase', 'money_format', 'get_magic_quotes_gpc',
    'get_magic_quotes_runtime', 'set_magic_quotes_runtime', 'magic_quotes_runtime',
    'call_user_method', 'call_user_method_array', 'session_register',
    'session_unregister', 'session_is_registered', 'define_syslog_variables',
    'hebrevc', 'convert_cyr_string', 'ezmlm_hash', 'restore_include_path',
    'fgetss', 'gzgetss', 'image2wbmp', 'png2wbmp', 'jpeg2wbmp', 'is_real',
    'mysql_*', 'mcrypt_*', '__autoload',
);

$allow = array();
$sources = array();
$selfTest = false;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--self-test') {
        $selfTest = true;
    } elseif (strncmp($arg, '--allow=', 8) === 0) {
        $allow[] = substr($arg, 8);
    } else {
        $sources[$arg] = file_get_contents($arg);
    }
}
if ($selfTest) {
    // each snippet must yield exactly the listed rules
    $cases = array(
        'ctor.php'  => array("<?php class A { function A() {} }", array('php4-ctor')),
        'ctor2.php' => array("<?php class B { function __construct() {} function b() {} }", array()),
        'ctor3.php' => array("<?php class C extends A { function x() { parent::x(); } }", array()),
        'const.php' => array("<?php \$x = (foo==null?'null':\$foo); echo PHP_EOL, true, E_ALL;", array('const')),
        'const2.php' => array("<?php define('MYC', 1); echo MYC; \$a = \"\$b[key] {\$c['d']}\"; \$o->p; X::Y; new Z; function f(Z \$z, array &\$w) {} try {} catch (E \$e) {}", array()),
        'const3.php' => array("<?php echo \"{\$a[bar]}\";", array('const')),
        'st.php'    => array("<?php class U { function a() {} static function b() {} } class V extends U { function c() { parent::a(); U::a(); self::b(); } static function d() { parent::a(); } } U::a(); U::b(); function g() { V::c(); }", array('static-call', 'static-call', 'static-call')),
        'fn.php'    => array("<?php \$a = split(',', \$b); while (list(\$k) = each(\$a)); mysql_query('x'); \$o->split(1);", array('removed-fn', 'removed-fn', 'removed-fn')),
    );
    $fail = 0;
    foreach ($cases as $name => $c) {
        $got = array();
        foreach (check_sources(array($name => $c[0]), array()) as $e) {
            preg_match('/\[([a-z0-9-]+)\]/', $e, $m);
            $got[] = $m[1];
        }
        if ($got !== $c[1]) {
            $fail++;
            fwrite(STDERR, "self-test $name: expected [" . implode(',', $c[1]) . "] got [" . implode(',', $got) . "]\n");
        }
    }
    fwrite(STDERR, sprintf("php84-static-check self-test: %d case(s), %d failure(s)\n", count($cases), $fail));
    exit($fail ? 1 : 0);
}

function is_allowed($rule, $file, $allow) {
    foreach ($allow as $a) {
        list($r, $p) = explode(':', $a, 2);
        if ($r === $rule && ($file === $p || strncmp($file, rtrim($p, '/') . '/', strlen(rtrim($p, '/')) + 1) === 0)) return true;
    }
    return false;
}

function sig($t, $i, $dir) {
    // next/previous significant token index
    for ($j = $i + $dir; isset($t[$j]); $j += $dir) {
        if (is_array($t[$j]) && in_array($t[$j][0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true)) continue;
        return $j;
    }
    return null;
}

function tid($tok) { return is_array($tok) ? $tok[0] : $tok; }

$errors = check_sources($sources, $allow);
foreach ($errors as $e) fwrite(STDOUT, $e . "\n");
fwrite(STDERR, sprintf("php84-static-check: %d file(s) scanned, %d problem(s)\n", count($sources), count($errors)));
exit($errors ? 1 : 0);

function check_sources($sources, $allow) {
global $removed;
$tokens = array();
$userConsts = array();
$classes = array();      // lc class => ['parent' => lc|null, 'methods' => [lc => isStatic]]
foreach ($sources as $f => $src) {
    $t = token_get_all($src);
    $tokens[$f] = $t;
    collect_classes($t, $classes);
    // collect define('NAME', ...) and const NAME = ...
    foreach ($t as $i => $tok) {
        if (is_array($tok) && $tok[0] === T_STRING && strcasecmp($tok[1], 'define') === 0) {
            $j = sig($t, $i, 1);
            if ($j !== null && $t[$j] === '(') {
                $k = sig($t, $j, 1);
                if ($k !== null && is_array($t[$k]) && $t[$k][0] === T_CONSTANT_ENCAPSED_STRING) {
                    $userConsts[strtolower(substr($t[$k][1], 1, -1))] = true;
                }
            }
        }
        if (is_array($tok) && $tok[0] === T_CONST) {
            $j = sig($t, $i, 1);
            if ($j !== null && is_array($t[$j]) && $t[$j][0] === T_STRING) $userConsts[strtolower($t[$j][1])] = true;
        }
    }
}

$notConstPrev = array(T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_CLASS, T_INTERFACE,
    T_TRAIT, T_NEW, T_EXTENDS, T_IMPLEMENTS, T_CONST, T_USE, T_NAMESPACE, T_INSTANCEOF,
    T_GOTO, T_NS_SEPARATOR, T_INSTEADOF, T_AS);
if (defined('T_NULLSAFE_OBJECT_OPERATOR')) $notConstPrev[] = T_NULLSAFE_OBJECT_OPERATOR;

$errors = array();
foreach ($tokens as $f => $t) {
    $n = count($t);
    $depth = 0;
    $classStack = array();   // each: [name, depth, ctor(bool), legacyLine, lastMethodStatic]
    $inString = false;       // inside "..." or heredoc
    $strCurly = 0;           // nesting of {$...} inside strings
    for ($i = 0; $i < $n; $i++) {
        $tok = $t[$i];
        $id = tid($tok);

        // string interpolation state
        if ($tok === '"') { $inString = !$inString; continue; }
        if ($id === T_START_HEREDOC) { $inString = true; continue; }
        if ($id === T_END_HEREDOC) { $inString = false; continue; }
        if ($id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES) {
            $depth++;
            if ($inString) $strCurly++;
            continue;
        }
        if ($tok === '{') { $depth++; continue; }
        if ($tok === '}') {
            $depth--;
            if ($inString && $strCurly > 0) { $strCurly--; continue; }
            if ($classStack && end($classStack)[1] === $depth) {
                $c = array_pop($classStack);
                if ($c[3] && !$c[2] && !is_allowed('php4-ctor', $f, $allow)) {
                    $errors[] = "$f:{$c[3]}: [php4-ctor] class {$c[0]} has a PHP 4 style constructor {$c[0]}() and no __construct()";
                }
            }
            continue;
        }

        if ($id === T_CLASS) {
            $p = sig($t, $i, -1);
            if ($p !== null && tid($t[$p]) === T_DOUBLE_COLON) continue; // Foo::class
            $j = sig($t, $i, 1);
            if ($j !== null && tid($t[$j]) === T_STRING) {
                $classStack[] = array($t[$j][1], $depth, false, 0, false);
            }
            continue;
        }

        if ($id === T_FUNCTION && $classStack) {
            $c =& $classStack[count($classStack) - 1];
            if ($depth === $c[1] + 1) {
                $j = sig($t, $i, 1);
                if ($j !== null && $t[$j] === '&') $j = sig($t, $j, 1);
                if ($j !== null && tid($t[$j]) === T_STRING) {
                    if (strcasecmp($t[$j][1], '__construct') === 0) $c[2] = true;
                    if (strcasecmp($t[$j][1], $c[0]) === 0) $c[3] = $t[$j][2];
                }
                $c[4] = is_static_decl($t, $i);
            }
            unset($c);
            continue;
        }

        if ($id === T_STRING && !($inString && $strCurly === 0)) {
            $nx = sig($t, $i, 1);
            $p = sig($t, $i, -1);
            if ($nx !== null && tid($t[$nx]) === T_DOUBLE_COLON && !($p !== null && in_array(tid($t[$p]), array(T_NEW, T_NS_SEPARATOR), true))) {
                $mi = sig($t, $nx, 1);
                $pa = $mi === null ? null : sig($t, $mi, 1);
                if ($mi !== null && tid($t[$mi]) === T_STRING && $pa !== null && $t[$pa] === '(') {
                    $cur = $classStack ? end($classStack) : null;
                    $inMethod = $cur && $depth >= $cur[1] + 2;
                    $instanceCtx = $inMethod && empty($cur[4]);
                    $target = strtolower($tok[1]);
                    $curLc = $cur ? strtolower($cur[0]) : null;
                    if ($target === 'self') $target = $curLc;
                    elseif ($target === 'parent') $target = $curLc && isset($classes[$curLc]) ? $classes[$curLc]['parent'] : null;
                    if ($target !== null) {
                        $isStatic = resolve_method($classes, $target, strtolower($t[$mi][1]));
                        $related = $curLc !== null && is_ancestor_or_self($classes, $target, $curLc);
                        if ($isStatic === false && !($related && $instanceCtx) && !is_allowed('static-call', $f, $allow)) {
                            $errors[] = "$f:{$tok[2]}: [static-call] non-static method {$tok[1]}::{$t[$mi][1]}() called statically (PHP 8: Error)";
                        }
                    }
                }
            }
        }

        if ($id !== T_STRING) continue;
        if ($inString && $strCurly === 0) continue; // "$a[key]" offsets
        $name = $tok[1];
        $p = sig($t, $i, -1);
        $nx = sig($t, $i, 1);
        $prevId = $p === null ? null : tid($t[$p]);
        $next = $nx === null ? null : $t[$nx];
        $nextId = tid($next);
        if (in_array($prevId, $notConstPrev, true)) continue;
        if ($next === '(') {
            $lname = strtolower($name);
            foreach ($removed as $r) {
                $hit = substr($r, -1) === '*' ? strncmp($lname, substr($r, 0, -1), strlen($r) - 1) === 0 : $lname === $r;
                if ($hit && !is_allowed('removed-fn', $f, $allow)) {
                    $errors[] = "$f:{$tok[2]}: [removed-fn] call to $name(), removed in PHP 7/8";
                }
            }
            continue;
        }
        if ($nextId === T_DOUBLE_COLON || $nextId === T_VARIABLE || $nextId === T_ELLIPSIS) continue;
        if ($next === '&') { // Foo &$x type hint
            $k = sig($t, $nx, 1);
            if ($k !== null && tid($t[$k]) === T_VARIABLE) continue;
        }
        if (in_array(strtolower($name), array('true', 'false', 'null'), true)) continue;
        if (defined($name) || isset($userConsts[strtolower($name)])) continue;
        if ($next === ':' && ($prevId === ';' || $prevId === '{' || $prevId === '}')) continue; // goto label
        if (!is_allowed('const', $f, $allow)) {
            $errors[] = "$f:{$tok[2]}: [const] undefined constant '$name' (PHP 8: Error)";
        }
    }
}

return $errors;
}

function is_static_decl($t, $fnIndex) {
    // look back over the modifiers of a method declaration
    for ($p = sig($t, $fnIndex, -1); $p !== null; $p = sig($t, $p, -1)) {
        $id = tid($t[$p]);
        if ($id === T_STATIC) return true;
        if (!in_array($id, array(T_PUBLIC, T_PROTECTED, T_PRIVATE, T_FINAL, T_ABSTRACT, T_VAR), true)) return false;
    }
    return false;
}

function collect_classes($t, &$classes) {
    $depth = 0;
    $cls = null;
    $clsDepth = -1;
    $n = count($t);
    for ($i = 0; $i < $n; $i++) {
        $id = tid($t[$i]);
        if ($id === '{' || $id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES) { $depth++; continue; }
        if ($id === '}') { $depth--; if ($cls !== null && $depth === $clsDepth) $cls = null; continue; }
        if ($id === T_CLASS && $cls === null) {
            $p = sig($t, $i, -1);
            if ($p !== null && tid($t[$p]) === T_DOUBLE_COLON) continue;
            $j = sig($t, $i, 1);
            if ($j === null || tid($t[$j]) !== T_STRING) continue;
            $cls = strtolower($t[$j][1]);
            $parent = null;
            $k = sig($t, $j, 1);
            if ($k !== null && tid($t[$k]) === T_EXTENDS) {
                $k = sig($t, $k, 1);
                if ($k !== null && tid($t[$k]) === T_STRING) $parent = strtolower($t[$k][1]);
            }
            if (!isset($classes[$cls])) $classes[$cls] = array('parent' => $parent, 'methods' => array());
            $clsDepth = $depth;
            continue;
        }
        if ($id === T_FUNCTION && $cls !== null && $depth === $clsDepth + 1) {
            $j = sig($t, $i, 1);
            if ($j !== null && $t[$j] === '&') $j = sig($t, $j, 1);
            if ($j !== null && tid($t[$j]) === T_STRING) {
                $classes[$cls]['methods'][strtolower($t[$j][1])] = is_static_decl($t, $i);
            }
        }
    }
}

function resolve_method($classes, $cls, $m) {
    // true/false = static/non-static; null = unknown class or method
    for ($guard = 0; $cls !== null && isset($classes[$cls]) && $guard < 50; $guard++) {
        if (isset($classes[$cls]['methods'][$m])) return $classes[$cls]['methods'][$m];
        $cls = $classes[$cls]['parent'];
    }
    return null;
}

function is_ancestor_or_self($classes, $anc, $cls) {
    for ($guard = 0; $cls !== null && $guard < 50; $guard++) {
        if ($cls === $anc) return true;
        $cls = isset($classes[$cls]) ? $classes[$cls]['parent'] : null;
    }
    return false;
}
