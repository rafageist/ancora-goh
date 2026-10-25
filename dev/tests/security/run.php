<?php
/**
 * Authentication / authorization regression tests for issue #14.
 *
 *   php dev/tests/security/run.php
 *
 * CLI only: no web server and no database. control/Application.php runs
 * against StubDatabase, which records every SQL text and its bound
 * parameters. Handlers are not executed by the dispatcher tests: a
 * subclass records which handler would have been included. Fixture users
 * and passwords are generated here; no historical data is used.
 * Exit status 0 = all tests passed.
 */

ob_start (); // keep output buffered so session_start() works in the CLI

$ROOT = realpath ( dirname ( __FILE__ ) . '/../../..' );
$TMP = sys_get_temp_dir () . '/ancora-security-tests-' . getmypid ();
@mkdir ( $TMP, 0700 );
ini_set ( 'session.save_path', $TMP );

chdir ( $ROOT . '/www' ); // the application resolves "../..." paths from www/

$WARNINGS = array ();
set_error_handler ( function ($no, $str, $file, $line) {
	global $WARNINGS;
	$WARNINGS [] = "$str (" . basename ( $file ) . ":$line)";
	return true;
} );

include $ROOT . '/lib/import.php';
import ( "lib.basic.file.PropertiesFile" );
import ( "model.data.PartModel" );
import ( "control.Application" );
include $ROOT . '/dev/tests/security/StubDatabase.php';

class RecordingApplication extends Application {
	public $dispatched = array ();
	public function dispatchEvent($f) {
		$this->dispatched [] = $f;
		return true;
	}
}

// ---------------------------------------------------------------- harness
$PASS = 0;
$FAIL = array ();
$CURRENT = '';

function check($cond, $msg) {
	global $PASS, $FAIL, $CURRENT;
	if ($cond)
		$PASS ++;
	else
		$FAIL [] = "$CURRENT: $msg";
}

function test($name, $fn) {
	global $CURRENT, $WARNINGS;
	$CURRENT = $name;
	$WARNINGS = array ();
	reset_request ();
	$out = '';
	ob_start ();
	try {
		$fn ();
	} catch ( Throwable $e ) {
		check ( false, 'exception ' . get_class ( $e ) . ': ' . $e->getMessage () . ' at ' . basename ( $e->getFile () ) . ':' . $e->getLine () );
	}
	ob_end_clean ();
	check ( count ( $WARNINGS ) == 0, 'unexpected PHP warnings/notices: ' . implode ( ' | ', $WARNINGS ) );
}

function reset_request() {
	global $dbconnection, $ROOT;
	$_POST = array ();
	$_GET = array ();
	$_SESSION = array ();
	$dbconnection = new StubDatabase ();
}

function new_app($conf = null) {
	global $ROOT;
	$app = new RecordingApplication ();
	$app->security_conf = $conf === null ? $ROOT . '/dev/tests/security/fixtures/security.conf' : $conf;
	return $app;
}

function add_user($id, $nick, $stored, $roles = array ()) {
	global $dbconnection;
	$dbconnection->usuarios [$id] = ( object ) array ('id' => $id, 'nick' => $nick, 'contrasena' => $stored );
	foreach ( $roles as $r )
		$dbconnection->roles_de_usuario [] = array ($id, $r );
}

function grant($rol, $permiso) {
	global $dbconnection;
	$dbconnection->permisos [] = array ($permiso, $rol );
}

function run_app($app, $execute = true) {
	if ($execute)
		$_GET ['execute'] = '';
	ob_start ();
	$app->Run ();
	return ob_get_clean ();
}

function conf_warnings_only() {
	// the fixture has one deliberately non-literal line; drop that warning
	global $WARNINGS;
	$WARNINGS = array_values ( array_filter ( $WARNINGS, function ($w) {
		return strpos ( $w, "PropertiesFile: ignored non-literal value for 'event_permissions[custom_bad_value]'" ) !== 0;
	} ) );
}

// ================================================= 1. anonymous requests
test ( 'anonymous: arbitrary handler is not dispatched', function () {
	$app = new_app ();
	$_POST = array ('app_event_add_Usuario' => 'yes', 'field_Usuario_nick' => 'x' );
	$out = run_app ( $app );
	conf_warnings_only ();
	check ( $app->dispatched === array (), 'no handler dispatched' );
	check ( strpos ( $out, 'message-session-expired' ) !== false, 'session-expired message shown' );
} );

