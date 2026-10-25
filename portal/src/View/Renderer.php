<?php

declare(strict_types=1);

namespace Ancora\Portal\View;

/**
 * Renders PHP templates from templates/. Templates receive their variables
 * and an escaping helper $e (HTML text and attribute context); they contain
 * presentation only.
 */
final class Renderer
{
    public function __construct(private readonly string $dir)
    {
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    /** @param array<string, mixed> $vars */
    public function render(string $template, array $vars = []): string
    {
        if (!preg_match('/^[a-z_]+$/', $template)) {
            throw new \InvalidArgumentException('invalid template name');
        }
        $e = [self::class, 'escape'];
        $content = $this->include("$this->dir/$template.php", $vars + ['e' => $e]);
        return $this->include("$this->dir/layout.php", ['content' => $content, 'title' => $vars['title'] ?? '', 'e' => $e]);
    }

    /** @param array<string, mixed> $vars */
    private function include(string $file, array $vars): string
    {
        ob_start();
        (static function (string $__file, array $__vars): void {
            extract($__vars, EXTR_SKIP);
            require $__file;
        })($file, $vars);
        return (string) ob_get_clean();
    }
}
