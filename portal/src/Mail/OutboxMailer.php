<?php

declare(strict_types=1);

namespace Ancora\Portal\Mail;

/**
 * Writes each message to a file in a private directory instead of sending
 * it. For private staging and tests only; never configured in production.
 */
final class OutboxMailer implements Mailer
{
    public function __construct(private readonly string $dir)
    {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        if (!is_dir($this->dir) && !@mkdir($this->dir, 0700, true)) {
            return false;
        }
        $name = sprintf('%s/%s-%s.eml', $this->dir, gmdate('Ymd\THis'), bin2hex(random_bytes(6)));
        $old = umask(077);
        $ok = file_put_contents($name, "To: $to\nSubject: $subject\n\n$body") !== false;
        umask($old);
        return $ok;
    }
}
