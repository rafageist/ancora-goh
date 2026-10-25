<?php

declare(strict_types=1);

namespace Ancora\Portal\Domain;

use Ancora\Portal\Log\StderrLogger;
use Ancora\Portal\Mail\Mailer;
use Psr\Log\LoggerInterface;

/**
 * Public intake: store a validated request and send the verification email;
 * consume verification tokens. Neither step activates anything: a verified
 * request only becomes "pending_review" for a human operator.
 *
 * submit() always reports the same outcome to the caller, whatever happened
 * to the email (new, duplicate, rate-limited), so responses cannot be used to
 * learn whether an address is known.
 */
final class IntakeService
{
    public const EMAIL_PER_DAY = 3;
    public const RESEND_AFTER_SECONDS = 3600;

    public function __construct(
        private readonly AccessRequestRepository $repo,
        private readonly RateLimiter $limiter,
        private readonly Mailer $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $baseUrl,
    ) {
    }

    /** @param array<string, string> $values validated form values */
    public function submit(array $values): void
    {
        $email = $values['email_normalized'];
        if (!$this->limiter->hit($this->limiter->key('email', $email), self::EMAIL_PER_DAY, 86400)) {
            $this->logger->notice('email rate limit reached; nothing sent', ['email' => StderrLogger::mask($email)]);
            return;
        }
        $existing = $this->repo->findActiveByEmail($email);
        if ($existing !== null) {
            $this->resendIfPending($existing);
            return;
        }
        $db = $this->repo->db();
        $db->beginTransaction();
        try {
            $id = $this->repo->create($values);
            $token = $this->repo->issueToken($id);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
        $this->sendVerification($id, $values['full_name'], $values['email'], $token);
    }

    /** @param array<string, mixed> $row */
    private function resendIfPending(array $row): void
    {
        $pending = in_array($row['status'], [Status::SUBMITTED, Status::EMAIL_VERIFICATION_PENDING], true);
        $recent = $row['email_sent_at'] !== null && strtotime((string) $row['email_sent_at']) > time() - self::RESEND_AFTER_SECONDS;
        if (!$pending || $recent) {
            $this->logger->info('duplicate request for an active address; nothing sent', ['request' => $row['id'], 'status' => $row['status']]);
            return;
        }
        $token = $this->repo->issueToken((string) $row['id']);
        $this->sendVerification((string) $row['id'], (string) $row['full_name'], (string) $row['email'], $token);
    }

    private function sendVerification(string $id, string $name, string $email, string $token): void
    {
        [$subject, $body] = Messages::verification($name, $this->baseUrl . '/verify?token=' . $token);
        if (!$this->mailer->send($email, $subject, $body)) {
            $this->logger->error('verification email not sent', ['request' => $id]);
            return;
        }
        $row = $this->repo->find($id);
        if ($row !== null && $row['status'] === Status::SUBMITTED) {
            $this->repo->transition($id, Status::EMAIL_VERIFICATION_PENDING, 'system', 'mailer', null, ['email_sent_at' => true]);
        } else {
            $this->repo->db()->prepare('UPDATE portal.access_request SET email_sent_at = now() WHERE id = :id')->execute(['id' => $id]);
        }
        $this->logger->info('verification email sent', ['request' => $id]);
    }

    /** Returns true when a token verified a pending request. */
    public function verify(string $token): bool
    {
        $db = $this->repo->db();
        $db->beginTransaction();
        try {
            $id = $this->repo->consumeToken($token);
            $row = $id === null ? null : $this->repo->find($id);
            if ($row === null || $row['status'] !== Status::EMAIL_VERIFICATION_PENDING) {
                $db->rollBack();
                return false;
            }
            $this->repo->transition($id, Status::PENDING_REVIEW, 'applicant', 'email-link', null, ['email_verified_at' => true]);
            $db->commit();
            $this->logger->info('email verified; request waiting for review', ['request' => $id]);
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
