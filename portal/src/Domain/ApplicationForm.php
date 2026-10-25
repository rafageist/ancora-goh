<?php

declare(strict_types=1);

namespace Ancora\Portal\Domain;

/**
 * Validation of the public request form. Only the fields documented in the
 * privacy notice are accepted; every value is length-limited and normalized.
 */
final class ApplicationForm
{
    public const ROLES = [
        'teacher' => 'Teacher',
        'coordinator' => 'Timetable or academic coordinator',
        'administrator' => 'School or department administrator',
        'it' => 'IT staff',
        'researcher' => 'Researcher',
        'student' => 'Student',
        'developer' => 'Independent developer',
        'other' => 'Other',
    ];

    public const ORGANIZATION_TYPES = [
        'institution' => 'Educational institution',
        'independent' => 'Independent or personal project',
    ];

    /** @var array<string, string> normalized values */
    public array $values = [];

    /** @var array<string, string> field => message */
    public array $errors = [];

    /** @param array<string, mixed> $input raw POST */
    public static function validate(array $input): self
    {
        $f = new self();
        $f->text($input, 'full_name', 2, 120, 'Enter your name (2 to 120 characters).');
        $f->email($input);
        $f->text($input, 'organization', 2, 160, 'Enter your organization or project (2 to 160 characters).');
        $f->choice($input, 'organization_type', self::ORGANIZATION_TYPES, 'Choose the kind of organization.');
        $f->choice($input, 'country', Countries::LIST, 'Choose a country or region.');
        $f->choice($input, 'role', self::ROLES, 'Choose your role.');
        $f->text($input, 'intended_use', 20, 1000, 'Describe the intended use (20 to 1000 characters).', true);
        $f->website($input);
        if (($input['privacy'] ?? '') !== 'accepted') {
            $f->errors['privacy'] = 'You must accept the privacy notice to send a request.';
        }
        return $f;
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /** Raw value of a field for redisplay (string only, never arrays). */
    public static function raw(array $input, string $name): string
    {
        $v = $input[$name] ?? '';
        return is_string($v) ? $v : '';
    }

    private function text(array $input, string $name, int $min, int $max, string $msg, bool $multiline = false): void
    {
        $v = self::raw($input, $name);
        if (!mb_check_encoding($v, 'UTF-8')) {
            $this->errors[$name] = $msg;
            return;
        }
        $v = $multiline
            ? trim(preg_replace("/\r\n?/", "\n", $v))
            : trim(preg_replace('/\s+/u', ' ', $v));
        // control characters (except newline in multiline fields) are never stored
        $pattern = $multiline ? '/[\x00-\x09\x0B-\x1F\x7F\x{80}-\x{9F}]/u' : '/[\x00-\x1F\x7F\x{80}-\x{9F}]/u';
        $len = mb_strlen($v);
        if ($len < $min || $len > $max || preg_match($pattern, $v)) {
            $this->errors[$name] = $msg;
            return;
        }
        $this->values[$name] = $v;
    }

    private function email(array $input): void
    {
        $v = trim(self::raw($input, 'email'));
        if (strlen($v) > 254 || filter_var($v, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors['email'] = 'Enter a valid email address.';
            return;
        }
        $this->values['email'] = $v;
        $this->values['email_normalized'] = strtolower($v);
    }

    /** @param array<string, string> $options */
    private function choice(array $input, string $name, array $options, string $msg): void
    {
        $v = self::raw($input, $name);
        if (!array_key_exists($v, $options)) {
            $this->errors[$name] = $msg;
            return;
        }
        $this->values[$name] = $v;
    }

    private function website(array $input): void
    {
        $v = trim(self::raw($input, 'website'));
        if ($v === '') {
            $this->values['website'] = '';
            return;
        }
        $ok = strlen($v) <= 200
            && filter_var($v, FILTER_VALIDATE_URL) !== false
            && preg_match('#^https?://#i', $v);
        if (!$ok) {
            $this->errors['website'] = 'Enter a web address starting with http:// or https://, or leave it empty.';
            return;
        }
        $this->values['website'] = $v;
    }
}
