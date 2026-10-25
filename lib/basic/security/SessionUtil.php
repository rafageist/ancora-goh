<?php
/**
 * Session hardening helpers (issue #14).
 *
 * - start(): strict mode (reject uninitialised session ids), cookies only,
 *   HttpOnly and SameSite=Lax cookies, Secure when the request is HTTPS.
 * - regenerate(): new session id after login (session fixation).
 * - destroy(): clear data, expire the cookie and destroy the session (logout).
 */
class SessionUtil {

	public static function isHttps() {
		if (! empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
			return true;
		return isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443';
	}

	/**
	 * Configure and start the session. Must run before any output.
	 */
	public static function start() {
		if (session_status() === PHP_SESSION_ACTIVE)
			return true;

		ini_set('session.use_strict_mode', '1');
		ini_set('session.use_only_cookies', '1');
		ini_set('session.use_trans_sid', '0');
		ini_set('session.cookie_httponly', '1');
		ini_set('session.cookie_samesite', 'Lax');

		$params = session_get_cookie_params();
		session_set_cookie_params(array(
			'lifetime' => 0,
			'path' => $params['path'],
			'domain' => $params['domain'],
			'secure' => self::isHttps(),
			'httponly' => true,
			'samesite' => 'Lax'
		));

		return session_start();
	}

	/**
	 * Issue a new session id and delete the old session (call on login).
	 */
	public static function regenerate() {
		if (session_status() === PHP_SESSION_ACTIVE)
			return session_regenerate_id(true);
		return false;
	}

	/**
	 * Destroy the current session completely (call on logout).
	 */
	public static function destroy() {
		$_SESSION = array();
		if (session_status() !== PHP_SESSION_ACTIVE)
			return true;

		if (ini_get('session.use_cookies') && ! headers_sent()) {
			$p = session_get_cookie_params();
			setcookie(session_name(), '', array(
				'expires' => time() - 42000,
				'path' => $p['path'],
				'domain' => $p['domain'],
				'secure' => $p['secure'],
				'httponly' => $p['httponly'],
				'samesite' => isset($p['samesite']) ? $p['samesite'] : 'Lax'
			));
		}
		return session_destroy();
	}
}

?>
