<?php

declare(strict_types=1);

namespace Divengine\Codegen\Tests;

use Divengine\Codegen\Model\ModelException;
use Divengine\Codegen\Model\ModelLoader;
use Divengine\Codegen\Profile\Profile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ModelLoaderTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/codegen-model-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            unlink($f);
        }
        rmdir($this->dir);
    }

    private static function profile(): Profile
    {
        return Profile::load(dirname(__DIR__) . '/profiles/ancoragoh.php');
    }

    /**
     * @param list<string> $tables file contents, written as t0.json, t1.json, ...
     */
    private function load(array $tables): void
    {
        $names = [];
        foreach ($tables as $i => $src) {
            file_put_contents("{$this->dir}/t$i.json", $src);
            $names[] = "\"t$i.json\"";
        }
        file_put_contents("{$this->dir}/main.json", "// banner\n{ data: { prefix: \"\", tabledefs: [" . implode(',', $names) . '], functiondefs: [] } }');
        (new ModelLoader(self::profile()))->load($this->dir, 'main.json');
    }

    private static function table(string $name = 'book', string $type = 'string', string $field = 'title', string $pk = '"title"'): string
    {
        return "/* banner */ { schema: \"\", name: \"$name\", fields: [ { name: \"$field\", type: \"$type\", long: 255, allownull: false, unique: false, }, ], primary_key: [$pk], foreign_keys: [] }";
    }

    public function testTheCommittedModelLoads(): void
    {
        $root = dirname(__DIR__, 3);
        $model = (new ModelLoader(self::profile()))->load("$root/dev/src", 'ancoragoh.json');
        self::assertCount(61, $model->tables);
        self::assertSame(['public'], array_keys($model->views));
        self::assertCount(1, (new ModelLoader(self::profile()))->loadServices("$root/dev/services"));
    }

    public function testAValidSyntheticModelLoads(): void
    {
        $this->load([self::table()]);
        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{0: list<string>, 1: string}>
     */
    public static function invalidModels(): array
    {
        return [
            'missing table name' => [['{ schema: "", fields: [], primary_key: [], foreign_keys: [] }'], 'missing name'],
            'unknown field type' => [[self::table(type: 'blob')], 'unknown type "blob"'],
            'invalid table identifier' => [[self::table(name: 'drop table x')], 'not a valid SQL/PHP identifier'],
            'invalid field identifier' => [[self::table(field: 'a-b', pk: '')], 'not a valid SQL/PHP identifier'],
            'duplicate table' => [[self::table(), self::table()], 'duplicate table book'],
            'primary key not a field' => [[self::table(pk: '"isbn"')], 'is not a field of book'],
            'backtick' => [[self::table(name: 'x`y')], 'backtick'],
            'syntax error' => [['{ schema: "", name: }'], 't0.json:1'],
        ];
    }

    /**
     * @param list<string> $tables
     */
    #[DataProvider('invalidModels')]
    public function testInvalidModelsAreRejected(array $tables, string $message): void
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage($message);
        $this->load($tables);
    }

    public function testAModelFileNameCannotLeaveTheModelDirectory(): void
    {
        file_put_contents("{$this->dir}/main.json", '{ data: { prefix: "", tabledefs: ["../etc/passwd.json"], functiondefs: [] } }');
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('invalid model file name');
        (new ModelLoader(self::profile()))->load($this->dir, 'main.json');
    }
}