test ( 'anonymous: access=public no longer bypasses authentication', function () {
	foreach ( array ('add_Usuario', 'del_Usuario', 'set_RolDeUsuario', 'get_ui_gallery_Usuario', 'custom_set_Restricciones', 'get_minibrowser_horarios' ) as $ev ) {
		reset_request ();
		$app = new_app ();
		$_POST = array ('app_event_' . $ev => 'yes', 'access' => 'public' );
		run_app ( $app );
		check ( $app->dispatched === array (), "$ev not dispatched with access=public" );
	}
	conf_warnings_only ();
} );

test ( 'anonymous: mixing an allowlisted and a protected event runs nothing', function () {
	$app = new_app ();
	$_POST = array ('app_event_get_browser_publico' => 'yes', 'app_event_del_Usuario' => 'yes', 'access' => 'public' );
	run_app ( $app );
	conf_warnings_only ();
	check ( $app->dispatched === array (), 'nothing dispatched' );
} );

test ( 'anonymous: executeEvents() itself denies protected events', function () {
	$app = new_app ();
	$_POST = array ('app_event_add_Usuario' => 'yes' );
	ob_start ();
	$r = $app->executeEvents ();
	$out = ob_get_clean ();
	conf_warnings_only ();
	check ( $r === false && $app->dispatched === array (), 'denied' );
	check ( strpos ( $out, 'access_denied' ) !== false, 'access denied page shown' );
} );

test ( 'anonymous: each allowlisted public viewer event is dispatched', function () {
	foreach ( Application::$anonymous_events as $ev ) {
		reset_request ();
		$app = new_app ();
		$_POST = array ('app_event_' . $ev => 'yes', 'access' => 'public' );
		run_app ( $app );
		check ( $app->dispatched === array ($ev ), "$ev dispatched" );
	}
	conf_warnings_only ();
	global $ROOT;
	foreach ( Application::$anonymous_events as $ev )
		check ( file_exists ( "$ROOT/control/custom/$ev.php" ), "allowlisted $ev has a handler" );
	check ( count ( Application::$anonymous_events ) == 6, 'allowlist has exactly 6 entries' );
} );

test ( 'anonymous: value "no" disables an event and no event means no dispatch', function () {
	$app = new_app ();
	$_POST = array ('app_event_get_browser_publico' => 'no' );
	run_app ( $app );
	conf_warnings_only ();
	check ( $app->dispatched === array (), 'nothing dispatched' );
} );

// ===================================== 2. authenticated, no permission
test ( 'authorization: user without permission is denied (deny by default)', function () {
	add_user ( 'u-plan', 'planner', PasswordUtil::hash ( 'p' ), array ('planificador' ) );
	grant ( 'planificador', 'planificar' );
	$_SESSION ['user'] = 'u-plan';
	foreach ( array ('add_Usuario', 'del_Usuario', 'get_form_edit_Planificacion', 'custom_set_Restricciones', 'evento_que_no_existe' ) as $ev ) {
		$_POST = array ('app_event_' . $ev => 'yes' );
		$app = new_app ();
		ob_start ();
		$r = $app->executeEvents ();
		$out = ob_get_clean ();
		check ( $r === false && $app->dispatched === array (), "$ev denied" );
		check ( strpos ( $out, 'access_denied' ) !== false, "$ev shows access denied" );
	}
	conf_warnings_only ();
} );

test ( 'authorization: security.conf requires ALL listed permissions', function () {
	add_user ( 'u1', 'u1', 'x', array ('r1' ) );
	grant ( 'r1', 'planificar' );
	grant ( 'r1', 'organizar_estudiantes' );
	$_SESSION ['user'] = 'u1';
	$_POST = array ('app_event_get_form_edit_Especialidad' => 'yes' );
	$app = new_app ();
	ob_start ();
	$app->executeEvents ();
	ob_end_clean ();
	check ( $app->dispatched === array (), '2 of 3 permissions: denied' );

	grant ( 'r1', 'editar_especialidad' );
	$app = new_app ();
	$app->executeEvents ();
	check ( $app->dispatched === array ('get_form_edit_Especialidad' ), '3 of 3 permissions: allowed' );
	conf_warnings_only ();
} );

