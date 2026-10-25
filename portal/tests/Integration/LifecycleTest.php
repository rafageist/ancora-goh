<?php

declare(strict_types=1);

namespace Ancora\Portal\Tests\Integration;

use Ancora\Portal\Cli\OperatorCli;
use Ancora\Portal\Database;
use Ancora\Portal\Domain\AccessRequestRepository;
use Ancora\Portal\Domain\IntakeService;
use Ancora\Portal\Domain\RateLimiter;
use Ancora\Portal\Domain\RetentionService;
use Ancora\Portal\Domain\ReviewService;
use Ancora\Portal\Domain\Status;
use Ancora\Portal\Mail\Mailer;
use Ancora\Portal\Tests\Unit\ApplicationFormTest;
use Ancora\Portal\Domain\ApplicationForm;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Runs against PORTAL_TEST_DSN, a disposable database (the e2e launcher and
 * CI create one with dev/sql/tools/with-ephemeral-pg.sh). The portal schema
 * is recreated for every test.
 */
final class LifecycleTest extends TestCase
{
    private const KEY = 'integration-key-integration-key-0123456789';
    private PDO $db;
    private CapturingMailer $mail;
    private IntakeService $intake;
    private ReviewService $review;
    private AccessRequestRepository $repo;

    protected function setUp(): void
    {
        $dsn = getenv('PORTAL_TEST_DSN');
        if (!is_string($dsn) || $dsn === '') {
            self::markTestSkipped('PORTAL_TEST_DSN not set');
        }
        $this->db = Database::fromDsn($dsn);
        $this->db->exec('DROP SCHEMA IF EXISTS portal CASCADE');
        $this->db->exec((string) file_get_contents(__DIR__ . '/../../sql/schema.sql'));
        $this->mail = new CapturingMailer();
        $this->repo = new AccessRequestRepository($this->db);
        $this->intake = new IntakeService($this->repo, new RateLimiter($this->db, self::KEY), $this->mail, new NullLogger(), 'https://t.example.org');
        $this->review = new ReviewService($this->repo, $this->mail, new NullLogger());
    }

    /** @return array<string, string> */
    private function values(string $email = 'ada@example.org'): array
    {
        $input = ApplicationFormTest::valid();
        $input['email'] = $email;
        $f = ApplicationForm::validate($input);
        self::assertTrue($f->isValid());
        return $f->values;
    }

    private function onlyRequest(): array
    {
        $rows = $this->db->query('SELECT * FROM portal.access_request')->fetchAll();
        self::assertCount(1, $rows);
        return $rows[0];
    }

    public function testSubmitVerifyAndApproveNeverActivatesAutomatically(): void
    {
        $this->intake->submit($this->values());
        $r = $this->onlyRequest();
        self::assertSame(Status::EMAIL_VERIFICATION_PENDING, $r['status']);
        self::assertCount(1, $this->mail->sent);
        self::assertStringNotContainsString('ada', strtolower($this->mail->sent[0]['subject']));
        $token = $this->mail->lastToken();
        $hash = $this->db->query('SELECT encode(token_hash, \'hex\') FROM portal.verification_token')->fetchColumn();
        self::assertSame(hash('sha256', $token), $hash, 'only the hash of the token is stored');
        self::assertStringNotContainsString($token, json_encode($this->db->query('SELECT * FROM portal.access_request')->fetchAll()));

        self::assertTrue($this->intake->verify($token));
        self::assertSame(Status::PENDING_REVIEW, $this->onlyRequest()['status'], 'verification never approves');
        self::assertFalse($this->intake->verify($token), 'tokens are single-use');

        $this->review->approve($r['id'], 'maint/root', 'known school');
        $r = $this->onlyRequest();
        self::assertSame(Status::APPROVED, $r['status']);
        self::assertNotNull($r['decided_at']);
        self::assertStringContainsString('No account has been created yet', end($this->mail->sent)['body']);
        $events = $this->repo->events($r['id']);
        self::assertSame(['submitted', 'email_verification_pending', 'pending_review', 'approved'], array_column($events, 'to_status'));
        self::assertSame(['applicant', 'system', 'applicant', 'operator'], array_column($events, 'actor_type'));
        self::assertSame('maint/root', $events[3]['actor']);
        self::assertSame('known school', $events[3]['reason']);
        // the control plane has no account table at all in R0
        self::assertNull($this->db->query("SELECT to_regclass('portal.account')")->fetchColumn());
    }

