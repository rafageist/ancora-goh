<?php
/**
 * Regression tests for the SQL emitted by the cogePHP generator (issue #35).
 *
 *   php dev/tests/codegen/run.php
 *
 * CLI only, no database. It checks:
 *   1. a static scan of every tracked generated file: SQL text carries no
 *      interpolated value; only the documented trusted fragments ($where,
 *      $set, $extrawhere, $phrasepart, the checked $fieldcount) remain;
 *   2. the generated models, items, collections, function wrappers and the
 *      service class, run against a recording database: injection probes are
 *      bound parameters and never reach the SQL text;
 *   3. strict value conversion (null, 0, "0", "", booleans) and the checks on
 *      select lists and identifiers;
 *   4. the generators themselves: the service generator refuses SQL with a
 *      "$" it cannot bind, and the set/del handlers no longer use the "null"
 *      string placeholder.
 * The real PostgreSQL behaviour is covered by dev/tests/codegen/pg-probes.sh.
 * Exit status 0 = all tests passed.
 */

$ROOT = realpath ( dirname ( __FILE__ ) . '/../../..' );
chdir ( $ROOT . '/www' ); // the application resolves "../..." paths from www/

$WARNINGS = array ();
set_error_handler ( function ($no, $str, $file, $line) {
	global $WARNINGS;
	$WARNINGS [] = "$str (" . basename ( $file ) . ":$line)";
	return true;
} );

include $ROOT . '/lib/import.php';
include_once $ROOT . '/lib/basic/string/StringUtils.php'; // getReplica()
import ( "lib.basic.security.PasswordUtil" );
import ( "model.data.PartModel" );
foreach ( array ('ProfesorModel', 'Profesor', 'ProfesorCollection', 'ActividadModel', 'ActividadCollection', 'Actividad', 'LugarModel', 'Lugar', 'PlanificacionModel', 'Planificacion', 'PublicFunctions' ) as $c )
	import ( "model.data.$c" );
import ( "model.data.services.PersonsService" );

/** Records every query; parameterized ones answer $result (or false). */
class RecordingDatabase {
	public $calls = array ();
	public $result = array ();
	public $fail = false;
	private function rec($m, $sql, $params) {
		$this->calls [] = array ('method' => $m, 'sql' => $sql, 'params' => $params === null ? null : array_values ( $params ) );
	}
	public function getDefault($sql, $debug = false) { $this->rec ( 'getDefault', $sql, null ); return false; }
	public function getAsObjects($sql) { $this->rec ( 'getAsObjects', $sql, null ); return $this->result; }
	public function getAsArray($sql) { $this->rec ( 'getAsArray', $sql, null ); return $this->result; }
	public function queryParams($sql, array $p) { $this->rec ( 'queryParams', $sql, $p ); return ! $this->fail; }
	public function getAsObjectsParams($sql, array $p) { $this->rec ( 'getAsObjectsParams', $sql, $p ); return $this->fail ? false : $this->result; }
	public function getAsArrayParams($sql, array $p) { $this->rec ( 'getAsArrayParams', $sql, $p ); return $this->fail ? false : $this->result; }
	public function last() { return end ( $this->calls ); }
	public function raw() {
		return array_values ( array_filter ( $this->calls, function ($c) { return $c ['params'] === null; } ) );
	}
}

// ---------------------------------------------------------------- harness
$PASS = 0;
$FAIL = array ();
$CURRENT = '';
function check($cond, $msg) {
	global $PASS, $FAIL, $CURRENT;
	if ($cond) $PASS ++;
	else $FAIL [] = "$CURRENT: $msg";
}
function test($name, $fn) {
	global $CURRENT, $WARNINGS, $dbconnection;
	$CURRENT = $name;
	$WARNINGS = array ();
	$dbconnection = new RecordingDatabase ();
	try {
		$fn ( $dbconnection );
	} catch ( Throwable $e ) {
		check ( false, 'exception ' . get_class ( $e ) . ': ' . $e->getMessage () . ' at ' . basename ( $e->getFile () ) . ':' . $e->getLine () );
	}
	check ( count ( $WARNINGS ) == 0, 'unexpected PHP warnings/notices: ' . implode ( ' | ', $WARNINGS ) );
}
function throws($fn, $class = 'InvalidArgumentException') {
	try {
		$fn ();
	} catch ( Throwable $e ) {
		return $e instanceof $class;
	}
	return false;
}
function no_probe_in_sql($db, $probe) {
	foreach ( $db->calls as $c )
		if (strpos ( $c ['sql'], $probe ) !== false) return false;
	return true;
}

