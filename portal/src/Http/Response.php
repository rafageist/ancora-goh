<?php

declare(strict_types=1);

namespace Ancora\Portal\Http;

/** HTTP response with the portal's security headers. */
final class Response
{
    /** @var array<string, string> */
    public array $headers = [];

    /** @var list<string> Set-Cookie values */
    public array $cookies = [];

    public function __construct(public string $body = '', public int $status = 200)
    {
        $this->headers = [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Security-Policy' => "default-src 'none'; style-src 'self'; img-src 'self'; form-action 'self'; "
                . "frame-ancestors 'none'; base-uri 'none'",
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'no-referrer',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'Cache-Control' => 'no-store',
        ];
    }

    public static function text(string $body, int $status = 200): self
    {
        $r = new self($body, $status);
        $r->headers['Content-Type'] = 'text/plain; charset=utf-8';
        return $r;
    }

    public function send(): void
    {
        http_response_code($this->status);
        header_remove('X-Powered-By');
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        foreach ($this->cookies as $cookie) {
            header("Set-Cookie: $cookie", false);
        }
        echo $this->body;
    }
}