    public function testOperatorCannotSkipVerificationOrReopenFinalDecisions(): void
    {
        $this->intake->submit($this->values());
        $id = $this->onlyRequest()['id'];
        try {
            $this->review->approve($id, 'maint', 'too early');
            self::fail('approval before verification must be refused');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('not allowed', $e->getMessage());
        }
        $this->intake->verify($this->mail->lastToken());
        $this->review->reject($id, 'maint', 'not an academic use');
        $this->expectException(\InvalidArgumentException::class);
        $this->review->approve($id, 'maint', 'changed my mind');
    }

    public function testDecisionsNeedOperatorAndReason(): void
    {
        $this->intake->submit($this->values());
        $id = $this->onlyRequest()['id'];
        $this->intake->verify($this->mail->lastToken());
        foreach ([['', 'reason ok'], ['/root', 'reason ok'], ['maint', ''], ['maint', "x\x1b[2J"]] as [$op, $reason]) {
            try {
                $this->review->approve($id, $op, $reason);
                self::fail('accepted operator ' . json_encode($op) . ' / reason ' . json_encode($reason));
            } catch (\InvalidArgumentException) {
                self::assertSame(Status::PENDING_REVIEW, $this->onlyRequest()['status']);
            }
        }
    }

    public function testDuplicatesAndEmailRateLimitSendNothingNew(): void
    {
        $this->intake->submit($this->values('ada@example.org'));
        $this->intake->submit($this->values('ADA@example.org'));
        self::assertCount(1, $this->db->query('SELECT id FROM portal.access_request')->fetchAll(), 'same normalized email, one request');
        self::assertCount(1, $this->mail->sent, 'no second email within an hour');
        // after an hour a pending request gets a fresh token; the old one is revoked
        $old = $this->mail->lastToken();
        $this->db->exec("UPDATE portal.access_request SET email_sent_at = now() - interval '2 hours'");
        $this->intake->submit($this->values());
        self::assertCount(2, $this->mail->sent);
        self::assertFalse($this->intake->verify($old), 'the earlier token was revoked');
        self::assertTrue($this->intake->verify($this->mail->lastToken()));
        // fourth submission in a day for that address: limited, nothing sent
        $this->db->exec("UPDATE portal.access_request SET status = 'cancelled'");
        $this->intake->submit($this->values());
        self::assertCount(2, $this->mail->sent);
        self::assertCount(1, $this->db->query('SELECT id FROM portal.access_request')->fetchAll());
        $buckets = $this->db->query('SELECT bucket FROM portal.rate_limit')->fetchAll(PDO::FETCH_COLUMN);
        self::assertStringNotContainsString('ada', implode(' ', $buckets), 'buckets hold HMACs, not addresses');
    }

    public function testExpiredAndForgedTokensFail(): void
    {
        $this->intake->submit($this->values());
        $token = $this->mail->lastToken();
        self::assertFalse($this->intake->verify(str_repeat('A', 43)));
        self::assertFalse($this->intake->verify("' OR '1'='1"));
        $this->db->exec("UPDATE portal.verification_token SET expires_at = now() - interval '1 second'");
        self::assertFalse($this->intake->verify($token));
        self::assertSame(Status::EMAIL_VERIFICATION_PENDING, $this->onlyRequest()['status']);
    }

    public function testMailFailureLeavesTheRequestSubmitted(): void
    {
        $this->mail->fail = true;
        $this->intake->submit($this->values());
        self::assertSame(Status::SUBMITTED, $this->onlyRequest()['status']);
    }

    public function testHostileValuesAreStoredLiterally(): void
    {
        $v = $this->values();
        $v['organization'] = "x'); DROP TABLE portal.access_request; --";
        $v['full_name'] = '<img src=x onerror=alert(1)>';
        $this->intake->submit($v);
        $r = $this->onlyRequest();
        self::assertSame($v['organization'], $r['organization']);
        self::assertSame($v['full_name'], $r['full_name']);
    }

