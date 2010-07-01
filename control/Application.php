<?php

/**
 * @name Application
 * @desc  Application Class
 * Part Control of MVC Pattern
 */

import ( "model.security: UsuarioModel, Usuario" );

class Application {
	
	/**
	 * @name VerifyLogout
	 * @desc Verify logout action
	 */
	public function VerifyLogout() {
		if (isset ( $_GET ['logout'] )) {
			unset ( $_SESSION ['user'] );
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
		
		$model = new UsuarioModel ( $dbconnection );
		
		$pass = md5 ( $password );
		
		$r = $model->getAllBy ( "nick = '$user' AND contrasena = '$pass'", "*", true );
		
		if (isset ( $r [0] )) {
			
			if ($r [0]->nick == $user) {
				$_SESSION ['user'] = $r [0]->id;
				$_SESSION ['nick'] = $r [0]->nick;
				return true;
			}
		}
		return false;
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
		
		import ( "model.security.RolDeUsuarioModel" );
		import ( "model.security.RolDeUsuario" );
		import ( "model.security.RolDeUsuarioCollection" );
		
		$m = new RolDeUsuarioCollection ( $dbconnection );
		$m->Load ( null, null, "*", "usuario = '{$_SESSION['user']}'" );
		
		$roles = array ();
		
		if (is_array ( $m->collection ))
			foreach ( $m->collection as $item )
				$roles [] = $item->rol;
		
		return $roles;
	
	}
	
	/**
	 * @desc load permissions
	 */
	
	public function getPermissionsOfCurrentUser() {
		global $dbconnection;
		
		$roles = $this->getRolesOfCurrentUser ();
		
		import ( "model.security.PermisoModel" );
		import ( "model.security.Permiso" );
		import ( "model.security.PermisoCollection" );
		
		$m = new PermisoCollection ( $dbconnection );
		
		$permisos = array ();
		
		foreach ( $roles as $rol ) {
			$m->Load ( null, null, "*", "rol = '$rol'" );
			foreach ( $m->collection as $item )
				$permisos [$item->permiso] = true;
		}
		
		return $permisos;
	}
	
	/**
	 * @desc show apllication
	 */
	public function showApplication() {
		global $dbconnection;
		global $adminpath;
		
		$permisos = array ();
		if (isset ( $_POST ['access'] )) {
			if ($_POST ['access'] != 'public')
				$permisos = $this->getPermissionsOfCurrentUser ();
		} else
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
	 * @desc Execute Events
	 */
	public function executeEvents() {
		
		global $adminpath;
		global $dbconnection;
		
		$app_event_prefix = "app_event_";
		
		$events_folders = array ("data/add", "data/set", "data/del", "view/gallery", "view/combo", "custom" );
		
		$event_permisos = new PropertiesFile ( "../bin/security.conf" );
		$permisos = $this->getPermissionsOfCurrentUser ();
		
		foreach ( $_POST as $key => $value ) {
			if ($value != "no")
				if (substr ( $key, 0, strlen ( $app_event_prefix ) ) == $app_event_prefix) {
					$f = substr ( $key, strlen ( $app_event_prefix ) );
					
					// comprobando los permisos
					if (! isset ( $permisos ["todos"] ))
						if (isset ( $event_permisos->properties ['event_permissions'] ))
							if (isset ( $event_permisos->properties ['event_permissions'] [$f] ))
								if (is_array ( $event_permisos->properties ['event_permissions'] [$f] ))
									foreach ( $event_permisos->properties ['event_permissions'] [$f] as $perm )
										if (! isset ( $permisos [$perm] )) {
											include "../view/custom/access_denied.tpl";
											return false;
										}
						//ejecutando
					foreach ( $events_folders as $value )
						if (file_exists ( "../control/$value/$f.php" )) {
							include "../control/$value/$f.php";
							break;
						}
				}
		}
	}
	
	/**
	 * @desc Run application
	 */
	public function Run() {
		
		if (isset ( $_POST ['access'] )) {
			if ($_POST ['access'] == 'public') {
				$this->executeEvents ();
				return true;
			}
		
		}
		
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
				else {
					include "../view/custom/msg_session_expired.tpl";
				}
			}
		}
	}
}

?>
