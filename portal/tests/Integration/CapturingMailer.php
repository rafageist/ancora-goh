<?php

declare(strict_types=1);

namespace Ancora\Portal\Tests\Integration;

use Ancora\Portal\Mail\Mailer;

/** Test mailer that records messages. */
final class CapturingMailer implements Mailer
{
    /** @var list<array{to: string, subject: string, body: string}> */
    public array $sent = [];
    public bool $fail = false;

    public function send(string $to, string $subject, string $body): bool
    {
        if ($this->fail) {
            return false;
        }
        $this->sent[] = ['to' => $to, 'subject' => $subject, 'body' => $body];
        return true;
    }

    public function lastToken(): string
    {
        preg_match('/token=([A-Za-z0-9_-]{43})/', end($this->sent)['body'], $m);
        return $m[1];
    }
}
