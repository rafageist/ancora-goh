<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/**
 * Reader for the relaxed JSON of the model files.
 *
 * The model files (dev/src/*.json, dev/services/*.json) were written for the
 * Services_JSON decoder of 2010: they start with a comment banner, use
 * unquoted keys and may leave a trailing comma. This reader accepts exactly
 * that superset of JSON:
 *
 *  - "//" line comments and C-style block comments outside strings;
 *  - unquoted keys made of letters, digits and "_";
 *  - single- or double-quoted strings with the JSON escapes;
 *  - a trailing comma before "]" or "}".
 *
 * Objects are returned as associative arrays. Anything else is rejected
 * with a ModelException that names the file and the line.
 */
final class JsonReader
{
    private string $src = '';
    private int $pos = 0;
    private int $len = 0;
    private string $file = '';

    /**
     * @return array<mixed>|string|int|float|bool|null
     */
    public function decode(string $src, string $file = '<string>'): array|string|int|float|bool|null
    {
        $this->src = $src;
        $this->pos = 0;
        $this->len = strlen($src);
        $this->file = $file;

        $this->skip();
        $value = $this->value();
        $this->skip();
        if ($this->pos < $this->len) {
            $this->fail('unexpected text after the end of the document');
        }

        return $value;
    }

    /**
     * @return array<mixed>|string|int|float|bool|null
     */
    private function value(): array|string|int|float|bool|null
    {
        if ($this->pos >= $this->len) {
            $this->fail('unexpected end of the document');
        }
        $c = $this->src[$this->pos];
        if ($c === '{') {
            return $this->members();
        }
        if ($c === '[') {
            return $this->elements();
        }
        if ($c === '"' || $c === "'") {
            return $this->string();
        }
        if (preg_match('/\G-?(0|[1-9][0-9]*)(\.[0-9]+)?([eE][+-]?[0-9]+)?/', $this->src, $m, 0, $this->pos) === 1) {
            $this->pos += strlen($m[0]);
            return (isset($m[2]) && $m[2] !== '') || (isset($m[3]) && $m[3] !== '') ? (float) $m[0] : (int) $m[0];
        }
        foreach (['true' => true, 'false' => false, 'null' => null] as $word => $literal) {
            if (substr_compare($this->src, $word, $this->pos, strlen($word), true) === 0) {
                $this->pos += strlen($word);
                return $literal;
            }
        }
        $this->fail('unexpected character ' . json_encode($c));
    }

    /**
     * @return array<string, mixed>
     */
    private function members(): array
    {
        $object = [];
        $this->pos++;
        $this->skip();
        while ($this->peek() !== '}') {
            $key = $this->key();
            if (array_key_exists($key, $object)) {
                $this->fail("duplicate key \"$key\"");
            }
            $this->skip();
            $this->expect(':');
            $this->skip();
            $object[$key] = $this->value();
            $this->skip();
            if ($this->peek() === ',') {
                $this->pos++;
                $this->skip();
                continue;
            }
            if ($this->peek() !== '}') {
                $this->fail('expected "," or "}"');
            }
        }
        $this->pos++;

        return $object;
    }

    /**
     * @return list<mixed>
     */
    private function elements(): array
    {
        $list = [];
        $this->pos++;
        $this->skip();
        while ($this->peek() !== ']') {
            $list[] = $this->value();
            $this->skip();
            if ($this->peek() === ',') {
                $this->pos++;
                $this->skip();
                continue;
            }
            if ($this->peek() !== ']') {
                $this->fail('expected "," or "]"');
            }
        }
        $this->pos++;

        return $list;
    }

    private function key(): string
    {
        $c = $this->peek();
        if ($c === '"' || $c === "'") {
            return $this->string();
        }
        if (preg_match('/\G[A-Za-z_][A-Za-z0-9_]*/', $this->src, $m, 0, $this->pos) !== 1) {
            $this->fail('expected a key');
        }
        $this->pos += strlen($m[0]);

        return $m[0];
    }

    private function string(): string
    {
        $quote = $this->src[$this->pos++];
        $out = '';
        while (true) {
            if ($this->pos >= $this->len) {
                $this->fail('unterminated string');
            }
            $c = $this->src[$this->pos++];
            if ($c === $quote) {
                return $out;
            }
            if ($c === "\n") {
                $this->fail('line break inside a string');
            }
            if ($c !== '\\') {
                $out .= $c;
                continue;
            }
            $e = $this->src[$this->pos++] ?? '';
            switch ($e) {
                case 'b':
                    $out .= "\x08";
                    break;
                case 't':
                    $out .= "\t";
                    break;
                case 'n':
                    $out .= "\n";
                    break;
                case 'f':
                    $out .= "\f";
                    break;
                case 'r':
                    $out .= "\r";
                    break;
                case '"':
                case "'":
                case '\\':
                case '/':
                    $out .= $e;
                    break;
                case 'u':
                    $hex = substr($this->src, $this->pos, 4);
                    if (preg_match('/^[0-9A-Fa-f]{4}$/', $hex) !== 1) {
                        $this->fail('invalid \\u escape');
                    }
                    $this->pos += 4;
                    $out .= mb_convert_encoding(pack('n', hexdec($hex)), 'UTF-8', 'UTF-16BE');
                    break;
                default:
                    $this->fail('invalid escape \\' . $e);
            }
        }
    }

    /** Skip white space and comments. */
    private function skip(): void
    {
        while ($this->pos < $this->len) {
            $c = $this->src[$this->pos];
            if ($c === ' ' || $c === "\t" || $c === "\n" || $c === "\r") {
                $this->pos++;
                continue;
            }
            if ($c === '/' && ($this->src[$this->pos + 1] ?? '') === '/') {
                $end = strpos($this->src, "\n", $this->pos);
                $this->pos = $end === false ? $this->len : $end + 1;
                continue;
            }
            if ($c === '/' && ($this->src[$this->pos + 1] ?? '') === '*') {
                $end = strpos($this->src, '*/', $this->pos + 2);
                if ($end === false) {
                    $this->fail('unterminated comment');
                }
                $this->pos = $end + 2;
                continue;
            }
            break;
        }
    }

    private function peek(): string
    {
        if ($this->pos >= $this->len) {
            $this->fail('unexpected end of the document');
        }

        return $this->src[$this->pos];
    }

    private function expect(string $c): void
    {
        if ($this->peek() !== $c) {
            $this->fail("expected \"$c\"");
        }
        $this->pos++;
    }

    private function fail(string $message): never
    {
        $line = substr_count($this->src, "\n", 0, min($this->pos, $this->len)) + 1;
        throw new ModelException("{$this->file}:$line: $message");
    }
}