$PROBE = "x' OR '1'='1'; DROP TABLE profesor; --";

// ================================================= 1. static scan
/**
 * Variables whose value reaches the text of a SQL statement: variables
 * interpolated in a double-quoted string that holds SQL, and variables
 * concatenated directly to such a string. Returns "file:line $name" items.
 */
function sql_text_variables($code, $label) {
	$found = array ();
	$tokens = token_get_all ( $code );
	$stmt = array ();
	$flush = function () use (&$stmt, &$found, $label) {
		$text = '';
		foreach ( $stmt as $t ) $text .= is_array ( $t ) ? $t [1] : $t;
		if (preg_match ( '/\b(SELECT|INSERT|UPDATE|DELETE)\b|->(setBy|set|getAllBy|removeBy|getLimitBy|Load|getJoinWith\w*By)\s*\(/i', $text )) {
			$instr = false; $prev = null;
			foreach ( $stmt as $t ) {
				if ($t === '"') { $instr = ! $instr; $prev = $t; continue; }
				if (is_array ( $t ) && $t [0] === T_VARIABLE && $t [1] !== '$this') {
					$concat = $prev === '.' ;
					if ($instr || $concat) $found [] = $label . ':' . $t [2] . ' ' . $t [1];
				}
				if (! (is_array ( $t ) && $t [0] === T_WHITESPACE)) $prev = is_array ( $t ) ? $t [0] : $t;
			}
		}
		$stmt = array ();
	};
	foreach ( $tokens as $t ) {
		$stmt [] = $t;
		if ($t === ';' || $t === '{' || $t === '}') $flush ();
	}
	$flush ();
	return $found;
}

$TRUSTED = array ('$where', '$set', '$extrawhere', '$phrasepart', '$fieldcount' );

test ( 'static: generated files put no value into SQL text', function () {
	global $ROOT, $TRUSTED;
	$files = array ();
	exec ( 'git -C ' . escapeshellarg ( $ROOT ) . ' ls-files model control/data control/view', $files );
	$scanned = 0; $bad = array (); $trusted = 0;
	foreach ( $files as $f ) {
		if (substr ( $f, - 4 ) !== '.php') continue;
		$scanned ++;
		foreach ( sql_text_variables ( file_get_contents ( "$ROOT/$f" ), $f ) as $v ) {
			$name = substr ( $v, strrpos ( $v, ' ' ) + 1 );
			if (in_array ( $name, $TRUSTED, true )) $trusted ++;
			else $bad [] = $v;
		}
	}
	check ( $scanned >= 500, "scanned $scanned generated files" );
	check ( count ( $bad ) == 0, 'values in SQL text: ' . implode ( ', ', array_slice ( $bad, 0, 10 ) ) );
	check ( $trusted > 0, 'the trusted fragments are found by the scan' );
} );

test ( 'static: negative control, the pre-#35 emitted code is detected', function () {
	$old = '<?php $r = $this->getAnything("INSERT INTO profesor (id,nombre) VALUES (\'$id\',".("\'".$nombre."\'").");");' .
		' $this->data->setBy("id = \'".$this->id."\'", "nombre = \'$value\'");' .
		' $sql = "select public.f(\'$a\',$b)";';
	$v = sql_text_variables ( $old, 'old' );
	$names = array_map ( function ($x) { return substr ( $x, strrpos ( $x, ' ' ) + 1 ); }, $v );
	foreach ( array ('$id', '$nombre', '$value', '$a', '$b' ) as $n )
		check ( in_array ( $n, $names, true ), "detects $n" );
} );