test ( 'authorization: nonexistent / incorrect permission names deny', function () {
	add_user ( 'u2', 'u2', 'x', array ('r2' ) );
	grant ( 'r2', 'planificar' );
	grant ( 'r2', 'organizar_estudiantes' );
	grant ( 'r2', 'editar_especialidad' );
	$_SESSION ['user'] = 'u2';
	$cases = array (
		'custom_requires_ghost' => 'permission no role has',
		'custom_wrong_case' => 'Planificar vs planificar',
		'custom_bad_value' => 'non-literal config value is ignored',
		'get_form_edit_Planificacion' => 'historical misspelt key does not grant the real event'
	);
	foreach ( $cases as $ev => $why ) {
		$_POST = array ('app_event_' . $ev => 'yes' );
		$app = new_app ();
		ob_start ();
		$app->executeEvents ();
		ob_end_clean ();
		check ( $app->dispatched === array (), "$ev denied ($why)" );
	}
	conf_warnings_only ();
} );

test ( 'authorization: role "todos" spelled differently is not superuser', function () {
	add_user ( 'u3', 'u3', 'x', array ('r3' ) );
	grant ( 'r3', 'TODOS' );
	grant ( 'r3', 'todos ' );
	$_SESSION ['user'] = 'u3';
	$_POST = array ('app_event_add_Usuario' => 'yes' );
	$app = new_app ();
	ob_start ();
	$app->executeEvents ();
	ob_end_clean ();
	check ( $app->dispatched === array (), 'denied' );
	conf_warnings_only ();
} );

test ( 'authorization: empty array() allows any authenticated user, never anonymous', function () {
	add_user ( 'u4', 'u4', 'x', array () );
	$_POST = array ('app_event_custom_authenticated_only' => 'yes' );
	$app = new_app ();
	ob_start ();
	$app->executeEvents ();
	ob_end_clean ();
	check ( $app->dispatched === array (), 'anonymous denied' );
	$_SESSION ['user'] = 'u4';
	$app = new_app ();
	$app->executeEvents ();
	check ( $app->dispatched === array ('custom_authenticated_only' ), 'authenticated allowed' );
	conf_warnings_only ();
} );

test ( 'authorization: superusuario with "todos" runs any existing event; traversal names are refused', function () {
	add_user ( 'u-root', 'root-fixture', 'x', array ('superusuario' ) );
	grant ( 'superusuario', 'todos' );
	$_SESSION ['user'] = 'u-root';
	$_POST = array ('app_event_add_Usuario' => 'yes', 'app_event_get_browser_Planificaciones' => 'yes' );
	$app = new_app ();
	$app->executeEvents ();
	check ( $app->dispatched === array ('add_Usuario', 'get_browser_Planificaciones' ), 'both dispatched in POST order' );

	foreach ( array ('../../bin/adduser', '..%2f..%2fx', 'add_Usuario.php', 'add Usuario', "add_Usuario\0" ) as $bad ) {
		$_POST = array ('app_event_' . $bad => 'yes' );
		$app = new_app ();
		ob_start ();
		$app->executeEvents ();
		ob_end_clean ();
		check ( $app->dispatched === array (), 'refused event name ' . json_encode ( $bad ) );
	}
	conf_warnings_only ();
} );

test ( 'authorization: real dispatcher includes only files inside the event folders', function () {
	$app = new Application ();
	ob_start ();
	$r = $app->dispatchEvent ( 'evento_que_no_existe' );
	ob_end_clean ();
	check ( $r === false, 'unknown event includes nothing' );
} );

test ( 'authorization: permission lookups are parameterized', function () {
	global $dbconnection;
	$_SESSION ['user'] = "x' OR '1'='1";
	$app = new_app ();
	$p = $app->getPermissionsOfCurrentUser ();
	check ( $p === array (), 'no permissions' );
	check ( count ( $dbconnection->rawCalls () ) == 0, 'no interpolated SQL' );
	$c = $dbconnection->callsOf ( 'getAsObjectsParams' );
	check ( isset ( $c [0] ) && $c [0] ['params'] === array ("x' OR '1'='1" ), 'session id bound as parameter' );
} );

