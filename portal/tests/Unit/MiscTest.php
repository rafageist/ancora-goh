<?php

declare(strict_types=1);

namespace Ancora\Portal\Tests\Unit;

use Ancora\Portal\Cli\OperatorCli;
use Ancora\Portal\Config;
use Ancora\Portal\Domain\Status;
use Ancora\Portal\Log\StderrLogger;
use Ancora\Portal\View\Renderer;
use PHPUnit\Framework\TestCase;

final class MiscTest extends TestCase
{
    public function testStateMachineHasNoPathFromVerificationToApprovalWithoutReview(): void
    {
        self::assertFalse(Status::canTransition(Status::SUBMITTED, Status::APPROVED));
        self::assertFalse(Status::canTransition(Status::EMAIL_VERIFICATION_PENDING, Status::APPROVED));
        self::assertTrue(Status::canTransition(Status::EMAIL_VERIFICATION_PENDING, Status::PENDING_REVIEW));
        self::assertTrue(Status::canTransition(Status::PENDING_REVIEW, Status::APPROVED));
        foreach ([Status::REJECTED, Status::EXPIRED, Status::CANCELLED] as $terminal) {
            foreach (Status::all() as $to) {
                self::assertFalse(Status::canTransition($terminal, $to), "$terminal is terminal");
            }
        }
        $this->expectException(\InvalidArgumentException::class);
        Status::assertTransition(Status::REJECTED, Status::APPROVED);
    }

    public function testEscapingCoversTextAndAttributeContexts(): void
    {
        self::assertSame('&lt;script&gt;&quot;x&quot;&apos;&amp;', Renderer::escape('<script>"x"\'&'));
        self::assertSame("\u{FFFD}(", Renderer::escape("\xC3\x28"), 'invalid UTF-8 is substituted, not passed through');
    }

    public function testOperatorOutputStripsTerminalEscapes(): void
    {
        self::assertSame('?[2J?]0;pwned?evil', OperatorCli::clean("\x1b[2J\x1b]0;pwned\x07evil", 100));
        self::assertSame('a?b', OperatorCli::clean("a\u{9B}b", 100));
        self::assertSame('abc…', OperatorCli::clean('abcdef', 3));
    }

    public function testEmailsAreMaskedInLogs(): void
    {
        self::assertSame('a***@example.org', StderrLogger::mask('ada@example.org'));
        $stream = fopen('php://memory', 'w+');
        (new StderrLogger($stream))->info('x', ['obj' => new \stdClass(), 'n' => 1]);
        rewind($stream);
        $line = json_decode((string) stream_get_contents($stream), true);
        self::assertSame(['obj' => 'stdClass', 'n' => 1], $line['ctx']);
    }

    public function testConfigReadsSecretsFromFilesAndValidates(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'sec');
        file_put_contents($file, str_repeat('k', 40) . "\n");
        $c = Config::fromEnvironment(['PORTAL_SECRET_FILE' => $file, 'PORTAL_BASE_URL' => 'https://timetables.divengine.com/']);
        self::assertSame(str_repeat('k', 40), $c->hmacKey());
        self::assertSame('https://timetables.divengine.com', $c->baseUrl());
        unlink($file);
        $this->expectException(\RuntimeException::class);
        Config::fromEnvironment(['PORTAL_SECRET' => 'short'])->hmacKey();
    }
}
