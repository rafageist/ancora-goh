<?php

declare(strict_types=1);

namespace Ancora\Portal\Domain;

use Ancora\Portal\Mail\Mailer;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Human operator decisions (operator CLI). Every decision needs a named
 * operator and a reason, is checked against the state machine and audited.
 * In R0 approval creates no account; it is the input of R1 provisioning.
 */
final class ReviewService
{
    public function __construct(
        private readonly AccessRequestRepository $repo,
        private readonly Mailer $mailer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function approve(string $id, string $operator, string $reason): void
    {
        $this->decide($id, Status::APPROVED, $operator, $reason, fn ($r) => Messages::approved($r['full_name']));
    }

    public function reject(string $id, string $operator, string $reason): void
    {
        $this->decide($id, Status::REJECTED, $operator, $reason, fn ($r) => Messages::rejected($r['full_name']));
    }

    public function requestInformation(string $id, string $operator, string $message): void
    {
        $this->decide($id, Status::MORE_INFORMATION_REQUESTED, $operator, $message, fn ($r) => Messages::moreInformation($r['full_name'], $message));
    }

    public function resumeReview(string $id, string $operator, string $reason): void
    {
        $this->decide($id, Status::PENDING_REVIEW, $operator, $reason, null);
    }

    public function cancel(string $id, string $operator, string $reason): void
    {
        $this->decide($id, Status::CANCELLED, $operator, $reason, null);
    }

    public function suspend(string $id, string $operator, string $reason): void
    {
        $this->decide($id, Status::SUSPENDED, $operator, $reason, null);
    }

    /** @param (callable(array<string, mixed>): array{0: string, 1: string})|null $message */
    private function decide(string $id, string $to, string $operator, string $reason, ?callable $message): void
    {
        $operator = trim($operator);
        $reason = trim($reason);
        // "name" or "name/os-user" (the operator CLI appends the OS account)
        if (!preg_match('#^[A-Za-z0-9._@-]{2,64}(/[A-Za-z0-9._-]{1,64})?$#', $operator)) {
            throw new InvalidArgumentException('operator name required: 2-64 characters, letters, digits and . _ @ -');
        }
        if (mb_strlen($reason) < 3 || mb_strlen($reason) > 1000 || preg_match('/[\x00-\x09\x0B-\x1F\x7F]/', $reason)) {
            throw new InvalidArgumentException('a reason of 3 to 1000 printable characters is required');
        }
        $row = $this->repo->find($id);
        if ($row === null) {
            throw new InvalidArgumentException('request not found');
        }
        $final = in_array($to, [Status::APPROVED, Status::REJECTED], true) ? ['decided_at' => true] : [];
        $this->repo->transition($id, $to, 'operator', $operator, $reason, $final);
        $this->logger->info('operator decision', ['request' => $id, 'to' => $to, 'operator' => $operator]);
        if ($message !== null) {
            [$subject, $body] = $message($row);
            if (!$this->mailer->send((string) $row['email'], $subject, $body)) {
                $this->logger->error('decision email not sent', ['request' => $id]);
                throw new \RuntimeException('the decision is recorded, but its email could not be sent; notify the applicant manually');
            }
        }
    }
}