test ( 'security.conf: shipped file parses and names real handlers', function () {
	global $ROOT;
	$app = new_app ( $ROOT . '/bin/security.conf' );
	$ep = $app->getEventPermissions ();
	check ( count ( $ep ) == 2, 'two entries' );
	foreach ( $ep as $ev => $perms ) {
		check ( file_exists ( "$ROOT/control/custom/$ev.php" ), "$ev has a handler" );
		check ( is_array ( $perms ) && count ( $perms ) == 3, "$ev lists 3 permissions" );
	}
} );

// ========================================================= 3. login / SQLi
function login($user, $pass) {
	$_POST = array ('btnLogin' => 'Entrar', 'edtUser' => $user, 'edtPass' => $pass );
	$app = new_app ();
	return $app->VerifyLogin ();
}

test ( 'login: valid modern hash logs in with one parameterized query', function () {
	global $dbconnection;
	add_user ( 'id-1', 'fixture', PasswordUtil::hash ( 'correct horse' ) );
	check ( login ( 'fixture', 'correct horse' ) === 'allow', 'allowed' );
	check ( $_SESSION ['user'] === 'id-1' && $_SESSION ['nick'] === 'fixture', 'session set' );
	check ( count ( $dbconnection->rawCalls () ) == 0, 'no interpolated SQL' );
	check ( count ( $dbconnection->callsOf ( 'queryParams' ) ) == 0, 'no rehash for a current hash' );
} );

test ( 'login: SQL injection probes are bound, do not alter the query and fail', function () {
	global $dbconnection;
	$probes = array (
		array ("fixture' --", 'whatever' ),
		array ("' OR '1'='1", "' OR '1'='1" ),
		array ("fixture' OR 'a'='a", 'x' ),
		array ('fixture', "x' OR '1'='1" ),
		array ('fixture', "') OR 1=1 --" ),
		array ("fixture'; UPDATE seguridad.usuario SET contrasena = ''; --", 'x' ),
		array ('fixture\\', 'x' )
	);
	foreach ( $probes as $p ) {
		reset_request ();
		add_user ( 'id-1', 'fixture', PasswordUtil::hash ( 'correct horse' ) );
		$r = login ( $p [0], $p [1] );
		check ( $r === 'access_denied', 'denied: ' . json_encode ( $p ) );
		check ( ! isset ( $_SESSION ['user'] ), 'no session: ' . json_encode ( $p ) );
		$c = $dbconnection->callsOf ( 'getAsObjectsParams' );
		check ( count ( $c ) == 1 && $c [0] ['sql'] === 'SELECT id, nick, contrasena FROM seguridad.usuario WHERE nick = $1', 'constant SQL text' );
		check ( $c [0] ['params'] === array ($p [0] ), 'username passed verbatim as $1' );
		foreach ( $dbconnection->calls as $call )
			check ( strpos ( $call ['sql'], $p [1] ) === false && strpos ( $call ['sql'], $p [0] ) === false, 'input never appears in SQL text' );
		check ( count ( $dbconnection->rawCalls () ) == 0 && count ( $dbconnection->callsOf ( 'queryParams' ) ) == 0, 'no other statement' );
	}
} );

test ( 'login: a row whose nick differs from the input is rejected', function () {
	global $dbconnection;
	// emulate a database/collation that returned another user's row
	add_user ( 'id-1', 'Fixture', PasswordUtil::hash ( 'pw' ) );
	$dbconnection->usuarios ['id-1']->nick = 'fixture ';
	check ( login ( 'fixture', 'pw' ) === 'access_denied', 'denied' );
} );

test ( 'login: unknown user, empty or non-string input are rejected', function () {
	global $dbconnection;
	add_user ( 'id-1', 'fixture', PasswordUtil::hash ( 'pw' ) );
	check ( login ( 'nobody', 'pw' ) === 'access_denied', 'unknown user' );
	reset_request ();
	add_user ( 'id-1', 'fixture', PasswordUtil::hash ( 'pw' ) );
	check ( login ( 'fixture', '' ) === 'access_denied', 'empty password' );
	check ( login ( '', 'pw' ) === 'access_denied', 'empty user' );
	check ( login ( array ('fixture' ), 'pw' ) === 'access_denied', 'array user' );
	check ( login ( 'fixture', array ('pw' ) ) === 'access_denied', 'array password' );
	check ( count ( $dbconnection->calls ) == 0, 'no query for empty/non-string input' );
} );

