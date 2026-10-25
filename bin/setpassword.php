<?php

/**
 * Set (reset) the password of an existing user (run from the bin/ directory):
 *
 *   php setpassword.php -nick <nick>
 *
 * The new password is read from standard input, so it never appears in the
 * shell history or the process list. It is stored with password_hash().
 *
 * #14: accounts that still hold a legacy MD5 hash cannot log in while
 * legacy_md5_login is off (bin/security.conf, the default). This is how an
 * administrator resets them, including the seeded administrator of a fresh
 * installation, which otherwise has no usable password.
 */

/**
 * Replace the password of user $nick. $db must provide
 * getAsObjectsParams($sql, array $params) (PostgresDatabaseImpl). Returns
 * true if exactly one user was updated.
 */
function ancora_setpassword_update($db, $nick, $pass) {
	if (! is_string ( $nick ) || trim ( $nick ) === '' || ! is_string ( $pass ) || $pass === '')
		return false;
	$rows = $db->getAsObjectsParams ( 'UPDATE seguridad.usuario SET contrasena = $1 WHERE nick = $2 RETURNING id', array (PasswordUtil::hash ( $pass ), trim ( $nick ) ) );
	return is_array ( $rows ) && count ( $rows ) == 1;
}

include_once dirname ( __FILE__ ) . "/../lib/basic/security/PasswordUtil.php";

if (isset ( $_SERVER ['SCRIPT_FILENAME'] ) && realpath ( $_SERVER ['SCRIPT_FILENAME'] ) === realpath ( __FILE__ )) {

	$adminpath = "../";

	include_once dirname ( __FILE__ ) . "/../lib/basic/cli/Console.php";

	$console = new Console ();

	if (! $console->verifyCli ())
		die ( "Esta aplicacion solo se ejecuta en la linea de comandos. \n" );

	$nick = isset ( $console->arguments ['-nick'] ) ? trim ( $console->arguments ['-nick'] ) : '';
	if ($nick == '') {
		$console->showMessage ( "Especifique el nombre de usuario: -nick <nick>" );
		exit ( 1 );
	}

	$pass = trim ( ( string ) $console->getStream ( "Nueva contrasena: " ) );
	if ($pass == '') {
		$console->showMessage ( "La contrasena no puede estar vacia." );
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

	if (ancora_setpassword_update ( $dbconnection, $nick, $pass ))
		$console->showMessage ( "Contrasena cambiada." );
	else {
		$console->showMessage ( "No se pudo cambiar la contrasena (usuario inexistente?)." );
		exit ( 1 );
	}
}

?>