test ( 'static: set/del handlers have no "null" string placeholder and skip unposted fields', function () {
	global $ROOT;
	$n = 0;
	foreach ( array_merge ( glob ( "$ROOT/control/data/set/*.php" ), glob ( "$ROOT/control/data/del/*.php" ) ) as $f ) {
		$n ++;
		$code = file_get_contents ( $f );
		check ( strpos ( $code, '= "null";' ) === false, basename ( $f ) . ': no "null" placeholder' );
		if (strpos ( $f, '/set/' ) !== false)
			check ( preg_match_all ( '/^\$obj->set/m', $code ) == 0, basename ( $f ) . ': every setter call is guarded' );
	}
	check ( $n >= 100, "checked $n handlers" );
} );

// ================================================= 2. generated code, recorded
test ( 'model add(): every value is bound, the SQL text is constant', function ($db) {
	global $PROBE;
	$m = new ProfesorModel ( $db );
	$r = $m->add ( $PROBE, 'owner-id' );
	$c = $db->last ();
	check ( $c ['method'] === 'getAsArrayParams', 'parameterized call' );
	check ( $c ['sql'] === 'INSERT INTO profesor (id,nombre,creador) VALUES ($1,$2,$3);', 'constant INSERT' );
	check ( $c ['params'] [1] === $PROBE && $c ['params'] [2] === 'owner-id', 'probe bound unchanged' );
	check ( $c ['params'] [0] === $r ['id'] && strlen ( $r ['id'] ) >= 50, 'generated replica id bound and returned' );
	check ( count ( $db->raw () ) == 0, 'no raw query' );
} );

test ( 'model get()/remove(): the key is bound', function ($db) {
	global $PROBE;
	$m = new ProfesorModel ( $db );
	check ( $m->get ( $PROBE ) === false, 'unknown key returns false' );
	check ( $db->last () ['sql'] === 'SELECT * FROM profesor WHERE id = $1;' && $db->last () ['params'] === array ($PROBE ), 'get binds' );
	$m->remove ( $PROBE );
	check ( $db->last () ['sql'] === 'DELETE FROM profesor WHERE id = $1;' && $db->last () ['params'] === array ($PROBE ), 'remove binds' );
	check ( no_probe_in_sql ( $db, "'1'='1" ), 'probe never in SQL text' );
} );

test ( 'item setters: value and key are bound; md5 setter binds the hash', function ($db) {
	global $PROBE;
	$m = new ProfesorModel ( $db );
	$p = new Profesor ( 'n', 'c', "k'1", false, $m, false );
	$p->setNombre ( $PROBE );
	check ( $db->last () ['sql'] === 'UPDATE profesor SET nombre = $1 WHERE id = $2;', 'constant UPDATE' );
	check ( $db->last () ['params'] === array ($PROBE, "k'1" ), 'value and key bound' );
	check ( count ( $db->raw () ) == 0, 'no raw query' );
} );

test ( 'item FK getter: the collection is loaded with a bound key', function ($db) {
	global $PROBE;
	$m = new ProfesorModel ( $db );
	$p = new Profesor ( 'n', 'c', $PROBE, false, $m, false );
	$p->getActividad ();
	$c = $db->last ();
	check ( $c ['sql'] === 'SELECT * FROM actividad WHERE profesor = $1;' && $c ['params'] === array ($PROBE ), 'getAllBy with $1' );
} );

