<?php

declare(strict_types=1);

namespace Ancora\Portal\Http;

/** Minimal immutable view of the incoming HTTP request. */
final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, string> $cookies
     * @param array<string, string> $headers lower-case names
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $post,
        public readonly array $cookies,
        public readonly array $headers,
        public readonly string $clientIp,
    ) {
    }

    public static function fromGlobals(): self
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_') && is_string($v)) {
                $headers[strtolower(str_replace('_', '-', substr($k, 5)))] = $v;
            }
        }
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        return new self(
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            is_string($path) ? $path : '/',
            $_GET,
            $_POST,
            array_filter($_COOKIE, 'is_string'),
            $headers,
            (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        );
    }

    public function input(string $name): string
    {
        $v = $this->post[$name] ?? '';
        return is_string($v) ? $v : '';
    }

    public function queryString(string $name): string
    {
        $v = $this->query[$name] ?? '';
        return is_string($v) ? $v : '';
    }
}
