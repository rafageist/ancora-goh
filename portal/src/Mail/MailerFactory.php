<?php

declare(strict_types=1);

namespace Ancora\Portal\Mail;

use Ancora\Portal\Config;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class MailerFactory
{
    public static function create(Config $config, LoggerInterface $logger): Mailer
    {
        return match ($config->get('PORTAL_MAIL_DRIVER')) {
            'postmark' => new PostmarkMailer($config->secret('PORTAL_POSTMARK_TOKEN'), $config->get('PORTAL_MAIL_FROM'), $logger),
            'outbox' => new OutboxMailer($config->get('PORTAL_OUTBOX_DIR')),
            default => throw new RuntimeException('PORTAL_MAIL_DRIVER must be postmark or outbox'),
        };
    }
}
