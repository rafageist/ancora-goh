<?php

declare(strict_types=1);

namespace Divengine\Codegen\Tests;

use Divengine\Codegen\Model\ServiceMethod;
use Divengine\Codegen\Sql\ServiceSql;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServiceSqlTest extends TestCase
{
    public function testQuotedAndUnquotedReferencesBecomePlaceholders(): void
    {
        $r = ServiceSql::parameterize(new ServiceMethod('m', '', ['a', 'b'], "SELECT * FROM t WHERE a = '\$a' AND b = \$b AND c = \$a"));
        self::assertSame('SELECT * FROM t WHERE a = $1 AND b = $2 AND c = $1', $r['sql']);
        self::assertSame(['a', 'b'], $r['params']);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unbindable(): array
    {
        return [
            'unknown variable' => ['SELECT * FROM t WHERE a = $x'],
            'reference inside a literal' => ["SELECT * FROM t WHERE a LIKE '%\$a%'"],
            'unbalanced quote' => ["SELECT * FROM t WHERE a = '\$a"],
            'complex interpolation' => ['SELECT * FROM t WHERE a = {$a}'],
        ];
    }

    #[DataProvider('unbindable')]
    public function testRefusesWhatItCannotBind(string $sql): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('service method m');
        ServiceSql::parameterize(new ServiceMethod('m', '', ['a'], $sql));
    }
}
