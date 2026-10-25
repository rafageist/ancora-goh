<?php

declare(strict_types=1);

namespace Ancora\Portal\Cli;

use Ancora\Portal\Domain\AccessRequestRepository;
use Ancora\Portal\Domain\RateLimiter;
use Ancora\Portal\Domain\RetentionService;
use Ancora\Portal\Domain\ReviewService;
use Ancora\Portal\Mail\Mailer;
use PDO;
use Psr\Log\LoggerInterface;

/**
 * Operator command line for the R0 review workflow (#53). Runs on the server
 * over SSH; there is no web administration surface. Every decision needs an
 * operator name (--operator or PORTAL_OPERATOR) and a reason, and is audited
 * together with the operating-system user.
 */
final class OperatorCli
{
    private const USAGE = <<<'TXT'
usage: bin/portal COMMAND [options]

  migrate                         create the portal schema (run as the owner role)
  list [--status=S] [--limit=N]   list requests (default: pending_review)
  show ID                         show one request and its audit trail
  approve ID --reason=TEXT        approve (records the decision; creates no account in R0)
  reject ID --reason=TEXT         reject and notify the applicant
  request-info ID --message=TEXT  ask the applicant for more information
  resume ID --reason=TEXT         back to pending_review after more information arrived
  cancel ID --reason=TEXT         cancel a request
  suspend ID --reason=TEXT        suspend an approved request
  purge [--dry-run]               apply the retention rules of the privacy notice

Decisions need --operator=NAME or PORTAL_OPERATOR.
TXT;

    /** @var resource */
    private $out;

    /** @param resource|null $out */
    public function __construct(
        private readonly PDO $db,
        private readonly Mailer $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $hmacKey,
        private readonly string $schemaFile,
        $out = null,
    ) {
        $this->out = $out ?? STDOUT;
    }

    /** @param list<string> $argv without the program name */
    public function run(array $argv, array $env = []): int
    {
        $cmd = array_shift($argv) ?? 'help';
        [$args, $opts] = self::parse($argv);
        $repo = new AccessRequestRepository($this->db);
        $review = new ReviewService($repo, $this->mailer, $this->logger);
        $operator = fn (): string => ($opts['operator'] ?? $env['PORTAL_OPERATOR'] ?? '') . '/' . self::osUser();
        try {
            switch ($cmd) {
                case 'migrate':
                    $this->db->exec((string) file_get_contents($this->schemaFile));
                    $this->say('schema created');
                    return 0;
                case 'list':
                    $rows = $repo->listByStatus($opts['status'] ?? 'pending_review', (int) ($opts['limit'] ?? 50));
                    foreach ($rows as $r) {
                        $this->say(sprintf(
                            '%s  %-28s  %s  %s  %s',
                            $r['id'],
                            $r['status'],
                            substr((string) $r['created_at'], 0, 16),
                            $r['country'],
                            self::clean($r['organization'], 50),
                        ));
                    }
                    $this->say(count($rows) . ' request(s)');
                    return 0;
                case 'show':
                    $r = $repo->find($args[0] ?? '');
                    if ($r === null) {
                        $this->say('request not found');
                        return 1;
                    }
                    foreach ($r as $k => $v) {
                        $this->say(sprintf('%-18s %s', $k, self::clean((string) $v, 2000)));
                    }
                    $this->say('-- audit');
                    foreach ($repo->events($r['id']) as $ev) {
                        $this->say(sprintf(
                            '%s  %-9s %-24s %s -> %s  %s',
                            substr((string) $ev['at'], 0, 19),
                            $ev['actor_type'],
                            self::clean($ev['actor'], 24),
                            $ev['from_status'] ?? '-',
                            $ev['to_status'],
                            self::clean((string) $ev['reason'], 200),
                        ));
                    }
                    return 0;
                case 'approve':
                case 'reject':
                case 'resume':
                case 'cancel':
                case 'suspend':
                    $method = ['approve' => 'approve', 'reject' => 'reject', 'resume' => 'resumeReview', 'cancel' => 'cancel', 'suspend' => 'suspend'][$cmd];
                    $review->$method($args[0] ?? '', $operator(), $opts['reason'] ?? '');
                    $this->say("$cmd: done");
                    return 0;
                case 'request-info':
                    $review->requestInformation($args[0] ?? '', $operator(), $opts['message'] ?? '');
                    $this->say('request-info: done');
                    return 0;
                case 'purge':
                    $res = (new RetentionService($repo, new RateLimiter($this->db, $this->hmacKey)))->purge(isset($opts['dry-run']));
                    $this->say(json_encode($res) . (isset($opts['dry-run']) ? ' (dry run, nothing changed)' : ''));
                    return 0;
                default:
                    $this->say(self::USAGE);
                    return $cmd === 'help' ? 0 : 2;
            }
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->say('error: ' . self::clean($e->getMessage(), 300));
            return 1;
        }
    }

    /**
     * Applicant-supplied text is printed to a terminal: drop control and
     * escape characters so it cannot rewrite the screen or the shell.
     */
    public static function clean(string $s, int $max): string
    {
        $s = preg_replace('/[\x00-\x1F\x7F]|\x{9B}|[\x{80}-\x{9F}]/u', '?', $s) ?? '?';
        return mb_strlen($s) > $max ? mb_substr($s, 0, $max) . '…' : $s;
    }

    /** @return array{0: list<string>, 1: array<string, string>} */
    private static function parse(array $argv): array
    {
        $args = [];
        $opts = [];
        foreach ($argv as $a) {
            if (preg_match('/^--([a-z-]+)(?:=(.*))?$/s', $a, $m)) {
                $opts[$m[1]] = $m[2] ?? '1';
            } else {
                $args[] = $a;
            }
        }
        return [$args, $opts];
    }

    private static function osUser(): string
    {
        $u = function_exists('posix_geteuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? '') : get_current_user();
        return preg_replace('/[^A-Za-z0-9._-]/', '', (string) $u) ?: 'unknown';
    }

    private function say(string $line): void
    {
        fwrite($this->out, $line . "\n");
    }
}
