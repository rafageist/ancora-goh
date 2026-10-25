<?php
/**
 * In-memory stand-in for PostgresDatabaseImpl (issue #14 tests).
 *
 * It never connects anywhere. Every call is recorded as
 * array('method' => ..., 'sql' => ..., 'params' => ...). Parameterized
 * queries are answered by a small emulation of the four statements used by
 * the authentication code, against fixture tables held in memory; values
 * are compared exactly as PostgreSQL would compare bound parameters.
 * Raw (interpolated) queries return $raw_result.
 */
class StubDatabase {

	public $calls = array ();
	public $usuarios = array ();       // id => object(id, nick, contrasena)
	public $roles_de_usuario = array (); // array(usuario, rol)
	public $permisos = array ();       // array(permiso, rol)
	public $tables = array ();         // table => list of row objects, for "WHERE id = $1" lookups
	public $raw_result = array ();
	public $fail_updates = false;

	public function Connect($dbname, $user, $password, $host = 'localhost', $port = '5432') {
		return false;
	}

	public function getDefault($sql, $debug = false) {
		$this->calls [] = array ('method' => 'getDefault', 'sql' => $sql, 'params' => null );
		return false;
	}

	public function getAsObjects($sql) {
		$this->calls [] = array ('method' => 'getAsObjects', 'sql' => $sql, 'params' => null );
		return $this->raw_result;
	}

	public function getAsArray($sql) {
		$this->calls [] = array ('method' => 'getAsArray', 'sql' => $sql, 'params' => null );
		return $this->raw_result;
	}

	public function Disconnect() {
	}

	public function queryParams($sql, array $params) {
		$this->calls [] = array ('method' => 'queryParams', 'sql' => $sql, 'params' => array_values ( $params ) );
		$params = array_values ( $params );
		if ($sql === 'UPDATE seguridad.usuario SET contrasena = $1 WHERE id = $2 AND contrasena = $3') {
			if ($this->fail_updates)
				return false;
			foreach ( $this->usuarios as $id => $u )
				if ($u->id === $params [1] && $u->contrasena === $params [2])
					$this->usuarios [$id]->contrasena = $params [0];
			return true;
		}
		if (strpos ( $sql, 'INSERT INTO seguridad.usuario' ) === 0)
			return true;
		return true;
	}

	public function getAsObjectsParams($sql, array $params) {
		$this->calls [] = array ('method' => 'getAsObjectsParams', 'sql' => $sql, 'params' => array_values ( $params ) );
		$params = array_values ( $params );
		$out = array ();
		if ($sql === 'SELECT id, nick, contrasena FROM seguridad.usuario WHERE nick = $1') {
			foreach ( $this->usuarios as $u )
				if ($u->nick === $params [0])
					$out [] = clone $u;
			return $out;
		}
		if ($sql === 'SELECT rol FROM seguridad.rol_de_usuario WHERE usuario = $1') {
			foreach ( $this->roles_de_usuario as $r )
				if ($r [0] === $params [0])
					$out [] = ( object ) array ('rol' => $r [1] );
			return $out;
		}
		if ($sql === 'SELECT permiso FROM seguridad.permiso WHERE rol = $1') {
			foreach ( $this->permisos as $p )
				if ($p [1] === $params [0])
					$out [] = ( object ) array ('permiso' => $p [0] );
			return $out;
		}
		if (preg_match ( '/^SELECT \* FROM ([a-z_]+) WHERE id = \$1;$/', $sql, $m ) && isset ( $this->tables [$m [1]] )) {
			foreach ( $this->tables [$m [1]] as $row )
				if ($row->id === $params [0])
					$out [] = $row;
			return $out;
		}
		return $out;
	}

	public function callsOf($method) {
		$r = array ();
		foreach ( $this->calls as $c )
			if ($c ['method'] === $method)
				$r [] = $c;
		return $r;
	}

	public function rawCalls() {
		$r = array ();
		foreach ( $this->calls as $c )
			if ($c ['params'] === null)
				$r [] = $c;
		return $r;
	}
}

?>
