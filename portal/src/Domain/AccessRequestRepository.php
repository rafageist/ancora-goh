<?php

declare(strict_types=1);

namespace Ancora\Portal\Domain;

use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * Persistence of access requests, verification tokens and the audit trail.
 * Every status change goes through transition(), which checks the state
 * machine under a row lock and writes an audit event in the same
 * transaction. All values are bound parameters.
 */
final class AccessRequestRepository
{
    public const TOKEN_TTL_SECONDS = 86400;

    public function __construct(private readonly PDO $db)
    {
    }

    public function db(): PDO
    {
        return $this->db;
    }

    /** @param array<string, string> $v validated form values */
    public function create(array $v): string
    {
        $stmt = $this->db->prepare(
            'INSERT INTO portal.access_request
               (status, full_name, email, email_normalized, organization, organization_type,
                country, role, intended_use, website, privacy_version)
             VALUES (:status, :full_name, :email, :email_normalized, :organization, :organization_type,
                     :country, :role, :intended_use, :website, :privacy_version)
             RETURNING id'
        );
        $stmt->execute([
            'status' => Status::SUBMITTED,
            'full_name' => $v['full_name'],
            'email' => $v['email'],
            'email_normalized' => $v['email_normalized'],
            'organization' => $v['organization'],
            'organization_type' => $v['organization_type'],
            'country' => $v['country'],
            'role' => $v['role'],
            'intended_use' => $v['intended_use'],
            'website' => $v['website'] === '' ? null : $v['website'],
            'privacy_version' => \Ancora\Portal\Config::PRIVACY_VERSION,
        ]);
        $id = (string) $stmt->fetchColumn();
        $this->event($id, 'applicant', 'web', null, Status::SUBMITTED, null);
        return $id;
    }

    /** @return array<string, mixed>|null */
    public function find(string $id): ?array
    {
        if (!self::isUuid($id)) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM portal.access_request WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Most recent request for an email that is still active. @return array<string, mixed>|null */
    public function findActiveByEmail(string $normalized): ?array
    {
        $in = implode(',', array_fill(0, count(Status::ACTIVE), '?'));
        $stmt = $this->db->prepare(
            "SELECT * FROM portal.access_request WHERE email_normalized = ? AND status IN ($in)
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute(array_merge([$normalized], Status::ACTIVE));
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function listByStatus(?string $status, int $limit = 50): array
    {
        if ($status !== null && !in_array($status, Status::all(), true)) {
            throw new InvalidArgumentException('unknown status');
        }
        $sql = 'SELECT id, status, full_name, email, organization, country, role, created_at, email_verified_at, decided_at
                FROM portal.access_request'
            . ($status === null ? '' : ' WHERE status = :status')
            . ' ORDER BY created_at LIMIT :limit';
        $stmt = $this->db->prepare($sql);
        if ($status !== null) {
            $stmt->bindValue('status', $status);
        }
        $stmt->bindValue('limit', max(1, min($limit, 500)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Move a request to $to if the state machine allows it; audited.
     * @param array<string, mixed> $extra column => value set with the change (decided_at, ...)
     */
    public function transition(string $id, string $to, string $actorType, string $actor, ?string $reason, array $extra = []): string
    {
        $own = !$this->db->inTransaction();
        if ($own) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare('SELECT status FROM portal.access_request WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $id]);
            $from = $stmt->fetchColumn();
            if ($from === false) {
                throw new RuntimeException('request not found');
            }
            Status::assertTransition((string) $from, $to);
            $set = 'status = :to, updated_at = now()';
            foreach (array_keys($extra) as $col) {
                if (!in_array($col, ['email_sent_at', 'email_verified_at', 'decided_at'], true)) {
                    throw new InvalidArgumentException('column not settable');
                }
                $set .= ", $col = now()";
            }
            $this->db->prepare("UPDATE portal.access_request SET $set WHERE id = :id")->execute(['to' => $to, 'id' => $id]);
            $this->event($id, $actorType, $actor, (string) $from, $to, $reason);
            if ($own) {
                $this->db->commit();
            }
            return (string) $from;
        } catch (\Throwable $e) {
            if ($own && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function event(string $id, string $actorType, string $actor, ?string $from, string $to, ?string $reason): void
    {
        $this->db->prepare(
            'INSERT INTO portal.request_event (request_id, actor_type, actor, from_status, to_status, reason)
             VALUES (:id, :type, :actor, :from, :to, :reason)'
        )->execute(['id' => $id, 'type' => $actorType, 'actor' => $actor, 'from' => $from, 'to' => $to, 'reason' => $reason]);
    }

    /** @return list<array<string, mixed>> */
    public function events(string $id): array
    {
        $stmt = $this->db->prepare('SELECT at, actor_type, actor, from_status, to_status, reason FROM portal.request_event WHERE request_id = :id ORDER BY id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    /** New single-use token for a request; earlier unused tokens are revoked. Returns the plain token. */
    public function issueToken(string $id): string
    {
        $this->db->prepare('DELETE FROM portal.verification_token WHERE request_id = :id AND used_at IS NULL')->execute(['id' => $id]);
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $stmt = $this->db->prepare(
            'INSERT INTO portal.verification_token (token_hash, request_id, expires_at)
             VALUES (:h, :id, now() + make_interval(secs => :ttl))'
        );
        $stmt->bindValue('h', hash('sha256', $token, true), PDO::PARAM_LOB);
        $stmt->bindValue('id', $id);
        $stmt->bindValue('ttl', self::TOKEN_TTL_SECONDS, PDO::PARAM_INT);
        $stmt->execute();
        return $token;
    }

    /**
     * Consume a token: returns the request id if it was valid, unused and
     * unexpired, and marks it used. Constant shape for every failure.
     */
    public function consumeToken(string $token): ?string
    {
        if (!preg_match('/^[A-Za-z0-9_-]{43}$/', $token)) {
            return null;
        }
        $stmt = $this->db->prepare(
            'UPDATE portal.verification_token SET used_at = now()
             WHERE token_hash = :h AND used_at IS NULL AND expires_at > now()
             RETURNING request_id'
        );
        $stmt->bindValue('h', hash('sha256', $token, true), PDO::PARAM_LOB);
        $stmt->execute();
        $id = $stmt->fetchColumn();
        return $id === false ? null : (string) $id;
    }

    public static function isUuid(string $id): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id);
    }
}
