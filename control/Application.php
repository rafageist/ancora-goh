<?php

/**
 * @name Application
 * @desc  Application Class
 * Part Control of MVC Pattern
 *
 * #14: parameterized login with password_hash()/legacy MD5 migration,
 * session fixation protection, and deny-by-default event authorization
 * (the former POST "access=public" bypass is gone).
 */

import ( "model.security: UsuarioModel, Usuario" );
import ( "lib.basic.security: PasswordUtil, SessionUtil" );

class Application {

	/**
	 * Events of the login page's public timetable viewer
	 * (view/custom/authform.tpl ->
	 * www/js/app/client/browsers/ancoragoh_client_browsers_publico.js). They
	 * only read data. They run without a session only when bin/security.conf
	 * sets public_viewer_anonymous = true; by default (maintainer decision,
	 * no publication policy yet) they need a login like every other event.
	 * Every other event requires an authenticated user and a permission (see
	 * bin/security.conf). Login (POST btnLogin) and logout (GET logout) are
	 * not events; Run() handles them.
	 */
	public static $anonymous_events = array (
		'get_browser_publico',            // public timetable window (plans and periods)
		'get_minibrowser_publico',        // timetable of one group/teacher/place/resource
		'get_public_treeBrigadas',        // tree of groups
		'get_public_list_profesores',     // list of teachers
		'get_public_list_lugares',        // list of places
		'get_public_list_otros_recursos'  // list of other resources
	);

	public $events_folders = array ("data/add", "data/set", "data/del", "view/gallery", "view/combo", "custom" );

	public $app_event_prefix = "app_event_";

	public $security_conf = "../bin/security.conf";

	private $security_properties = null;

	/**
	 * @desc bin/security.conf properties, read once per request
	 */
	public function getSecurityProperties() {
		if ($this->security_properties === null) {
			$conf = new PropertiesFile ( $this->security_conf );
			$this->security_properties = $conf->properties;
		}
		return $this->security_properties;
	}

	/**
	 * @desc True only if bin/security.conf sets the switch to the literal true
	 * (public_viewer_anonymous, legacy_md5_login). Missing or any other value
	 * means off.
	 */
	public function isSecuritySwitchOn($name) {
		$p = $this->getSecurityProperties ();
		return isset ( $p [$name] ) && $p [$name] === true;
	}

	/**
	 * @desc True if the event may run without a session
	 */
	public function isAnonymousEvent($event) {
		return $this->isSecuritySwitchOn ( 'public_viewer_anonymous' ) && in_array ( $event, self::$anonymous_events, true );
	}

	/**
	 * @name VerifyLogout
	 * @desc Verify logout action
	 */
	public function VerifyLogout() {
		if (isset ( $_GET ['logout'] )) {
			SessionUtil::destroy ();
			return true;
		}
		return false;
	}

	/**
	 * @desc VerifyAuth
	 * @desc Verify authentication
	 */
	public function VerifyAuth($user, $password) {

		global $dbconnection;

		if (! is_string ( $user ) || ! is_string ( $password ) || $user === '' || $password === '')
			return false;

		$rows = $dbconnection->getAsObjectsParams ( 'SELECT id, nick, contrasena FROM seguridad.usuario WHERE nick = $1', array ($user ) );

		if (! is_array ( $rows ) || count ( $rows ) != 1 || $rows [0]->nick !== $user) {
			PasswordUtil::dummyVerify ( $password );
			return false;
		}

		$row = $rows [0];

		// Legacy MD5 rows log in only during a controlled recovery
		// (legacy_md5_login = true in bin/security.conf). Otherwise the
		// account needs a password reset (bin/setpassword.php).
		if (PasswordUtil::isLegacyMd5 ( $row->contrasena ) && ! $this->isSecuritySwitchOn ( 'legacy_md5_login' )) {
			PasswordUtil::dummyVerify ( $password );
			return false;
		}

		$result = PasswordUtil::verify ( $password, $row->contrasena );

		if ($result == PasswordUtil::RESULT_FAIL)
			return false;

		if ($result == PasswordUtil::RESULT_OK_REHASH) {
			// Replace the legacy MD5 (or outdated) hash right away. The old
			// value is part of the WHERE clause so a concurrent change wins.
			// If the update fails the login still succeeds and the next
			// login retries; the stored value never becomes weaker.
			$dbconnection->queryParams ( 'UPDATE seguridad.usuario SET contrasena = $1 WHERE id = $2 AND contrasena = $3', array (PasswordUtil::hash ( $password ), $row->id, $row->contrasena ) );
		}

		SessionUtil::regenerate ();
		$_SESSION ['user'] = $row->id;
		$_SESSION ['nick'] = $row->nick;
		return true;
	}
	/**
	 * @name VerifyLogin
	 * @desc Verify login action
	 */
	public function VerifyLogin() {

		if (isset ( $_SESSION ['user'] ))
			return 'allow';

		if (isset ( $_POST ['btnLogin'] )) {
			if (isset ( $_POST ['edtUser'] ) && isset ( $_POST ['edtPass'] )) {

				if ($this->VerifyAuth ( $_POST ['edtUser'], $_POST ['edtPass'] ) == true)
					return 'allow';
				else
					return 'access_denied';
			}
		}
		return 'normal';

	}