test ( 'raw-fragment methods: values can be bound with $params; select lists are checked', function ($db) {
	$m = new ProfesorModel ( $db );
	$m->getAllBy ( 'creador = $1 ORDER BY nombre', '*', true, array ("u' --" ) );
	check ( $db->last () ['method'] === 'getAsObjectsParams' && $db->last () ['params'] === array ("u' --" ), 'getAllBy binds $params' );
	$m->removeBy ( 'creador = $1', false, array ('u' ) );
	check ( $db->last () ['sql'] === 'DELETE FROM profesor WHERE creador = $1;', 'removeBy binds' );
	$m->setBy ( 'id = $2', 'nombre = $1', false, array ('n', 'i' ) );
	check ( $db->last () ['sql'] === 'UPDATE profesor SET nombre = $1 WHERE id = $2;' && $db->last () ['params'] === array ('n', 'i' ), 'setBy binds' );
	$m->getLimitBy ( 10, "0", 'creador = $1', 'id, nombre', false, array ('u' ) );
	check ( $db->last () ['sql'] === 'SELECT id, nombre FROM profesor WHERE creador = $1 LIMIT $2 OFFSET $3;' && $db->last () ['params'] === array ('u', '10', '0' ), 'getLimitBy numbers limit/offset after $params' );
	$m->getLimit ( 5, 10 );
	check ( $db->last () ['sql'] === 'SELECT * FROM profesor LIMIT $1 OFFSET $2;' && $db->last () ['params'] === array ('5', '10' ), 'getLimit binds' );
	$n = count ( $db->calls );
	check ( throws ( function () use ($m) { $m->getLimit ( '5; DROP TABLE profesor', 0 ); } ), 'non-numeric limit rejected' );
	check ( throws ( function () use ($m) { $m->getAll ( "id FROM profesor; DROP TABLE profesor; --" ); } ), 'select list with ; rejected' );
	check ( throws ( function () use ($m) { $m->getAll ( "'x' AS y" ); } ), 'select list with a literal rejected' );
	check ( throws ( function () use ($m) { $m->length ( "id) FROM profesor; --" ); } ), 'length() field checked' );
	check ( count ( $db->calls ) == $n, 'nothing was sent for a rejected call' );
	$m->getAll ( '*, upper(nombre) as n' );
	check ( $db->last () ['sql'] === 'SELECT *, upper(nombre) as n FROM profesor;', 'function calls and aliases still allowed' );
	$m->getAllBy ( "creador = 'x'" );
	check ( $db->last () ['method'] === 'getAsArray', 'legacy trusted fragment without $params still runs (hand-written callers)' );
} );

test ( 'join by phrase: words are bound LIKE parameters; columns are qualified', function ($db) {
	global $PROBE;
	$m = new ActividadModel ( $db );
	$m->getJoinWithProfesorByPhrase ( '"exact ' . $PROBE, 'actividad.dia = $1', '*', false, array (3 ) );
	$c = $db->last ();
	check ( strpos ( $c ['sql'], 'FROM actividad INNER JOIN public.profesor ON actividad.profesor = public.profesor.id WHERE (actividad.dia = $1) AND (' ) !== false, 'join and fragment' );
	check ( strpos ( $c ['sql'], 'CAST(public.profesor.nombre AS text) LIKE $' ) !== false, 'qualified columns cast to text' );
	check ( $c ['params'] [0] === 3 && in_array ( '%exact%', $c ['params'], true ) && in_array ( '%' . explode ( ' ', $PROBE ) [0] . '%', $c ['params'], true ), 'words bound' );
	check ( no_probe_in_sql ( $db, "'1'='1" ), 'probe not in SQL' );
	check ( throws ( function () use ($m) { $m->getJoinWithProfesorByPhrase ( 'a', 'true', 'id; DROP TABLE x' ); } ), 'bad field list rejected' );
	check ( throws ( function () use ($m) { $m->getAllWithPhraseIn ( 'profesor; DROP TABLE x', 'nombre', 'a' ); } ), 'bad table name rejected' );
} );

test ( 'function wrappers: arguments are bound', function ($db) {
	global $PROBE;
	$f = new PublicFunctions ( $db );
	$f->DameCantProfesAsignados ( $PROBE );
	check ( $db->last () ['sql'] === 'select  public.dame_cant_profes_asignados($1)' && $db->last () ['params'] === array ($PROBE ), 'one argument bound' );
	$f->MueveOCreaBrigadaDeGrupo ( 'b', $PROBE, true );
	check ( $db->last () ['method'] === 'getAsObjectsParams' && $db->last () ['params'] === array ('b', $PROBE ), 'two arguments bound, objects' );
	check ( count ( $db->raw () ) == 0, 'no raw query' );
} );

test ( 'service class: $name / \'$name\' became placeholders', function ($db) {
	global $PROBE;
	$s = new PersonsService ( $db );
	$s->getPersonsByNameAndAge ( $PROBE, '30' );
	check ( $db->last () ['sql'] === 'SELECT * FROM persons WHERE name = $1 AND age = $2', 'constant SQL' );
	check ( $db->last () ['params'] === array ($PROBE, '30' ), 'bound' );
} );