    public function testRetentionExpiresDeletesAndKeepsAnonymousAudit(): void
    {
        $this->intake->submit($this->values('old@example.org'));
        $this->intake->submit($this->values('new@example.org'));
        $this->db->exec("UPDATE portal.access_request SET created_at = now() - interval '2 days' WHERE email_normalized = 'old@example.org'");
        $retention = new RetentionService($this->repo, new RateLimiter($this->db, self::KEY));
        self::assertSame(1, $retention->purge(true)['expired']);
        self::assertSame(0, (int) $this->db->query("SELECT count(*) FROM portal.access_request WHERE status = 'expired'")->fetchColumn(), 'dry run');
        $retention->purge();
        $old = $this->db->query("SELECT id, status FROM portal.access_request WHERE email_normalized = 'old@example.org'")->fetch();
        self::assertSame('expired', $old['status']);
        $this->db->exec("UPDATE portal.access_request SET updated_at = now() - interval '8 days' WHERE id = '{$old['id']}'");
        self::assertSame(1, $retention->purge()['deleted']);
        self::assertSame(['new@example.org'], $this->db->query('SELECT email_normalized FROM portal.access_request')->fetchAll(PDO::FETCH_COLUMN));
        $events = $this->repo->events($old['id']);
        self::assertSame('deleted', end($events)['to_status']);
        self::assertStringNotContainsString('old@example.org', json_encode($events));
    }

    public function testOperatorCliShowsSanitizedDataAndRecordsTheOsUser(): void
    {
        $v = $this->values();
        $v['organization'] = "Evil\x1b]0;title\x07 School";
        $this->intake->submit($v);
        $this->intake->verify($this->mail->lastToken());
        $id = $this->onlyRequest()['id'];
        $out = fopen('php://memory', 'w+');
        $cli = new OperatorCli($this->db, $this->mail, new NullLogger(), self::KEY, __DIR__ . '/../../sql/schema.sql', $out);
        self::assertSame(0, $cli->run(['list']));
        self::assertSame(1, $cli->run(['approve', $id, '--reason=ok school']), 'operator name required');
        self::assertSame(0, $cli->run(['approve', $id, '--operator=maint', '--reason=ok school']));
        self::assertSame(0, $cli->run(['show', $id]));
        rewind($out);
        $text = (string) stream_get_contents($out);
        self::assertStringNotContainsString("\x1b", $text);
        self::assertStringContainsString('Evil?]0;title? School', $text);
        self::assertMatchesRegularExpression('#operator\s+maint/[A-Za-z0-9._-]+#', $text);
    }

    public function testRuntimeRoleHasOnlyTheGrantedPrivileges(): void
    {
        $role = 'tt_control_app_test';
        $this->db->exec("DROP ROLE IF EXISTS $role");
        $this->db->exec("CREATE ROLE $role LOGIN");
        $grants = str_replace(':"app_role"', $role, (string) file_get_contents(__DIR__ . '/../../sql/grants.sql'));
        $this->db->exec($grants);
        $this->db->exec("SET ROLE $role");
        try {
            $repo = new AccessRequestRepository($this->db);
            $intake = new IntakeService($repo, new RateLimiter($this->db, self::KEY), $this->mail, new NullLogger(), 'https://t.example.org');
            $intake->submit($this->values());
            self::assertTrue($intake->verify($this->mail->lastToken()), 'the runtime role can run the whole intake');
            foreach (
                [
                'CREATE TABLE portal.x (i int)', 'DROP TABLE portal.rate_limit', 'UPDATE portal.request_event SET reason = NULL',
                'DELETE FROM portal.request_event', 'ALTER TABLE portal.access_request ADD COLUMN y int', 'TRUNCATE portal.access_request',
                ] as $sql
            ) {
                try {
                    $this->db->exec($sql);
                    self::fail("runtime role may run: $sql");
                } catch (\PDOException $e) {
                    self::assertSame('42501', $e->getCode(), $sql);
                }
            }
        } finally {
            $this->db->exec('RESET ROLE');
            $this->db->exec('DROP SCHEMA portal CASCADE');
            $this->db->exec("DROP ROLE $role");
        }
    }
}
