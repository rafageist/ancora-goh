<?php

declare(strict_types=1);

namespace Divengine\Codegen\Template;

use divengine\div;
use RuntimeException;

/**
 * Renders the external Div templates (templates/**) with prepared data.
 *
 * The templates use the backtick dialect of {@see Dialect}. A template must
 * not end a line with white space (editors strip it): a meaningful trailing
 * space is written `{\s}`. The final newline of a template file is not part
 * of the output. A rendered text that still contains a backtick has an
 * unresolved tag and is refused.
 */
final class Renderer
{
    /** @var array<string, string> */
    private array $sources = [];

    public function __construct(private readonly string $templateDir)
    {
        Dialect::install();
        if (!is_dir($templateDir)) {
            throw new RuntimeException("template directory not found: $templateDir");
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data): string
    {
        $source = $this->source($template);
        $out = div::div($source, $data, [], 1, true);
        if (!is_string($out)) {
            throw new RuntimeException("template $template: no output");
        }
        if (str_contains($out, Dialect::SIGIL)) {
            $line = substr_count(substr($out, 0, (int) strpos($out, Dialect::SIGIL)), "\n") + 1;
            throw new RuntimeException("template $template: unresolved Div tag near output line $line");
        }

        return $out;
    }

    public function templateDir(): string
    {
        return $this->templateDir;
    }

    private function source(string $template): string
    {
        if (!isset($this->sources[$template])) {
            if (preg_match('#^[a-z0-9_-]+(/[A-Za-z0-9_.-]+)*\.tpl$#', $template) !== 1) {
                throw new RuntimeException("invalid template name $template");
            }
            $path = $this->templateDir . '/' . $template;
            if (!is_file($path)) {
                throw new RuntimeException("template not found: $path");
            }
            $source = (string) file_get_contents($path);
            // the final newline of a template file is not part of the output
            if (str_ends_with($source, "\n")) {
                $source = substr($source, 0, -1);
            }
            $this->sources[$template] = $source;
        }

        return $this->sources[$template];
    }
}