test ( 'login: duplicate rows for one nick are rejected', function () {
	add_user ( 'id-1', 'fixture', PasswordUtil::hash ( 'pw' ) );
	add_user ( 'id-2', 'fixture', PasswordUtil::hash ( 'pw' ) );
	check ( login ( 'fixture', 'pw' ) === 'access_denied', 'denied' );
} );

// ===================================================== 4. MD5 migration
test ( 'migration: legacy MD5 account logs in once and is rehashed', function () {
	global $dbconnection;
	$legacy = md5 ( 'legacy-fixture-pw' ); // computed, not a historical hash
	add_user ( 'id-L', 'legacy', $legacy );
	check ( login ( 'legacy', 'legacy-fixture-pw' ) === 'allow', 'legacy login allowed' );
	$u = $dbconnection->callsOf ( 'queryParams' );
	check ( count ( $u ) == 1 && $u [0] ['sql'] === 'UPDATE seguridad.usuario SET contrasena = $1 WHERE id = $2 AND contrasena = $3', 'one parameterized UPDATE' );
	check ( $u [0] ['params'] [1] === 'id-L' && $u [0] ['params'] [2] === $legacy, 'UPDATE targets this row and old value' );
	$new = $dbconnection->usuarios ['id-L']->contrasena;
	check ( $new !== $legacy && ! PasswordUtil::isLegacyMd5 ( $new ), 'stored value is no longer MD5' );
	check ( password_verify ( 'legacy-fixture-pw', $new ), 'new value is a password_hash of the password' );
	check ( password_get_info ( $new ) ['algo'] !== null, 'recognised algorithm' );

	// second login uses the modern path only
	$_SESSION = array ();
	$dbconnection->calls = array ();
	check ( login ( 'legacy', 'legacy-fixture-pw' ) === 'allow', 'second login allowed' );
	check ( count ( $dbconnection->callsOf ( 'queryParams' ) ) == 0, 'no second rehash' );
	check ( $dbconnection->usuarios ['id-L']->contrasena === $new, 'hash unchanged' );
} );

test ( 'migration: wrong password on a legacy account fails and keeps the hash', function () {
	global $dbconnection;
	$legacy = md5 ( 'legacy-fixture-pw' );
	add_user ( 'id-L', 'legacy', $legacy );
	foreach ( array ('wrong', 'LEGACY-FIXTURE-PW', 'legacy-fixture-pw ', $legacy, strtoupper ( $legacy ) ) as $bad ) {
		$_SESSION = array ();
		check ( login ( 'legacy', $bad ) === 'access_denied', 'denied: ' . ($bad === $legacy ? '<the stored md5 itself>' : $bad) );
		check ( ! isset ( $_SESSION ['user'] ), 'no session' );
	}
	check ( count ( $dbconnection->callsOf ( 'queryParams' ) ) == 0, 'no UPDATE' );
	check ( $dbconnection->usuarios ['id-L']->contrasena === $legacy, 'hash untouched' );
} );

test ( 'migration: upper-case legacy hex is accepted; failed rehash still logs in, retried next time', function () {
	global $dbconnection;
	add_user ( 'id-U', 'upper', strtoupper ( md5 ( 'pw-upper' ) ) );
	$dbconnection->fail_updates = true;
	check ( login ( 'upper', 'pw-upper' ) === 'allow', 'allowed' );
	check ( PasswordUtil::isLegacyMd5 ( $dbconnection->usuarios ['id-U']->contrasena ), 'still legacy after failed UPDATE' );
	$dbconnection->fail_updates = false;
	$_SESSION = array ();
	check ( login ( 'upper', 'pw-upper' ) === 'allow', 'allowed again' );
	check ( ! PasswordUtil::isLegacyMd5 ( $dbconnection->usuarios ['id-U']->contrasena ), 'migrated on retry' );
} );

