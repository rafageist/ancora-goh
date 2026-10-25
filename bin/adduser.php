<?php

/**
 * Add a user to seguridad.usuario (run from the bin/ directory):
 *
 *   php adduser.php -nick <nick> [-nombre <name>] [-info <info>] [-correo <mail>] [-pass <password>]
 *
 * If -pass is omitted the password is read from standard input (preferred:
 * a password given on the command line stays in the shell history and the
 * process list).
 *
 * #14: the historical script included files that no longer exist
 * (../lib/console.lib, ../modelo/...) and never inserted anything. It now
 * uses the current library layout, stores password_hash() (never MD5) and
 * sends every value as a bound parameter. Roles are not assigned here.
 */

/**
 * Insert one user. $db must provide queryParams($sql, array $params)
 * (PostgresDatabaseImpl). Returns true on success.
 */
function ancora_adduser_insert($db, $nombre, $info, $correo, $nick, $pass) {
	if (! is_string ( $nick ) || trim ( $nick ) === '' || ! is_string ( $pass ) || $pass === '')
		return false;
	$r = $db->queryParams ( 'INSERT INTO seguridad.usuario (nombre, info, correo, nick, contrasena) VALUES ($1, $2, $3, $4, $5)', array ($nombre, $info, $correo, trim ( $nick ), PasswordUtil::hash ( $pass ) ) );
	return $r !== false;
}

include_once dirname ( __FILE__ ) . "/../lib/basic/security/PasswordUtil.php";

if (isset ( $_SERVER ['SCRIPT_FILENAME'] ) && realpath ( $_SERVER ['SCRIPT_FILENAME'] ) === realpath ( __FILE__ )) {

	$adminpath = "../";

	include_once dirname ( __FILE__ ) . "/../lib/basic/cli/Console.php";

	$console = new Console ();

	if (! $console->verifyCli ())
		die ( "Esta aplicacion solo se ejecuta en la linea de comandos. \n" );

	$nombre = '';
	$info = '';
	$correo = '';
	$nick = '';
	$pass = '';

	if (isset ( $console->arguments ['-nombre'] )) $nombre = trim ( $console->arguments ['-nombre'] );
	if (isset ( $console->arguments ['-info'] )) $info = trim ( $console->arguments ['-info'] );
	if (isset ( $console->arguments ['-correo'] )) $correo = trim ( $console->arguments ['-correo'] );
	if (isset ( $console->arguments ['-nick'] )) $nick = trim ( $console->arguments ['-nick'] );
	if (isset ( $console->arguments ['-pass'] )) $pass = trim ( $console->arguments ['-pass'] );

	if ($nick != '' && $pass == '')
		$pass = trim ( ( string ) $console->getStream ( "Contrasena: " ) );

	if ($nick == '' || $pass == '') {
		$console->showMessage ( "Especifique el nombre de usuario y contrasena." );
		exit ( 1 );
	}

	include_once dirname ( __FILE__ ) . "/../lib/import.php";
	include_once dirname ( __FILE__ ) . "/../lib/basic/file/PropertiesFile.php";
	include_once dirname ( __FILE__ ) . "/../lib/enterprise/gendb/IDatabase.php";
	include_once dirname ( __FILE__ ) . "/../lib/enterprise/gendb/PostgresDatabaseImpl.php";

	$dbcfg = new PropertiesFile ( dirname ( __FILE__ ) . "/ancoragoh.conf" );
	$dbconnection = new PostgresDatabaseImpl ();
	if (! $dbconnection->Connect ( $dbcfg->dbname, $dbcfg->user, $dbcfg->password, $dbcfg->host, $dbcfg->port )) {
		$console->showMessage ( "No se puede conectar con la base de datos." );
		exit ( 1 );
	}

	if (ancora_adduser_insert ( $dbconnection, $nombre, $info, $correo, $nick, $pass ))
		$console->showMessage ( "Usuario agregado satisfactoriamente." );
	else {
		$console->showMessage ( "No se pudo agregar el usuario." );
		exit ( 1 );
	}
}

?>