test ( 'a failed parameterized query throws without values in the message', function ($db) {
	$db->fail = true;
	$m = new ProfesorModel ( $db );
	$msg = '';
	try { $m->add ( 'secret-value', 'c' ); } catch ( RuntimeException $e ) { $msg = $e->getMessage (); }
	check ( $msg !== '' && strpos ( $msg, 'secret-value' ) === false, 'RuntimeException, no value leaked' );
} );

// ================================================= 3. value conversion
test ( 'sqlValue: strict null, 0, "0" and "" semantics', function ($db) {
	check ( PartModel::sqlValue ( null, 'text' ) === null && PartModel::sqlValue ( null, 'number' ) === null && PartModel::sqlValue ( null, 'bool' ) === null, 'only null is NULL' );
	check ( PartModel::sqlValue ( '', 'text' ) === '', '"" text stays ""' );
	check ( PartModel::sqlValue ( 0, 'text' ) === '0' && PartModel::sqlValue ( '0', 'text' ) === '0', '0 text is "0"' );
	check ( PartModel::sqlValue ( 0, 'number' ) === '0' && PartModel::sqlValue ( '0', 'number' ) === '0' && PartModel::sqlValue ( ' 7 ', 'number' ) === '7' && PartModel::sqlValue ( 1.5, 'number' ) === '1.5', 'numbers' );
	foreach ( array ('', 'abc', '1; DROP', '1 OR 1=1', true, array (1 ), INF, NAN ) as $bad )
		check ( throws ( function () use ($bad) { PartModel::sqlValue ( $bad, 'number' ); } ), 'number rejects ' . var_export ( $bad, true ) );
	foreach ( array (true, 1, '1', 't', 'TRUE', 'on', 'yes' ) as $t ) check ( PartModel::sqlValue ( $t, 'bool' ) === 't', 'bool true ' . var_export ( $t, true ) );
	foreach ( array (false, 0, '0', 'f', 'false', 'off', 'no' ) as $f ) check ( PartModel::sqlValue ( $f, 'bool' ) === 'f', 'bool false ' . var_export ( $f, true ) );
	foreach ( array ('', 'abc', 2, '2', array () ) as $bad )
		check ( throws ( function () use ($bad) { PartModel::sqlValue ( $bad, 'bool' ); } ), 'bool rejects ' . var_export ( $bad, true ) );
	foreach ( array (true, false, array ('a' ), new stdClass () ) as $bad )
		check ( throws ( function () use ($bad) { PartModel::sqlValue ( $bad, 'text' ); } ), 'text rejects ' . gettype ( $bad ) );
} );

test ( 'generated add(): typed columns use their kind', function ($db) {
	$m = new PlanificacionModel ( $db );
	$m->add ( 'desc', 0, '30', 'f', 'owner' );
	$p = $db->last () ['params'];
	check ( $p [1] === 'desc' && $p [2] === '0' && $p [3] === '30' && $p [4] === 'f' && $p [5] === 'owner', 'text, bigint, bigint, boolean, text' );
	$m->add ( 'd', null, null, null, 'o' );
	$p = $db->last () ['params'];
	check ( $p [2] === null && $p [3] === null && $p [4] === null, 'null is NULL' );
	// in PHP 8 "f" == true, so the old conversion stored "f" as true
	$m->add ( 'd', 1, 2, 'false', 'o' );
	check ( $db->last () ['params'] [4] === 'f', '"false" is false' );
	$n = count ( $db->calls );
	check ( throws ( function () use ($m) { $m->add ( 'd', '', 1, true, 'o' ); } ), '"" for a bigint column rejected' );
	check ( throws ( function () use ($m) { $m->add ( 'd', 1, 1, 'maybe', 'o' ); } ), 'unknown boolean rejected' );
	check ( count ( $db->calls ) == $n, 'nothing sent' );
	$l = new LugarModel ( $db );
	$l->add ( '', 0, 'o' );
	$p = $db->last () ['params'];
	check ( $p [1] === '' && $p [2] === '0', 'empty text and 0 are values, not NULL' );
} );