test ( 'migration: plain-text or unknown stored values are never accepted', function () {
	add_user ( 'id-P', 'plain', 'plain-fixture' );
	check ( login ( 'plain', 'plain-fixture' ) === 'access_denied', 'plain text stored value rejected' );
	reset_request ();
	add_user ( 'id-S', 'short', substr ( md5 ( 'x' ), 0, 31 ) );
	check ( login ( 'short', 'x' ) === 'access_denied', '31-hex value rejected' );
} );

test ( 'PasswordUtil: verify results', function () {
	$h = PasswordUtil::hash ( 'abc' );
	check ( PasswordUtil::verify ( 'abc', $h ) === PasswordUtil::RESULT_OK, 'modern ok' );
	check ( PasswordUtil::verify ( 'abd', $h ) === PasswordUtil::RESULT_FAIL, 'modern wrong' );
	check ( PasswordUtil::verify ( 'abc', md5 ( 'abc' ) ) === PasswordUtil::RESULT_OK_REHASH, 'legacy ok needs rehash' );
	$weak = password_hash ( 'abc', PASSWORD_BCRYPT, array ('cost' => 4 ) );
	check ( PasswordUtil::verify ( 'abc', $weak ) === PasswordUtil::RESULT_OK_REHASH, 'outdated cost needs rehash' );
	check ( PasswordUtil::verify ( null, $h ) === PasswordUtil::RESULT_FAIL, 'null password' );
	check ( PasswordUtil::verify ( 'abc', null ) === PasswordUtil::RESULT_FAIL, 'null stored' );
	check ( PasswordUtil::hash ( 'abc' ) !== PasswordUtil::hash ( 'abc' ), 'salted' );
} );

// ========================================================= 5. sessions
test ( 'session: strict mode, HttpOnly/SameSite, id regenerated on login, destroyed on logout', function () {
	global $dbconnection;
	check ( SessionUtil::start () === true, 'session started' );
	check ( ini_get ( 'session.use_strict_mode' ) === '1', 'strict mode' );
	check ( ini_get ( 'session.use_only_cookies' ) === '1', 'cookies only' );
	$p = session_get_cookie_params ();
	check ( $p ['httponly'] === true && $p ['samesite'] === 'Lax', 'HttpOnly + SameSite=Lax' );
	check ( $p ['secure'] === false, 'Secure only on HTTPS (CLI is not)' );
	$_SESSION = array ();
	$before = session_id ();
	add_user ( 'id-1', 'fixture', PasswordUtil::hash ( 'pw' ) );
	check ( login ( 'fixture', 'pw' ) === 'allow', 'login' );
	check ( session_id () !== '' && session_id () !== $before, 'session id changed on login' );
	$failed_id = session_id ();
	$_SESSION = array ();
	check ( login ( 'fixture', 'bad' ) === 'access_denied' && session_id () === $failed_id, 'failed login keeps id' );
	$_SESSION ['user'] = 'id-1';
	$_GET = array ('logout' => '' );
	$app = new_app ();
	ob_start ();
	$app->Run ();
	$out = ob_get_clean ();
	check ( session_status () !== PHP_SESSION_ACTIVE && $_SESSION === array (), 'session destroyed on logout' );
	check ( strpos ( $out, 'formauth' ) !== false, 'login form shown after logout' );
	conf_warnings_only ();
	// session_regenerate_id() in the CLI warns that headers cannot be sent; ignore only that
	global $WARNINGS;
	$WARNINGS = array_values ( array_filter ( $WARNINGS, function ($w) {
		return stripos ( $w, 'headers' ) === false;
	} ) );
} );

// ========================================================= 6. templates
test ( 'templates: no eval() token left in views, handlers or the web root', function () {
	global $ROOT;
	$found = array ();
	$it = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $ROOT, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		$path = substr ( $f->getPathname (), strlen ( $ROOT ) + 1 );
		if (! preg_match ( '#^(view|control|www|model|lib/basic|lib/ancora)/.*\.(php|tpl)$#', $path ))
			continue;
		foreach ( token_get_all ( file_get_contents ( $f->getPathname () ) ) as $t )
			if (is_array ( $t ) && $t [0] == T_EVAL)
				$found [] = $path;
	}
	check ( $found === array (), 'eval found in: ' . implode ( ', ', array_unique ( $found ) ) );
} );

