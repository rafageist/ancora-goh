<?php

declare(strict_types=1);

namespace Ancora\Portal\Domain;

use PDO;

/**
 * Fixed-window counters in PostgreSQL. Bucket keys are HMACs of the subject
 * (IP address, email), so raw identifiers are never stored.
 */
final class RateLimiter
{
    public function __construct(private readonly PDO $db, private readonly string $hmacKey)
    {
    }

    public function key(string $kind, string $subject): string
    {
        return $kind . ':' . substr(hash_hmac('sha256', $kind . '|' . $subject, $this->hmacKey), 0, 40);
    }

    /** Count one hit; true while the count is within $limit for the window. */
    public function hit(string $bucket, int $limit, int $windowSeconds): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO portal.rate_limit (bucket, window_start, hits)
             VALUES (:bucket, to_timestamp(floor(extract(epoch FROM now()) / :w1) * :w2), 1)
             ON CONFLICT (bucket, window_start) DO UPDATE SET hits = portal.rate_limit.hits + 1
             RETURNING hits'
        );
        $stmt->execute(['bucket' => $bucket, 'w1' => $windowSeconds, 'w2' => $windowSeconds]);
        return (int) $stmt->fetchColumn() <= $limit;
    }

    public function purge(int $olderThanSeconds): int
    {
        $stmt = $this->db->prepare("DELETE FROM portal.rate_limit WHERE window_start < now() - make_interval(secs => :s)");
        $stmt->execute(['s' => $olderThanSeconds]);
        return $stmt->rowCount();
    }
}
