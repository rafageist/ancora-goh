<?php

declare(strict_types=1);

namespace Ancora\Portal;

use RuntimeException;

/**
 * Portal configuration from environment variables. Secrets are read from
 * files (*_FILE) so they never appear in the process environment of the web
 * server, in logs or in the repository.
 */
final class Config
{
    public const PRIVACY_VERSION = '2026-10-25';

    /**
     * @param array<string, string> $env
     */
    private function __construct(private readonly array $env)
    {
    }

    /**
     * @param array<string, string>|null $env defaults to getenv()
     */
    public static function fromEnvironment(?array $env = null): self
    {
        return new self($env ?? getenv());
    }

    public function get(string $name, ?string $default = null): string
    {
        $value = $this->env[$name] ?? null;
        if ($value === null || $value === '') {
            if ($default === null) {
                throw new RuntimeException("missing configuration $name");
            }
            return $default;
        }
        return $value;
    }

    /** A secret from NAME_FILE (preferred) or NAME. */
    public function secret(string $name): string
    {
        $file = $this->env[$name . '_FILE'] ?? '';
        if ($file !== '') {
            $value = @file_get_contents($file);
            if ($value === false) {
                throw new RuntimeException("cannot read $name" . '_FILE');
            }
            return trim($value);
        }
        return $this->get($name);
    }

    public function int(string $name, int $default): int
    {
        $value = $this->env[$name] ?? '';
        if ($value === '') {
            return $default;
        }
        if (!preg_match('/^[0-9]+$/', $value)) {
            throw new RuntimeException("configuration $name must be a non-negative integer");
        }
        return (int) $value;
    }

    public function bool(string $name, bool $default): bool
    {
        $value = $this->env[$name] ?? '';
        return $value === '' ? $default : in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    /** Public base URL, e.g. https://timetables.divengine.com (no trailing slash). */
    public function baseUrl(): string
    {
        $url = rtrim($this->get('PORTAL_BASE_URL'), '/');
        if (!preg_match('#^https?://[A-Za-z0-9.-]+(:[0-9]+)?$#', $url)) {
            throw new RuntimeException('PORTAL_BASE_URL must be scheme://host[:port]');
        }
        return $url;
    }

    /** HMAC key for CSRF tokens and rate-limit buckets (at least 32 bytes). */
    public function hmacKey(): string
    {
        $key = $this->secret('PORTAL_SECRET');
        if (strlen($key) < 32) {
            throw new RuntimeException('PORTAL_SECRET must be at least 32 bytes');
        }
        return $key;
    }
}
