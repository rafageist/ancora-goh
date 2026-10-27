<?php

declare(strict_types=1);

namespace Ancora\Portal\Http;

/**
 * Stateless CSRF protection and form-timing check (signed double submit).
 *
 * A random value lives in a cookie (__Host- prefixed over HTTPS). Each form
 * carries "issued_at.HMAC(key, form|cookie|issued_at)". A POST is accepted
 * only if the HMAC matches the cookie, and the form was issued at least
 * $minSeconds (bots submit instantly) and at most $maxSeconds ago.
 */
final class FormToken
{
    public const FIELD = 'form_token';

    public function __construct(
        private readonly string $key,
        private readonly bool $secureCookie,
        private readonly int $minSeconds = 3,
        private readonly int $maxSeconds = 7200,
    ) {
    }

    public function cookieName(): string
    {
        return $this->secureCookie ? '__Host-portal_csrf' : 'portal_csrf';
    }

    /** Cookie value of the request, or a new one (added to $response). */
    public function ensureCookie(Request $request, Response $response): string
    {
        $value = $request->cookies[$this->cookieName()] ?? '';
        if (preg_match('/^[A-Za-z0-9_-]{43}$/', $value)) {
            return $value;
        }
        $value = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $response->cookies[] = $this->cookieName() . '=' . $value . '; Path=/; HttpOnly; SameSite=Lax'
            . ($this->secureCookie ? '; Secure' : '');
        return $value;
    }

    public function issue(string $form, string $cookie, ?int $now = null): string
    {
        $t = (string) ($now ?? time());
        return $t . '.' . hash_hmac('sha256', "$form|$cookie|$t", $this->key);
    }

    /** "ok", "invalid", "too_fast" or "expired". */
    public function check(string $form, Request $request, ?int $now = null): string
    {
        $cookie = $request->cookies[$this->cookieName()] ?? '';
        $token = $request->input(self::FIELD);
        if (!preg_match('/^[A-Za-z0-9_-]{43}$/', $cookie) || !preg_match('/^([0-9]{1,12})\.([0-9a-f]{64})$/', $token, $m)) {
            return 'invalid';
        }
        if (!hash_equals(hash_hmac('sha256', "$form|$cookie|{$m[1]}", $this->key), $m[2])) {
            return 'invalid';
        }
        $age = ($now ?? time()) - (int) $m[1];
        if ($age < $this->minSeconds) {
            return 'too_fast';
        }
        if ($age > $this->maxSeconds) {
            return 'expired';
        }
        return 'ok';
    }
}
