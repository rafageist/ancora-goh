<?php

declare(strict_types=1);

namespace Divengine\Codegen\Tests;

use Divengine\Codegen\Generator\Clock;
use Divengine\Codegen\Template\Renderer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TemplateTest extends TestCase
{
    private static function templateDir(): string
    {
        return dirname(__DIR__) . '/templates';
    }

    /** Trailing white space would be lost by editors: it must be written `{\s}`. */
    public function testTemplatesHaveNoTrailingWhitespace(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::templateDir(), \FilesystemIterator::SKIP_DOTS));
        $n = 0;
        foreach ($files as $file) {
            $n++;
            foreach (file((string) $file) ?: [] as $i => $line) {
                self::assertDoesNotMatchRegularExpression('/[ \t]\n?$/', $line, "$file:" . ($i + 1));
            }
        }
        self::assertGreaterThanOrEqual(17, $n);
    }

    public function testDialectKeepsCodeLiteral(): void
    {
        $dir = sys_get_temp_dir() . '/codegen-tpl-' . bin2hex(random_bytes(4));
        mkdir("$dir/t", 0777, true);
        file_put_contents("$dir/t/x.tpl", "<?php {\$a} [\$b] ?\$c \$c? <? ?> `{\$name}`(`[\$args]`\$`{\$value}``!\$_is_last`,`{\\s}``\$_is_last!``[/\$args]`);`{\\s}`\n");
        $out = (new Renderer($dir))->render('t/x.tpl', ['name' => 'f', 'args' => ['a', 'b']]);
        unlink("$dir/t/x.tpl");
        rmdir("$dir/t");
        rmdir($dir);
        self::assertSame('<?php {$a} [$b] ?$c $c? <? ?> f($a, $b); ', $out);
    }

    public function testUnresolvedTagIsRefused(): void
    {
        $dir = sys_get_temp_dir() . '/codegen-tpl-' . bin2hex(random_bytes(4));
        mkdir("$dir/t", 0777, true);
        file_put_contents("$dir/t/y.tpl", "`{\$missing");
        try {
            $this->expectException(RuntimeException::class);
            (new Renderer($dir))->render('t/y.tpl', []);
        } finally {
            unlink("$dir/t/y.tpl");
            rmdir("$dir/t");
            rmdir($dir);
        }
    }

    public function testSourceDateEpochFixesTheTimestamp(): void
    {
        self::assertSame('08/06/10 12:26:40', Clock::updated('1276000000'));
    }
}