function render_gallery($tpl, $vars) {
	extract ( $vars );
	ob_start ();
	include $tpl;
	return ob_get_clean ();
}

test ( 'templates: gallery renders request-derived names as data, never as code', function () {
	global $ROOT, $TMP;
	$probe = $TMP . '/gallery-eval-probe';
	$item = ( object ) array ('id' => 'a1', 'descripcion' => 'Primera' );
	$base = array ('collection' => ( object ) array ('collection' => array ($item ) ), 'offset' => 0, 'title' => '', 'icon' => 'i.png', 'columns' => 3 );
	$tpls = glob ( $ROOT . '/view/gallery/gallery_*.tpl' );
	check ( count ( $tpls ) == 61, '61 gallery templates' );
	foreach ( $tpls as $tpl ) {
		$out = render_gallery ( $tpl, $base + array ('link' => 'index.php?x=1', 'fieldlink' => 'id', 'fielddescrip' => 'descripcion' ) );
		check ( strpos ( $out, "<a href = 'index.php?x=1&amp;id=a1'>" ) !== false, basename ( $tpl ) . ': link built from the property' );
		check ( strpos ( $out, '<br>Primera' ) !== false, basename ( $tpl ) . ': description printed' );

		$payload = 'id."\'>"; touch(' . var_export ( $probe, true ) . '); //';
		$out = render_gallery ( $tpl, $base + array ('link' => 'index.php', 'fieldlink' => $payload, 'fielddescrip' => 'id; touch(' . var_export ( $probe, true ) . ')' ) );
		check ( ! file_exists ( $probe ), basename ( $tpl ) . ': payload not executed' );
		check ( strpos ( $out, '<a ' ) === false, basename ( $tpl ) . ': no link for an invalid field name' );

		$out = render_gallery ( $tpl, $base + array ('link' => "x'><script>", 'fieldlink' => 'id' ) );
		check ( strpos ( $out, "<script>" ) === false, basename ( $tpl ) . ': link text escaped' );
	}
} );

// ============================================ 7. configuration parsing
test ( 'PropertiesFile: array keys keep their full name and values are not executed', function () {
	global $ROOT;
	$cwd = getcwd ();
	$p = new PropertiesFile ( $ROOT . '/dev/tests/security/fixtures/security.conf' );
	check ( isset ( $p->properties ['event_permissions'] ), 'event_permissions (not event_permission)' );
	check ( ! isset ( $p->properties ['event_permission'] ), 'no truncated key' );
	check ( $p->properties ['event_permissions'] ['custom_authenticated_only'] === array (), 'empty array' );
	check ( ! isset ( $p->properties ['event_permissions'] ['custom_bad_value'] ), 'function call value dropped' );
	check ( ! file_exists ( $cwd . '/ancora-propertiesfile-eval-probe' ), 'function in config not executed' );
	check ( $p->event_permissions === $p->properties ['event_permissions'], 'property access still works' );
	conf_warnings_only ();
	$ok = null;
	foreach ( array ('"a$b"', 'phpinfo()', 'PHP_VERSION', '1+1', '`id`', '"x".exec("id")', 'array(1, f())', '"a" /* c */' ) as $bad ) {
		PropertiesFile::parseLiteral ( $bad, $ok );
		check ( $ok === false, 'rejected literal ' . $bad );
	}
	foreach ( array ('"localhost"' => 'localhost', '5432' => 5432, "'it\\'s'" => "it's", 'true' => true, "array('a', 'b')" => array ('a', 'b' ), "['k' => -1.5]" => array ('k' => - 1.5 ) ) as $good => $want ) {
		$v = PropertiesFile::parseLiteral ( $good, $ok );
		check ( $ok === true && $v === $want, 'parsed literal ' . $good );
	}
} );

// ===================================== 8. user creation and public viewer
test ( 'adduser: stores password_hash() with bound parameters', function () {
	global $ROOT, $dbconnection;
	include_once $ROOT . '/bin/adduser.php';
	check ( ancora_adduser_insert ( $dbconnection, 'N', 'I', 'c@example.invalid', "new'user", 'nuevo-pw' ) === true, 'inserted' );
	$c = $dbconnection->callsOf ( 'queryParams' );
	check ( count ( $c ) == 1 && strpos ( $c [0] ['sql'], 'VALUES ($1, $2, $3, $4, $5)' ) !== false, 'parameterized INSERT' );
	check ( $c [0] ['params'] [3] === "new'user", 'nick bound verbatim' );
	check ( password_verify ( 'nuevo-pw', $c [0] ['params'] [4] ) && $c [0] ['params'] [4] !== md5 ( 'nuevo-pw' ), 'password_hash, not md5' );
	check ( ancora_adduser_insert ( $dbconnection, '', '', '', 'x', '' ) === false, 'empty password refused' );
} );

