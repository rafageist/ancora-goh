<?php

declare(strict_types=1);

namespace Divengine\Codegen\Tests;

use Divengine\Codegen\Model\JsonReader;
use Divengine\Codegen\Model\ModelException;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

final class JsonReaderTest extends TestCase
{
    public function testRelaxedSyntax(): void
    {
        $src = "/** banner */\n  //-$ 20/06/10\n{ a: 1, 'b': \"x\\\"y\", c: [true, false, null, 1.5,], d: { e: \"// not a comment\" }, }";
        self::assertSame(
            ['a' => 1, 'b' => 'x"y', 'c' => [true, false, null, 1.5], 'd' => ['e' => '// not a comment']],
            (new JsonReader())->decode($src),
        );
    }

    /**
     * Every committed model file decodes to what the historical decoder
     * (lib/parsers/json/JSON.php, Services_JSON) returned. That 2010
     * library creates a dynamic property, deprecated since PHP 8.2.
     */
    #[IgnoreDeprecations]
    public function testCompatibleWithServicesJsonOnTheCommittedModel(): void
    {
        $root = dirname(__DIR__, 3);
        $legacy = "$root/lib/parsers/json/JSON.php";
        if (!is_file($legacy)) {
            self::markTestSkipped('Services_JSON not available');
        }
        require_once $legacy;
        $services = new \Services_JSON();
        $reader = new JsonReader();
        $files = array_merge(glob("$root/dev/src/*.json") ?: [], glob("$root/dev/services/*.json") ?: []);
        self::assertGreaterThan(200, count($files));
        foreach ($files as $file) {
            $src = (string) file_get_contents($file);
            self::assertSame(
                json_encode($services->decode($src)),
                json_encode($reader->decode($src, $file)),
                $file,
            );
        }
    }

    public function testErrorsNameTheLine(): void
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('f.json:3');
        (new JsonReader())->decode("{\n a: 1,\n b: }", 'f.json');
    }
}
