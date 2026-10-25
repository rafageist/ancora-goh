<?php

declare(strict_types=1);

namespace Ancora\Portal\Http;

use Ancora\Portal\Config;
use Ancora\Portal\Database;
use Ancora\Portal\Domain\AccessRequestRepository;
use Ancora\Portal\Domain\ApplicationForm;
use Ancora\Portal\Domain\Countries;
use Ancora\Portal\Domain\IntakeService;
use Ancora\Portal\Domain\RateLimiter;
use Ancora\Portal\Mail\Mailer;
use Ancora\Portal\Mail\MailerFactory;
use Ancora\Portal\View\Renderer;
use PDO;
use Psr\Log\LoggerInterface;

/**
 * The public portal: service description, access-request form, email
 * verification, privacy notice and terms. Nothing else is routed; in
 * particular no legacy application path, login or academic data exists here.
 */
final class App
{
    public const IP_PER_HOUR = 10;
    public const GLOBAL_PER_DAY = 300;
    public const VERIFY_IP_PER_HOUR = 30;
    private const HONEYPOT = 'website_url';

    private ?PDO $db = null;

    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger,
        private readonly Renderer $view,
        private ?Mailer $mailer = null,
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->route($request);
        } catch (\Throwable $e) {
            $this->logger->error('unhandled error', ['type' => get_class($e), 'where' => basename($e->getFile()) . ':' . $e->getLine()]);
            return $this->page('error', ['title' => 'Something went wrong'], 500);
        }
    }

    private function route(Request $r): Response
    {
        $routes = [
            '/' => ['GET' => 'home'],
            '/request' => ['GET' => 'requestForm', 'POST' => 'submit'],
            '/verify' => ['GET' => 'verifyForm', 'POST' => 'verify'],
            '/privacy' => ['GET' => 'privacy'],
            '/terms' => ['GET' => 'terms'],
            '/healthz' => ['GET' => 'health'],
        ];
        if (!isset($routes[$r->path])) {
            return $this->page('not_found', ['title' => 'Page not found'], 404);
        }
        $method = $r->method === 'HEAD' ? 'GET' : $r->method;
        if (!isset($routes[$r->path][$method])) {
            $resp = Response::text('Method not allowed', 405);
            $resp->headers['Allow'] = implode(', ', array_keys($routes[$r->path]));
            return $resp;
        }
        if ($method === 'POST' && !$this->sameOrigin($r)) {
            return $this->page('error', ['title' => 'Request refused'], 403);
        }
        return $this->{$routes[$r->path][$method]}($r);
    }

    private function home(Request $r): Response
    {
        return $this->page('home', ['title' => 'Academic timetables, free']);
    }

    private function privacy(Request $r): Response
    {
        return $this->page('privacy', ['title' => 'Privacy notice', 'version' => Config::PRIVACY_VERSION,
            'controller' => $this->config->get('PORTAL_CONTROLLER_NAME'), 'contact' => $this->config->get('PORTAL_CONTACT_EMAIL')]);
    }

    private function terms(Request $r): Response
    {
        return $this->page('terms', ['title' => 'Terms of the access request', 'contact' => $this->config->get('PORTAL_CONTACT_EMAIL')]);
    }

    private function health(Request $r): Response
    {
        $this->db()->query('SELECT 1');
        return Response::text("ok\n");
    }

    private function requestForm(Request $r, array $errors = [], array $input = [], int $status = 200): Response
    {
        $resp = new Response();
        $cookie = $this->formToken()->ensureCookie($r, $resp);
        $body = $this->view->render('request', [
            'title' => 'Request access',
            'token' => $this->formToken()->issue('request', $cookie),
            'errors' => $errors,
            'input' => $input,
            'roles' => ApplicationForm::ROLES,
            'types' => ApplicationForm::ORGANIZATION_TYPES,
            'countries' => Countries::LIST,
            'honeypot' => self::HONEYPOT,
        ]);
        $resp->body = $body;
        $resp->status = $status;
        return $resp;
    }

    private function submit(Request $r): Response
    {
        $check = $this->formToken()->check('request', $r);
        if ($check !== 'ok') {
            $this->logger->notice('request form refused', ['reason' => $check]);
            return $this->requestForm($r, ['form' => $check === 'too_fast'
                ? 'The form was sent too quickly. Please check your details and send it again.'
                : 'The form expired. Please send it again.'], $r->post, 400);
        }
        $limiter = $this->limiter();
        if (
            !$limiter->hit($limiter->key('ip', $r->clientIp), self::IP_PER_HOUR, 3600)
            || !$limiter->hit('global:request', self::GLOBAL_PER_DAY, 86400)
        ) {
            $this->logger->notice('request rate limit reached');
            return $this->page('rate_limited', ['title' => 'Too many requests'], 429);
        }
        if ($r->input(self::HONEYPOT) !== '') {
            $this->logger->notice('honeypot filled; request discarded');
            return $this->page('submitted', ['title' => 'Check your email']);
        }
        $form = ApplicationForm::validate($r->post);
        if (!$form->isValid()) {
            return $this->requestForm($r, $form->errors, $r->post, 400);
        }
        $this->intake()->submit($form->values);
        return $this->page('submitted', ['title' => 'Check your email']);
    }

    private function verifyForm(Request $r): Response
    {
        $resp = new Response();
        $cookie = $this->formToken()->ensureCookie($r, $resp);
        $resp->body = $this->view->render('verify', [
            'title' => 'Confirm your email',
            'token' => $this->formToken()->issue('verify', $cookie, time() - 3600),
            'verification' => $r->queryString('token'),
        ]);
        return $resp;
    }

    private function verify(Request $r): Response
    {
        if ($this->formToken()->check('verify', $r) !== 'ok') {
            return $this->page('verify_failed', ['title' => 'Link not valid'], 400);
        }
        $limiter = $this->limiter();
        if (!$limiter->hit($limiter->key('verify-ip', $r->clientIp), self::VERIFY_IP_PER_HOUR, 3600)) {
            return $this->page('rate_limited', ['title' => 'Too many requests'], 429);
        }
        if (!$this->intake()->verify($r->input('verification'))) {
            return $this->page('verify_failed', ['title' => 'Link not valid'], 400);
        }
        return $this->page('verified', ['title' => 'Email confirmed']);
    }

    private function sameOrigin(Request $r): bool
    {
        $origin = $r->headers['origin'] ?? null;
        return $origin === null || $origin === $this->config->baseUrl();
    }

    /** @param array<string, mixed> $vars */
    private function page(string $template, array $vars, int $status = 200): Response
    {
        return new Response($this->view->render($template, $vars), $status);
    }

    private function db(): PDO
    {
        return $this->db ??= Database::connect($this->config);
    }

    private function formToken(): FormToken
    {
        return new FormToken(
            $this->config->hmacKey(),
            $this->config->bool('PORTAL_COOKIE_SECURE', true),
            $this->config->int('PORTAL_FORM_MIN_SECONDS', 3),
        );
    }

    private function limiter(): RateLimiter
    {
        return new RateLimiter($this->db(), $this->config->hmacKey());
    }

    private function intake(): IntakeService
    {
        $this->mailer ??= MailerFactory::create($this->config, $this->logger);
        return new IntakeService(new AccessRequestRepository($this->db()), $this->limiter(), $this->mailer, $this->logger, $this->config->baseUrl());
    }
}
