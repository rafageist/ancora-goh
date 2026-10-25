<?php

declare(strict_types=1);

namespace Divengine\Codegen\Naming;

use Divengine\Codegen\Model\Model;
use Divengine\Codegen\Profile\Profile;

/**
 * Names of the generated classes, methods, packages and UI titles, ported
 * from the cogePHP helpers (capitalLetter, getClassNameOf, dame_carpeta_de,
 * dame_paquete_de, fixFunctionName, getCrudalTitleOf). The project-specific
 * tables come from the profile.
 */
final class Naming
{
    public function __construct(private readonly Profile $profile, private readonly ?Model $model = null)
    {
    }

    /** "dia_de_la_semana" => "DiaDeLaSemana" (only the first letter of each word changes). */
    public static function capitalize(string $text): string
    {
        $s = '';
        foreach (explode('_', $text) as $word) {
            $s .= strtoupper(substr($word, 0, 1)) . substr($word, 1);
        }

        return $s;
    }

    /** "dia_de_la_semana" => "Dia De La Semana". */
    public static function capitalizeWords(string $text): string
    {
        $words = [];
        foreach (explode('_', $text) as $word) {
            $words[] = strtoupper(substr($word, 0, 1)) . substr($word, 1);
        }

        return trim(implode(' ', $words));
    }

    /** "schema." for a schema, "" for the public schema. */
    public static function qualify(string $schema, string $separator = '.'): string
    {
        return $schema !== '' ? $schema . $separator : '';
    }

    /** Two schema names are the same; "" and "public" are the same schema. */
    public static function sameSchema(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        return ($a === 'public' && $b === '') || ($a === '' && $b === 'public');
    }

    /**
     * Class name of a table: the table name, with the schema name added
     * (as a suffix by default) unless the profile ignores that schema.
     */
    public function className(string $schema, string $table): string
    {
        $withSchema = true;
        if (
            $schema === $table
            && $this->profile->get('naming.ignore_schema_equal_to_table', false) === true
            && !$this->publicTableExists($table)
        ) {
            $withSchema = false;
        }
        if (in_array($schema, $this->profile->list('naming.ignore_schema_in_class_name'), true)) {
            return self::capitalize($table);
        }
        $schemaPart = $withSchema ? self::capitalize($schema) : '';
        if ($this->profile->get('naming.schema_as_suffix', true) === false) {
            return $schemaPart . self::capitalize($table);
        }

        return self::capitalize($table) . $schemaPart;
    }

    /**
     * Package of a class ("model.data"): the first profile package that lists
     * the class name or the "{schema}" selector.
     */
    public function package(string $className, string $selector = ''): string
    {
        foreach ($this->profile->list('packages') as $package => $classes) {
            foreach ($classes as $entry) {
                $entry = trim((string) $entry);
                if ($entry === $className || $entry === '{' . $selector . '}') {
                    return (string) $package;
                }
            }
        }

        return $this->profile->string('default_package');
    }

    /** Folder of a class relative to the output root ("model/data"). */
    public function folder(string $className, string $selector = ''): string
    {
        return str_replace('.', '/', $this->package($className, $selector));
    }

    /** PHP method name of a database function; PHP keywords get the profile prefix. */
    public function functionName(string $name): string
    {
        $method = self::capitalize($name);
        if (in_array($name, $this->profile->list('naming.php_keywords'), true)) {
            $method = $this->profile->string('naming.keyword_prefix') . $method;
        }

        return $method;
    }

    /** UI title of a column or table: "fecha_de_inicio" => "Fecha de inicio" (with the spelling table). */
    public function title(string $name): string
    {
        $words = explode('_', $name);
        $words[0] = self::capitalize($words[0]);
        $s = implode(' ', $words) . ' ';

        return trim($this->spell($s));
    }

    private function spell(string $text): string
    {
        foreach ($this->profile->list('ui.spelling') as $search => $replace) {
            $text = str_replace((string) $search, (string) $replace, $text);
        }

        return $text;
    }

    private function publicTableExists(string $name): bool
    {
        // cogePHP intended "a table with this name in the public schema"; its
        // test was an assignment (schema='public') that matched any table and
        // rewrote the schemas. No Áncora table has the name of its schema, so
        // the corrected test does not change the generated code.
        foreach ($this->model?->tables ?? [] as $table) {
            if (($table->schema === '' || $table->schema === 'public') && $table->name === $name) {
                return true;
            }
        }

        return false;
    }
}
