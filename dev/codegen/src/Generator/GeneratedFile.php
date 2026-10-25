<?php

declare(strict_types=1);

namespace Divengine\Codegen\Generator;

/** A generated file: path relative to the output root, and its content. */
final readonly class GeneratedFile
{
    public function __construct(
        public string $path,
        public string $content,
    ) {
    }
}
