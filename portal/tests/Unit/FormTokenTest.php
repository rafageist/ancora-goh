<?php

declare(strict_types=1);

namespace Ancora\Portal\Tests\Unit;

use Ancora\Portal\Http\FormToken;
use Ancora\Portal\Http\Request;
use Ancora\Portal\Http\Response;
use PHPUnit\Framework\TestCase;

final class FormTokenTest extends TestCase
{
    private const KEY = 'unit-test-key-unit-test-key-0123456789';

    private function post(string $cookie, string $token, string $name = 'portal_csrf'): Request
    {
        return new Request('POST', '/request', [], [FormToken::FIELD => $token], [$name => $cookie], [], '127.0.0.1');
    }

    public function testValidTokenWithinTheTimeWindow(): void
    {
        $t = new FormToken(self::KEY, false);
        $cookie = str_repeat('a', 43);
        $token = $t->issue('request', $cookie, 1000);
        self::assertSame('ok', $t->check('request', $this->post($cookie, $token), 1010));
        self::assertSame('too_fast', $t->check('request', $this->post($cookie, $token), 1001));
        self::assertSame('expired', $t->check('request', $this->post($cookie, $token), 1000 + 7201));
    }

    public function testTokenIsBoundToCookieFormAndKey(): void
    {
        $t = new FormToken(self::KEY, false);
        $cookie = str_repeat('a', 43);
        $token = $t->issue('request', $cookie, 1000);
        self::assertSame('invalid', $t->check('request', $this->post(str_repeat('b', 43), $token), 1010), 'other cookie');
        self::assertSame('invalid', $t->check('verify', $this->post($cookie, $token), 1010), 'other form');
        $other = new FormToken(self::KEY . 'x', false);
        self::assertSame('invalid', $other->check('request', $this->post($cookie, $token), 1010), 'other key');
        self::assertSame('invalid', $t->check('request', $this->post($cookie, ''), 1010), 'missing token');
        self::assertSame('invalid', $t->check('request', $this->post('', $token), 1010), 'missing cookie');
        // the timestamp is covered by the HMAC: moving it back to pass the timing check fails
        [, $mac] = explode('.', $token);
        self::assertSame('invalid', $t->check('request', $this->post($cookie, '990.' . $mac), 1001));
    }

    public function testSecureCookieUsesHostPrefixAndFlags(): void
    {
        $t = new FormToken(self::KEY, true);
        $resp = new Response();
        $value = $t->ensureCookie(new Request('GET', '/request', [], [], [], [], ''), $resp);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]{43}$/', $value);
        self::assertCount(1, $resp->cookies);
        self::assertStringStartsWith('__Host-portal_csrf=' . $value . '; Path=/; HttpOnly; SameSite=Lax; Secure', $resp->cookies[0]);
        $again = new Response();
        self::assertSame($value, $t->ensureCookie(new Request('GET', '/', [], [], ['__Host-portal_csrf' => $value], [], ''), $again));
        self::assertSame([], $again->cookies, 'an existing cookie is reused');
    }
}
