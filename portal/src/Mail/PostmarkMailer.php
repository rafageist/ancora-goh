<?php

declare(strict_types=1);

namespace Ancora\Portal\Mail;

use Psr\Log\LoggerInterface;

/**
 * Transactional email over the Postmark HTTPS API (outbound SMTP is blocked
 * on the DigitalOcean host). The server token is read from a secret file and
 * is never logged. Other HTTPS providers can implement Mailer the same way.
 */
final class PostmarkMailer implements Mailer
{
    private const ENDPOINT = 'https://api.postmarkapp.com/email';

    public function __construct(
        private readonly string $token,
        private readonly string $from,
        private readonly LoggerInterface $logger,
        private readonly string $endpoint = self::ENDPOINT,
    ) {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $payload = json_encode([
            'From' => $this->from,
            'To' => $to,
            'Subject' => $subject,
            'TextBody' => $body,
            'MessageStream' => 'outbound',
            'TrackOpens' => false,
            'TrackLinks' => 'None',
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'X-Postmark-Server-Token: ' . $this->token,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_errno($ch);
        if ($response === false || $status !== 200) {
            $this->logger->error('mail provider rejected a message', ['http_status' => $status, 'curl_error' => $error]);
            return false;
        }
        return true;
    }
}
