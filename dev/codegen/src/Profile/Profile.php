<?php

declare(strict_types=1);

namespace Divengine\Codegen\Profile;

/**
 * Project profile: the project-specific settings of a generation.
 *
 * A profile is a PHP file that returns an array (see
 * profiles/ancoragoh.php for the documented keys). The generator logic and
 * the templates only read project settings through this class.
 */
final class Profile
{
    private const REQUIRED = [
        'name', 'model', 'naming', 'packages', 'default_package', 'package_selectors',
        'types', 'ui', 'output', 'runtime',
    ];

    /**
     * @param array<string, mixed> $settings
     */
    private function __construct(private readonly array $settings, private readonly string $source)
    {
    }

    public static function load(string $file): self
    {
        if (!is_file($file)) {
            throw new ProfileException("profile not found: $file");
        }
        $settings = (static fn (string $f): mixed => require $f)($file);
        if (!is_array($settings)) {
            throw new ProfileException("$file: a profile must return an array");
        }

        return self::fromArray($settings, $file);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function fromArray(array $settings, string $source = '<array>'): self
    {
        foreach (self::REQUIRED as $key) {
            if (!array_key_exists($key, $settings)) {
                throw new ProfileException("$source: missing \"$key\"");
            }
        }
        if (!is_string($settings['name']) || preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $settings['name']) !== 1) {
            throw new ProfileException("$source: \"name\" must be an identifier");
        }
        foreach (['dir', 'file'] as $key) {
            if (!isset($settings['model'][$key]) || !is_string($settings['model'][$key])) {
                throw new ProfileException("$source: missing model.$key");
            }
        }
        foreach ($settings['packages'] as $package => $selectors) {
            if (!is_string($package) || preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)*$/', $package) !== 1) {
                throw new ProfileException("$source: invalid package name " . json_encode($package));
            }
            if (!is_array($selectors)) {
                throw new ProfileException("$source: package $package must list its classes or {schema} selectors");
            }
        }
        foreach (['quoted', 'database'] as $key) {
            if (!isset($settings['types'][$key]) || !is_array($settings['types'][$key])) {
                throw new ProfileException("$source: missing types.$key");
            }
        }

        return new self($settings, $source);
    }

    public function name(): string
    {
        return $this->settings['name'];
    }

    public function source(): string
    {
        return $this->source;
    }

    /**
     * A setting by dotted path ("output.sql_dir").
     */
    public function get(string $path, mixed $default = null): mixed
    {
        $value = $this->settings;
        foreach (explode('.', $path) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }

    public function string(string $path): string
    {
        $value = $this->get($path);
        if (!is_string($value)) {
            throw new ProfileException("{$this->source}: \"$path\" must be a string");
        }

        return $value;
    }

    /**
     * @return array<mixed>
     */
    public function list(string $path): array
    {
        $value = $this->get($path, []);
        if (!is_array($value)) {
            throw new ProfileException("{$this->source}: \"$path\" must be an array");
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->settings;
    }
}
