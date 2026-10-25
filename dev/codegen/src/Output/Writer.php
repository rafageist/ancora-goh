<?php

declare(strict_types=1);

namespace Divengine\Codegen\Output;

use Divengine\Codegen\Generator\GeneratedFile;
use RuntimeException;

/**
 * Writes generated files under an output root. It never deletes anything:
 * a file is created or overwritten, and the folders it needs are created.
 * A path that would leave the output root is refused.
 */
final class Writer
{
    private int $written = 0;
    private int $unchanged = 0;

    public function __construct(private readonly string $root)
    {
        if (!is_dir($root)) {
            throw new RuntimeException("output directory not found: $root");
        }
    }

    public function write(GeneratedFile $file): void
    {
        $path = $this->path($file->path);
        if (is_file($path) && file_get_contents($path) === $file->content) {
            $this->unchanged++;
            return;
        }
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException("cannot create $dir");
        }
        if (file_put_contents($path, $file->content) === false) {
            throw new RuntimeException("cannot write $path");
        }
        $this->written++;
    }

    public function path(string $relative): string
    {
        $relative = str_replace('\\', '/', $relative);
        $parts = [];
        foreach (explode('/', $relative) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                throw new RuntimeException("generated path outside the output directory: $relative");
            }
            $parts[] = $part;
        }
        if ($parts === []) {
            throw new RuntimeException('empty generated path');
        }

        return rtrim($this->root, '/') . '/' . implode('/', $parts);
    }

    public function written(): int
    {
        return $this->written;
    }

    public function unchanged(): int
    {
        return $this->unchanged;
    }
}
