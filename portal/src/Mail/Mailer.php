<?php

declare(strict_types=1);

namespace Ancora\Portal\Mail;

/** Sends a plain-text message; returns false on failure (never throws secrets). */
interface Mailer
{
    public function send(string $to, string $subject, string $body): bool;
}