test ( 'sqlIdentifier / sqlSelectList', function ($db) {
	check ( PartModel::sqlIdentifier ( 'nombre' ) === 'nombre' && PartModel::sqlIdentifier ( 'seguridad.usuario', true ) === 'seguridad.usuario', 'valid identifiers' );
	foreach ( array ('', '1a', 'a b', 'a;b', 'a.b', '"a"', "a'--" ) as $bad )
		check ( throws ( function () use ($bad) { PartModel::sqlIdentifier ( $bad ); } ), 'identifier rejects ' . var_export ( $bad, true ) );
	check ( PartModel::sqlIdentifier ( 'public.profesor.nombre', true ) === 'public.profesor.nombre', 'schema.table.column' );
	check ( throws ( function () { PartModel::sqlIdentifier ( 'a.b.c.d', true ); } ), 'three dots rejected' );
	foreach ( array ('*', 'id', 'a.id, b.nombre', '*, dame_x(plantilla) as clasificacion' ) as $ok )
		check ( PartModel::sqlSelectList ( $ok ) === $ok, "select list ok: $ok" );
	foreach ( array ('', 'id;', "id, 'x'", 'id -- c', 'id /* c */', 'id, $1', 'id, "x"', null, array () ) as $bad )
		check ( throws ( function () use ($bad) { PartModel::sqlSelectList ( $bad ); } ), 'select list rejects ' . var_export ( $bad, true ) );
} );

// ================================================= 4. the generators
function common_php($code) {
	global $ROOT;
	$out = array ();
	$cmd = 'cd ' . escapeshellarg ( "$ROOT/dev" ) . ' && ' . escapeshellarg ( PHP_BINARY ) . ' -r ' . escapeshellarg ( 'ob_start(); include "cogephp.common.php"; ob_end_clean(); ' . $code ) . ' 2>&1';
	exec ( $cmd, $out, $status );
	return array ($status, implode ( "\n", $out ) );
}

test ( 'service generator: placeholders, and refusal of what it cannot bind', function ($db) {
	$m = function ($sql, $params) {
		return 'var_export(serviceSqlParams((object) array("name" => "m", "params" => ' . var_export ( $params, true ) . ', "SQL" => ' . var_export ( $sql, true ) . ')));';
	};
	list ($s, $out) = common_php ( $m ( "SELECT * FROM t WHERE a = '\$a' AND b = \$b AND c = \$a", array ('a', 'b' ) ) );
	check ( $s === 0 && strpos ( $out, "'SELECT * FROM t WHERE a = \$1 AND b = \$2 AND c = \$1'" ) !== false, 'quoted and unquoted references, repeated parameter' );
	check ( strpos ( $out, 'PartModel::sqlValue($a, "text")' ) !== false, 'bound with sqlValue' );
	foreach ( array (
		"SELECT * FROM t WHERE a = \$x" => 'unknown variable',
		"SELECT * FROM t WHERE a LIKE '%\$a%'" => 'reference inside a literal',
		"SELECT * FROM t WHERE a = '\$a" => 'unbalanced quote',
		"SELECT * FROM t WHERE a = {\$a}" => 'complex interpolation'
	) as $sql => $what ) {
		list ($s, $out) = common_php ( $m ( $sql, array ('a' ) ) );
		check ( $s === 1 && strpos ( $out, '[FAIL] service method m' ) !== false, "refuses: $what" );
	}
} );

test ( 'generator emits the documented helpers in PartModel', function ($db) {
	global $ROOT;
	$code = file_get_contents ( "$ROOT/model/data/PartModel.php" );
	foreach ( array ('getAnythingParams', 'sqlValue', 'sqlIdentifier', 'sqlSelectList' ) as $f )
		check ( strpos ( $code, "function $f(" ) !== false, "PartModel::$f" );
	check ( strpos ( $code, 'explode($this' ) === false && strpos ( $code, '$this-getAnything' ) === false, 'old phrase-search defects gone' );
} );

// ---------------------------------------------------------------- report
restore_error_handler ();
foreach ( $FAIL as $f )
	echo "FAIL $f\n";
echo "codegen SQL tests: $PASS checks passed, " . count ( $FAIL ) . " failed (PHP " . PHP_VERSION . ")\n";
exit ( count ( $FAIL ) == 0 ? 0 : 1 );
