<?php
/**
 * Injection probes through the generated model against real PostgreSQL
 * (#35). Run by dev/tests/codegen/pg-probes.sh on a disposable cluster
 * (PGHOST, PGPORT, PGUSER from the launcher; AGOH_PROBE_DB). It connects
 * with PostgresDatabaseImpl, like the application, and checks that hostile
 * values are stored and compared as data, that the tables survive, and the
 * NULL / 0 / "" semantics on real columns.
 */

$ROOT = realpath ( dirname ( __FILE__ ) . '/../../..' );
chdir ( $ROOT . '/www' );
include $ROOT . '/lib/import.php';
include_once $ROOT . '/lib/basic/string/StringUtils.php';
include_once $ROOT . '/lib/enterprise/gendb/PostgresDatabaseImpl.php';
import ( "lib.basic.security.PasswordUtil" );
import ( "model.data.PartModel" );
foreach ( array ('ProfesorModel', 'Profesor', 'ProfesorCollection', 'ActividadModel', 'Actividad', 'ActividadCollection', 'LugarModel', 'Lugar', 'PlanificacionModel', 'Planificacion', 'PublicFunctions' ) as $c )
	import ( "model.data.$c" );

$PASS = 0;
$FAIL = 0;
function check($cond, $msg) {
	global $PASS, $FAIL;
	echo ($cond ? 'PASS  ' : 'FAIL  ') . $msg . "\n";
	if ($cond) $PASS ++; else $FAIL ++;
}
function throws($fn) {
	try { $fn (); } catch ( InvalidArgumentException $e ) { return true; }
	return false;
}

$db = new PostgresDatabaseImpl ();
check ( $db->Connect ( getenv ( 'AGOH_PROBE_DB' ), getenv ( 'PGUSER' ), '', getenv ( 'PGHOST' ), getenv ( 'PGPORT' ) ), 'connected to the disposable cluster' );
$dbconnection = $db;
$owner = 'admin-superuser'; // seeded by dev/sql/datos/datos_por_defecto.sql
$count = function ($table) use ($db) {
	$r = $db->getAsObjects ( "SELECT count(*) AS n FROM $table" );
	return ( int ) $r [0]->n;
};

$probes = array (
	"x'); DROP TABLE profesor; --",
	"' OR '1'='1",
	"O'Brien \\ \$1 -- /* */ ;",
	"Robert'); DELETE FROM seguridad.usuario; --",
	"ñandú \u{1F600} \"quoted\""
);

// add(): stored exactly, tables intact
$m = new ProfesorModel ( $db );
$before = $count ( 'profesor' );
$ids = array ();
foreach ( $probes as $p ) {
	$r = $m->add ( $p, $owner );
	$ids [$r ['id']] = $p;
}
check ( $count ( 'profesor' ) == $before + count ( $probes ), 'add(): one row per probe, table still exists' );
check ( $count ( 'seguridad.usuario' ) >= 1, 'seguridad.usuario untouched' );
$ok = true;
foreach ( $ids as $id => $p ) {
	$row = $m->get ( $id );
	$ok = $ok && $row && $row->nombre === $p && $row->creador === $owner;
}
check ( $ok, 'get(): every probe reads back byte for byte' );

// get()/remove() with hostile keys match nothing
check ( $m->get ( "' OR '1'='1" ) === false, "get(\"' OR '1'='1\") finds nothing" );
$n = $count ( 'profesor' );
$m->remove ( "' OR '1'='1" );
$m->remove ( "x' OR id <> '" );
check ( $count ( 'profesor' ) == $n, 'remove() with hostile keys deletes nothing' );

// setters and FK getters
$id = array_key_first ( $ids );
$p = new Profesor ( '', '', $id, false, $m );
$p->setNombre ( "renamed'; UPDATE profesor SET nombre = 'pwned'; --" );
$r = $db->getAsObjects ( "SELECT count(*) AS n FROM profesor WHERE nombre = 'pwned'" );
check ( ( int ) $r [0]->n == 0 && $m->get ( $id )->nombre === "renamed'; UPDATE profesor SET nombre = 'pwned'; --", 'setter stores the probe and changes one row only' );
$col = $p->getActividad ();
check ( is_array ( $col->collection ) ? count ( $col->collection ) == 0 : $col->collection === null, 'FK getter with a bound key runs (no activities)' );

// raw-fragment API with bound $params
$rows = $m->getAllBy ( 'creador = $1 AND nombre = $2', '*', true, array ($owner, "' OR '1'='1" ) );
check ( is_array ( $rows ) && count ( $rows ) == 1 && $rows [0]->nombre === "' OR '1'='1", 'getAllBy with $params matches the literal probe only' );
$lim = $m->getLimitBy ( 2, 0, 'creador = $1', 'id', false, array ($owner ) );
check ( is_array ( $lim ) && count ( $lim ) == 2, 'getLimitBy binds limit and offset after $params' );

// NULL / 0 / "" on real columns (lugar: nombre VARCHAR NULL, capacidad INTEGER NULL)
$l = new LugarModel ( $db );
$a = $l->add ( '', 0, $owner ) ['id'];
$b = $l->add ( null, null, $owner ) ['id'];
$ra = $db->getAsObjectsParams ( 'SELECT nombre IS NULL AS nn, nombre, capacidad IS NULL AS cn, capacidad FROM lugar WHERE id = $1', array ($a ) ) [0];
$rb = $db->getAsObjectsParams ( 'SELECT nombre IS NULL AS nn, capacidad IS NULL AS cn FROM lugar WHERE id = $1', array ($b ) ) [0];
check ( $ra->nn === 'f' && $ra->nombre === '' && $ra->cn === 'f' && $ra->capacidad === '0', '"" and 0 are stored as values' );
check ( $rb->nn === 't' && $rb->cn === 't', 'null is stored as NULL' );
check ( throws ( function () use ($l, $owner) { $l->add ( 'x', '', $owner ); } ), '"" for an integer column is rejected before PostgreSQL' );
check ( throws ( function () use ($l, $owner) { $l->add ( 'x', '1; DROP TABLE lugar', $owner ); } ), 'a non-numeric integer is rejected' );

// booleans (planificacion.activa BOOLEAN, fecha_inicial/cant_dias BIGINT)
$pm = new PlanificacionModel ( $db );
$t = $pm->add ( 'plan t', 1, 2, 'on', $owner ) ['id'];
$f = $pm->add ( 'plan f', 1, 2, 'f', $owner ) ['id'];
$q = function ($id) use ($db) {
	return $db->getAsObjectsParams ( 'SELECT activa FROM planificacion WHERE id = $1', array ($id ) ) [0]->activa;
};
check ( $q ( $t ) === 't' && $q ( $f ) === 'f', 'booleans: "on" is true, "f" is false (PHP 8 "f" == true was stored as true)' );
check ( $pm->get ( $f )->activa === false, 'repairBoolean still maps "f" to false' );

// function wrappers
$fn = new PublicFunctions ( $db );
$r = $fn->DameCantProfesAsignados ( "x' OR '1'='1" );
check ( is_array ( $r ) && count ( $r ) == 1, 'function wrapper with a hostile argument returns one row' );

// select-list check on the real database
check ( throws ( function () use ($m) { $m->getAll ( 'id FROM profesor; DROP TABLE profesor; --' ); } ) && $count ( 'profesor' ) > 0, 'select list injection rejected, table intact' );

echo "pg probes: $PASS passed, $FAIL failed\n";
exit ( $FAIL == 0 ? 0 : 1 );