test ( 'model: UsuarioModel::add and Usuario::setContrasena no longer store md5/plain text', function () {
	global $ROOT, $dbconnection;
	if (! function_exists ( 'getReplica' )) {
		function getReplica($t) {
			return 'fixture-id';
		}
	}
	$m = new UsuarioModel ( $dbconnection );
	$m->add ( 'N', 'I', 'C', 0, 'nick', 'pw-model' );
	$sql = $dbconnection->calls [0] ['sql'];
	check ( strpos ( $sql, md5 ( 'pw-model' ) ) === false && strpos ( $sql, 'pw-model' ) === false, 'no md5/plain password in INSERT' );
	check ( preg_match ( "/'(\\$2y\\$[^']+)'\\);$/", $sql, $mm ) === 1 && password_verify ( 'pw-model', $mm [1] ), 'password_hash in INSERT' );

	$dbconnection->calls = array ();
	$u = new Usuario ( 'N', 'I', 'C', 0, 'nick', 'x', 'fixture-id', false, $m, false );
	$u->setContrasena ( 'pw-set' );
	$sql = $dbconnection->calls [0] ['sql'];
	check ( strpos ( $sql, 'pw-set' ) === false && password_verify ( 'pw-set', $u->contrasena ), 'setter hashes' );
	$dbconnection->calls = array ();
	check ( $u->setContrasena ( '' ) === false && count ( $dbconnection->calls ) == 0, 'empty password not stored' );
} );

test ( 'public viewer: get_minibrowser_publico binds request values', function () {
	global $ROOT, $dbconnection;
	// the template declares a function, so the handler can run only once
	// per process: post all four selectors at once (each one is queried)
	$probe = "x' OR '1'='1";
	$per = "p1'; DROP TABLE actividad; --";
	// the stub matches ids exactly, like a bound parameter would
	$dbconnection->tables ['periodo'] = array (( object ) array ('id' => $per, 'cant_dias' => 1, 'cant_turnos' => 1 ) );
	$_POST = array ('idbrg' => $probe, 'idprofe' => $probe, 'idlug' => $probe, 'idres' => $probe, 'idperiodo' => $per );
	$app = new Application ();
	ob_start ();
	$app->dispatchEvent ( 'get_minibrowser_publico' );
	ob_end_clean ();
	check ( count ( $dbconnection->rawCalls () ) == 0, 'no interpolated SQL' );
	$c = $dbconnection->callsOf ( 'getAsObjectsParams' );
	check ( count ( $c ) == 5, 'four activity queries and one period query, all parameterized' );
	for ($i = 0; $i < 4; $i ++) {
		check ( $c [$i] ['params'] === array ($probe, $per ), "query $i: values bound" );
		check ( strpos ( $c [$i] ['sql'], "'1'='1" ) === false && strpos ( $c [$i] ['sql'], 'DROP' ) === false && strpos ( $c [$i] ['sql'], 'FROM actividad WHERE' ) !== false, "query $i: SQL text constant" );
	}
	check ( $c [4] ['sql'] === 'SELECT * FROM periodo WHERE id = $1;' && $c [4] ['params'] === array ($per ), 'period id bound' );
} );

// ---------------------------------------------------------------- report
restore_error_handler ();
if (session_status () === PHP_SESSION_ACTIVE)
	session_destroy ();
foreach ( glob ( $TMP . '/*' ) as $f )
	@unlink ( $f );
@rmdir ( $TMP );
ob_end_flush ();

foreach ( $FAIL as $f )
	echo "FAIL $f\n";
echo "security tests: $PASS checks passed, " . count ( $FAIL ) . " failed (PHP " . PHP_VERSION . ")\n";
exit ( count ( $FAIL ) == 0 ? 0 : 1 );