	/**
	 * @desc Show Access denied
	 */
	public function showAccessDenied() {
		include "../view/custom/access_denied.tpl";
	}

	/**
	 * @desc Return roles of current user
	 */

	public function getRolesOfCurrentUser() {

		global $dbconnection;

		if (! isset ( $_SESSION ['user'] ))
			return array ();

		$rows = $dbconnection->getAsObjectsParams ( 'SELECT rol FROM seguridad.rol_de_usuario WHERE usuario = $1', array ($_SESSION ['user'] ) );

		$roles = array ();
		if (is_array ( $rows ))
			foreach ( $rows as $item )
				$roles [] = $item->rol;

		return $roles;

	}

	/**
	 * @desc load permissions
	 */

	public function getPermissionsOfCurrentUser() {
		global $dbconnection;

		$permisos = array ();

		foreach ( $this->getRolesOfCurrentUser () as $rol ) {
			$rows = $dbconnection->getAsObjectsParams ( 'SELECT permiso FROM seguridad.permiso WHERE rol = $1', array ($rol ) );
			if (is_array ( $rows ))
				foreach ( $rows as $item )
					$permisos [$item->permiso] = true;
		}

		return $permisos;
	}

	/**
	 * @desc event => list of required permissions, from bin/security.conf
	 */
	public function getEventPermissions() {
		$p = $this->getSecurityProperties ();
		if (isset ( $p ['event_permissions'] ) && is_array ( $p ['event_permissions'] ))
			return $p ['event_permissions'];
		return array ();
	}

	/**
	 * @desc Event names requested in this POST, in order
	 */
	public function getRequestedEvents() {
		$events = array ();
		$n = strlen ( $this->app_event_prefix );
		foreach ( $_POST as $key => $value ) {
			if ($value === "no")
				continue;
			if (substr ( ( string ) $key, 0, $n ) == $this->app_event_prefix)
				$events [] = substr ( ( string ) $key, $n );
		}
		return $events;
	}

	/**
	 * @desc Deny-by-default authorization of one event.
	 */
	public function isEventAllowed($event, $authenticated, $permisos, $event_permissions) {

		// handler names are identifiers; this also rules out path traversal
		// in the include below (e.g. "app_event_../../x")
		if (! is_string ( $event ) || ! preg_match ( '/^[A-Za-z0-9_]+$/', $event ))
			return false;

		if ($this->isAnonymousEvent ( $event ))
			return true;

		if (! $authenticated)
			return false;

		if (isset ( $permisos ['todos'] ))
			return true;

		if (! isset ( $event_permissions [$event] ) || ! is_array ( $event_permissions [$event] ))
			return false;

		foreach ( $event_permissions [$event] as $perm )
			if (! is_string ( $perm ) || ! isset ( $permisos [$perm] ))
				return false;

		return true;
	}

	/**
	 * @desc True if the request names at least one event and all are anonymous
	 */
	public function requestHasOnlyAnonymousEvents() {
		$events = $this->getRequestedEvents ();
		if (count ( $events ) == 0)
			return false;
		foreach ( $events as $event )
			if (! $this->isAnonymousEvent ( $event ))
				return false;
		return true;
	}

	/**
	 * @desc show apllication
	 */
	public function showApplication() {
		global $dbconnection;
		global $adminpath;

		$permisos = $this->getPermissionsOfCurrentUser ();
		include "../view/custom/app.tpl";
	}

	/**
	 * @desc Show authentication form
	 */
	public function showAuthForm() {
		include "../view/custom/authform.tpl";
	}

	/**
	 * @desc Include the handler file of an (already authorized) event
	 */
	public function dispatchEvent($f) {

		global $adminpath;
		global $dbconnection;

		foreach ( $this->events_folders as $folder )
			if (file_exists ( "../control/$folder/$f.php" )) {
				include "../control/$folder/$f.php";
				return true;
			}
		return false;
	}

	/**
	 * @desc Execute Events
	 */
	public function executeEvents() {

		$authenticated = isset ( $_SESSION ['user'] );
		$permisos = $authenticated ? $this->getPermissionsOfCurrentUser () : array ();
		$event_permissions = $this->getEventPermissions ();

		foreach ( $this->getRequestedEvents () as $f ) {
			if (! $this->isEventAllowed ( $f, $authenticated, $permisos, $event_permissions )) {
				$this->showAccessDenied ();
				return false;
			}
			$this->dispatchEvent ( $f );
		}
		return true;
	}

	/**
	 * @desc Run application
	 */
	public function Run() {

		if ($this->VerifyLogout () == true)
			$this->showAuthForm ();
		else {
			$vl = $this->VerifyLogin ();

			if ($vl == 'access_denied') {
				$this->showAccessDenied ();
				if (! isset ( $_GET ['execute'] ))
					$this->showAuthForm ();
			} else if ($vl == 'allow') {
				$this->executeEvents ();
				if (! isset ( $_GET ['execute'] ))
					$this->showApplication ();
			} else {

				if (! isset ( $_GET ['execute'] ))
					$this->showAuthForm ();
				else if ($this->requestHasOnlyAnonymousEvents ())
					$this->executeEvents ();
				else {
					include "../view/custom/msg_session_expired.tpl";
				}
			}
		}
	}
}

?>
