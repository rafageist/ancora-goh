<?php

declare(strict_types=1);

namespace Ancora\Portal\Domain;

/**
 * Retention rules of the privacy notice:
 * - an unverified request expires after 24 hours and is deleted after 7 days;
 * - a rejected, cancelled or expired request is deleted 90 days after its
 *   last change (7 days for never-verified ones);
 * - approved and suspended requests are kept while access may be granted.
 * Audit events (no personal data) are kept and record the deletion.
 */
final class RetentionService
{
    public function __construct(private readonly AccessRequestRepository $repo, private readonly RateLimiter $limiter)
    {
    }

    /** @return array{expired: int, deleted: int, tokens: int, rate_windows: int} */
    public function purge(bool $dryRun = false): array
    {
        $db = $this->repo->db();
        $db->beginTransaction();
        $expired = 0;
        $stmt = $db->query(
            "SELECT id FROM portal.access_request
             WHERE status IN ('submitted', 'email_verification_pending') AND created_at < now() - interval '24 hours'"
        );
        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $id) {
            $this->repo->transition((string) $id, Status::EXPIRED, 'system', 'retention', 'email not verified within 24 hours');
            $expired++;
        }
        $doomed = $db->query(
            "SELECT id FROM portal.access_request
             WHERE (status = 'expired' AND email_verified_at IS NULL AND updated_at < now() - interval '7 days')
                OR (status IN ('rejected', 'cancelled', 'expired') AND updated_at < now() - interval '90 days')"
        )->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($doomed as $id) {
            $this->repo->event((string) $id, 'system', 'retention', null, 'deleted', 'personal data deleted by the retention policy');
            $db->prepare('DELETE FROM portal.access_request WHERE id = :id')->execute(['id' => $id]);
        }
        $tokens = $db->exec("DELETE FROM portal.verification_token WHERE expires_at < now() - interval '1 day'");
        $windows = $this->limiter->purge(2 * 86400);
        if ($dryRun) {
            $db->rollBack();
        } else {
            $db->commit();
        }
        return ['expired' => $expired, 'deleted' => count($doomed), 'tokens' => (int) $tokens, 'rate_windows' => $windows];
    }
}
