<?php
/**
 * Password storage helpers (issue #14).
 *
 * New credentials are stored with password_hash() (PASSWORD_DEFAULT).
 * Historical accounts were stored as unsalted md5(password): a stored
 * value of exactly 32 hexadecimal characters is treated as a legacy hash.
 * It is accepted only when md5(candidate) matches it (constant-time
 * comparison), and the caller must then replace it with a modern hash
 * (see Application::VerifyAuth). The stored legacy hash itself is never
 * accepted as a password.
 */
class PasswordUtil {

	const RESULT_FAIL = 0;
	const RESULT_OK = 1;
	const RESULT_OK_REHASH = 2;

	/**
	 * Hash a new password for storage.
	 */
	public static function hash($plain) {
		return password_hash((string) $plain, PASSWORD_DEFAULT);
	}

	/**
	 * True if the stored value is a legacy unsalted MD5 hex digest.
	 */
	public static function isLegacyMd5($stored) {
		return is_string($stored) && preg_match('/^[0-9a-fA-F]{32}$/', $stored) === 1;
	}

	/**
	 * Verify a candidate password against a stored value.
	 * Returns RESULT_FAIL, RESULT_OK, or RESULT_OK_REHASH (verified, but the
	 * stored value is legacy MD5 or uses outdated hash parameters and must
	 * be replaced with self::hash($plain)).
	 */
	public static function verify($plain, $stored) {
		if (! is_string($plain) || ! is_string($stored) || $plain === '' || $stored === '')
			return self::RESULT_FAIL;

		if (self::isLegacyMd5($stored)) {
			if (hash_equals(strtolower($stored), md5($plain)))
				return self::RESULT_OK_REHASH;
			return self::RESULT_FAIL;
		}

		$info = password_get_info($stored);
		if ($info['algo'] === null || $info['algo'] === 0)
			return self::RESULT_FAIL; // unknown format (e.g. plain text): never accepted

		if (! password_verify($plain, $stored))
			return self::RESULT_FAIL;

		if (password_needs_rehash($stored, PASSWORD_DEFAULT))
			return self::RESULT_OK_REHASH;

		return self::RESULT_OK;
	}

	/**
	 * Spend roughly the same time as a real verification when the user
	 * does not exist, so response time does not reveal valid nicks.
	 */
	public static function dummyVerify($plain) {
		static $dummy = null;
		if ($dummy === null)
			$dummy = password_hash('ancora-dummy-password', PASSWORD_DEFAULT);
		password_verify((string) $plain, $dummy);
		return self::RESULT_FAIL;
	}
}

?>
