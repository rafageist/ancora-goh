<?php

declare(strict_types=1);

namespace Ancora\Portal\Tests\Unit;

use Ancora\Portal\Domain\ApplicationForm;
use PHPUnit\Framework\TestCase;

final class ApplicationFormTest extends TestCase
{
    /** @return array<string, string> */
    public static function valid(): array
    {
        return [
            'full_name' => '  Ada   Lovelace ', 'email' => 'Ada@Example.org', 'organization' => 'Analytical School',
            'organization_type' => 'institution', 'country' => 'GB', 'role' => 'coordinator',
            'intended_use' => "Weekly timetables for\r\nthirty groups and forty teachers.", 'website' => '', 'privacy' => 'accepted',
        ];
    }

    public function testValidInputIsNormalized(): void
    {
        $f = ApplicationForm::validate(self::valid());
        self::assertTrue($f->isValid(), json_encode($f->errors));
        self::assertSame('Ada Lovelace', $f->values['full_name']);
        self::assertSame('Ada@Example.org', $f->values['email']);
        self::assertSame('ada@example.org', $f->values['email_normalized']);
        self::assertSame("Weekly timetables for\nthirty groups and forty teachers.", $f->values['intended_use']);
        self::assertSame('', $f->values['website']);
    }

    /** @return iterable<string, array{0: string, 1: mixed}> */
    public static function invalidFields(): iterable
    {
        yield 'short name' => ['full_name', 'A'];
        yield 'long name' => ['full_name', str_repeat('x', 121)];
        yield 'control char in name' => ['full_name', "Ada\x1b[31m"];
        yield 'bad email' => ['email', 'not-an-email'];
        yield 'header injection email' => ['email', "a@example.org\r\nBcc: x@example.org"];
        yield 'long email' => ['email', str_repeat('a', 250) . '@e.org'];
        yield 'unknown country' => ['country', 'XX'];
        yield 'lower-case country' => ['country', 'gb'];
        yield 'unknown role' => ['role', 'admin'];
        yield 'unknown org type' => ['organization_type', 'company'];
        yield 'short use' => ['intended_use', 'too short'];
        yield 'long use' => ['intended_use', str_repeat('x', 1001)];
        yield 'javascript url' => ['website', 'javascript:alert(1)'];
        yield 'ftp url' => ['website', 'ftp://example.org'];
        yield 'array value' => ['full_name', ['Ada', 'Lovelace']];
        yield 'invalid utf-8' => ['organization', "School \xC3\x28"];
        yield 'no consent' => ['privacy', ''];
    }

    /** @dataProvider invalidFields */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidFields')]
    public function testInvalidFieldIsRejected(string $field, mixed $value): void
    {
        $input = self::valid();
        $input[$field] = $value;
        $f = ApplicationForm::validate($input);
        self::assertFalse($f->isValid());
        self::assertArrayHasKey($field, $f->errors);
        self::assertArrayNotHasKey($field, $f->values);
    }

    public function testHostileTextIsAcceptedAsDataNotFiltered(): void
    {
        // escaping happens at output; the stored value is exactly what was typed
        $input = self::valid();
        $input['organization'] = "<script>alert(1)</script> O'Brien; DROP TABLE x; --";
        $f = ApplicationForm::validate($input);
        self::assertTrue($f->isValid());
        self::assertSame($input['organization'], $f->values['organization']);
    }
}
