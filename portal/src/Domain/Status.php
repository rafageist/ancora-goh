<?php

declare(strict_types=1);

namespace Ancora\Portal\Domain;

use InvalidArgumentException;

/**
 * Access-request states and the only allowed transitions (#40, #50).
 * No transition creates an account: in R0, "approved" records a human
 * decision only; accounts are provisioned in R1 by a separate operator step.
 */
final class Status
{
    public const SUBMITTED = 'submitted';
    public const EMAIL_VERIFICATION_PENDING = 'email_verification_pending';
    public const PENDING_REVIEW = 'pending_review';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const MORE_INFORMATION_REQUESTED = 'more_information_requested';
    public const EXPIRED = 'expired';
    public const CANCELLED = 'cancelled';
    public const SUSPENDED = 'suspended';

    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        self::SUBMITTED => [self::EMAIL_VERIFICATION_PENDING, self::EXPIRED, self::CANCELLED],
        self::EMAIL_VERIFICATION_PENDING => [self::PENDING_REVIEW, self::EXPIRED, self::CANCELLED],
        self::PENDING_REVIEW => [self::APPROVED, self::REJECTED, self::MORE_INFORMATION_REQUESTED, self::CANCELLED],
        self::MORE_INFORMATION_REQUESTED => [self::PENDING_REVIEW, self::REJECTED, self::CANCELLED, self::EXPIRED],
        self::APPROVED => [self::SUSPENDED],
        self::SUSPENDED => [self::APPROVED],
        self::REJECTED => [],
        self::EXPIRED => [],
        self::CANCELLED => [],
    ];

    /** States in which a new request for the same email is not created. */
    public const ACTIVE = [
        self::SUBMITTED, self::EMAIL_VERIFICATION_PENDING, self::PENDING_REVIEW,
        self::MORE_INFORMATION_REQUESTED, self::APPROVED, self::SUSPENDED,
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::TRANSITIONS);
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function assertTransition(string $from, string $to): void
    {
        if (!isset(self::TRANSITIONS[$from]) || !isset(self::TRANSITIONS[$to])) {
            throw new InvalidArgumentException('unknown status');
        }
        if (!self::canTransition($from, $to)) {
            throw new InvalidArgumentException("transition $from -> $to is not allowed");
        }
    }
}
