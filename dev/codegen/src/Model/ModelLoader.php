<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

use Divengine\Codegen\Profile\Profile;

/**
 * Loads and validates the JSON model (a main file that lists one file per
 * table, view and function) and the service models.
 *
 * Validation happens here, before any template is rendered: a model with a
 * missing name, an unknown field type, a name that is not a valid SQL/PHP
 * identifier, a duplicate table, a primary or foreign key on a column that
 * does not exist, or a character the templates cannot carry is rejected
 * with a ModelException that names the file.
 */
final class ModelLoader
{
    /** SQL and PHP identifier: letters, digits and "_", not starting with a digit. */
    public const IDENTIFIER = '/^[A-Za-z_][A-Za-z0-9_]*$/';

    /** A database type name as written by the introspector ("double precision", "USER-DEFINED"). */
    private const TYPE_NAME = '/^"?[A-Za-z][A-Za-z0-9_ -]*"?$/';

    /** A model file name: no directory part. */
    private const FILE_NAME = '/^[A-Za-z0-9_][A-Za-z0-9_.-]*\.json$/';

    private JsonReader $reader;

    /** @var array<string, true> */
    private array $knownTypes = [];

    public function __construct(private readonly Profile $profile)
    {
        $this->reader = new JsonReader();
        foreach (array_keys($profile->list('types.quoted')) as $type) {
            $this->knownTypes[(string) $type] = true;
        }
        foreach ($profile->list('types.other') as $type) {
            $this->knownTypes[(string) $type] = true;
        }
    }

    public function load(string $dir, string $file): Model
    {
        $main = $this->readObject($dir, $file);
        $data = $this->object($main, 'data', $file);
        $prefix = $this->string($data, 'prefix', $file, true);
        if ($prefix !== '' && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $prefix) !== 1) {
            throw new ModelException("$file: data.prefix must be an identifier or empty");
        }

        $tables = [];
        $seen = [];
        foreach ($this->fileList($data, 'tabledefs', $file) as $name) {
            $table = $this->table($this->readObject($dir, $name), $name);
            $key = ($table->schema === 'public' ? '' : $table->schema) . '.' . $table->name;
            if (isset($seen[$key])) {
                throw new ModelException("$name: duplicate table " . ltrim($key, '.') . " (also in {$seen[$key]})");
            }
            $seen[$key] = $name;
            $tables[] = $table;
        }

        $views = [];
        foreach ($this->fileList($data, 'viewdefs', $file, true) as $name) {
            $view = $this->view($this->readObject($dir, $name), $name);
            $views[$view->schema][] = $view;
        }

        $functions = [];
        foreach ($this->fileList($data, 'functiondefs', $file, true) as $name) {
            $function = $this->dbFunction($this->readObject($dir, $name), $name);
            $functions[$function->schema][] = $function;
        }

        if (!empty($data['services_defs'])) {
            throw new ModelException("$file: data.services_defs is not supported; put service models in the services directory");
        }

        return new Model($prefix, $tables, $views, $functions);
    }

    /**
     * Service models: every *.json file of $dir, in name order.
     *
     * @return list<Service>
     */
    public function loadServices(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob(rtrim($dir, '/') . '/*.json') ?: [];
        sort($files, SORT_STRING);
        $services = [];
        $names = [];
        foreach ($files as $path) {
            $name = basename($path);
            $service = $this->service($this->readObject($dir, $name), $name);
            if (isset($names[$service->name])) {
                throw new ModelException("$name: duplicate service {$service->name} (also in {$names[$service->name]})");
            }
            $names[$service->name] = $name;
            $services[] = $service;
        }

        return $services;
    }

    /**
     * @param array<string, mixed> $json
     */
    private function table(array $json, string $file): Table
    {
        $schema = $this->schema($json, $file);
        $name = $this->identifier($json, 'name', $file);
        $fields = [];
        foreach ($this->listOf($json, 'fields', $file) as $i => $f) {
            $where = "$file: fields[$i]";
            if (!is_array($f)) {
                throw new ModelException("$where must be an object");
            }
            $fieldName = $this->identifier($f, 'name', $where);
            if (isset($fields[$fieldName])) {
                throw new ModelException("$file: duplicate field $fieldName");
            }
            $type = $this->string($f, 'type', $where);
            if (!isset($this->knownTypes[$type])) {
                throw new ModelException("$where: unknown type \"$type\" of field $fieldName");
            }
            $long = $f['long'] ?? 255;
            if (!is_int($long) || $long < 0) {
                throw new ModelException("$where: long must be a non-negative integer");
            }
            $fields[$fieldName] = new Field(
                $fieldName,
                $type,
                $long,
                $this->bool($f, 'allownull', $where),
                $this->bool($f, 'unique', $where),
            );
        }
        if ($fields === []) {
            throw new ModelException("$file: table $name has no fields");
        }

        $primaryKey = [];
        foreach ($this->listOf($json, 'primary_key', $file) as $column) {
            if (!is_string($column) || !isset($fields[$column])) {
                throw new ModelException("$file: primary key column " . json_encode($column) . " is not a field of $name");
            }
            $primaryKey[] = $column;
        }

        $foreignKeys = [];
        foreach ($this->listOf($json, 'foreign_keys', $file) as $i => $fk) {
            $where = "$file: foreign_keys[$i]";
            if (!is_array($fk)) {
                throw new ModelException("$where must be an object");
            }
            $columns = [];
            foreach ($this->listOf($fk, 'fields', $where) as $c) {
                if (!is_array($c)) {
                    throw new ModelException("$where: fields must be objects");
                }
                $local = $this->identifier($c, 'localfield', $where);
                if (!isset($fields[$local])) {
                    throw new ModelException("$where: $local is not a field of $name");
                }
                $columns[] = new ForeignKeyColumn($local, $this->identifier($c, 'foreign_key', $where));
            }
            if ($columns === []) {
                throw new ModelException("$where: no columns");
            }
            $foreignSchema = $this->string($fk, 'foreign_schema', $where, true);
            if ($foreignSchema !== '' && preg_match(self::IDENTIFIER, $foreignSchema) !== 1) {
                throw new ModelException("$where: invalid foreign_schema " . json_encode($foreignSchema));
            }
            $foreignKeys[] = new ForeignKey(
                $columns,
                $foreignSchema,
                $this->identifier($fk, 'foreign_table', $where),
                $this->bool($fk, 'on_delete_cascade', $where),
                $this->bool($fk, 'on_update_cascade', $where),
            );
        }

        return new Table($schema, $name, array_values($fields), $primaryKey, $foreignKeys);
    }

    /**
     * @param array<string, mixed> $json
     */
    private function view(array $json, string $file): View
    {
        return new View(
            $this->schema($json, $file),
            $this->identifier($json, 'name', $file),
            $this->string($json, 'SQL', $file, true),
        );
    }

    /**
     * @param array<string, mixed> $json
     */
    private function dbFunction(array $json, string $file): DbFunction
    {
        $params = [];
        foreach ($this->listOf($json, 'params', $file, true) as $i => $p) {
            $where = "$file: params[$i]";
            if (!is_array($p)) {
                throw new ModelException("$where must be an object");
            }
            $name = $this->string($p, 'name', $where, true);
            if ($name === '') {
                // unnamed parameters were named by position (cogePHP)
                $name = 'param' . ($i + 1);
            } elseif (preg_match(self::IDENTIFIER, $name) !== 1) {
                throw new ModelException("$where: invalid parameter name " . json_encode($name));
            }
            $type = $this->string($p, 'data_type', $where);
            if (preg_match(self::TYPE_NAME, $type) !== 1) {
                throw new ModelException("$where: invalid data_type " . json_encode($type));
            }
            $params[] = new FunctionParam($name, $type, $this->string($p, 'mode', $where, true));
        }
        $return = $this->string($json, 'return', $file, true);
        if ($return !== '' && preg_match(self::TYPE_NAME, $return) !== 1) {
            throw new ModelException("$file: invalid return type " . json_encode($return));
        }

        return new DbFunction($this->schema($json, $file), $this->identifier($json, 'name', $file), $return, $params);
    }

    /**
     * @param array<string, mixed> $json
     */
    private function service(array $json, string $file): Service
    {
        $methods = [];
        foreach ($this->listOf($json, 'methods', $file) as $i => $m) {
            $where = "$file: methods[$i]";
            if (!is_array($m)) {
                throw new ModelException("$where must be an object");
            }
            $params = [];
            foreach ($this->listOf($m, 'params', $where, true) as $param) {
                if (!is_string($param) || preg_match(self::IDENTIFIER, $param) !== 1) {
                    throw new ModelException("$where: invalid parameter " . json_encode($param));
                }
                $params[] = $param;
            }
            $methods[] = new ServiceMethod(
                $this->identifier($m, 'name', $where),
                $this->string($m, 'descrip', $where, true),
                $params,
                $this->string($m, 'SQL', $where),
            );
        }

        return new Service($this->identifier($json, 'name', $file), $this->string($json, 'description', $file, true), $methods);
    }

    /**
     * @return array<string, mixed>
     */
    private function readObject(string $dir, string $file): array
    {
        if (preg_match(self::FILE_NAME, $file) !== 1) {
            throw new ModelException("invalid model file name " . json_encode($file));
        }
        $path = rtrim($dir, '/') . '/' . $file;
        if (!is_file($path)) {
            throw new ModelException("model file not found: $path");
        }
        $src = (string) file_get_contents($path);
        if (str_contains($src, '`')) {
            // the backtick delimits the Div tags of the templates (Template\Dialect)
            throw new ModelException("$file: the model must not contain a backtick (`)");
        }
        $json = $this->reader->decode($src, $file);
        if (!is_array($json) || array_is_list($json)) {
            throw new ModelException("$file: the document must be an object");
        }

        return $json;
    }

    /**
     * @param array<string, mixed> $json
     * @return list<string>
     */
    private function fileList(array $json, string $key, string $file, bool $optional = false): array
    {
        $files = [];
        foreach ($this->listOf($json, $key, $file, $optional) as $name) {
            if (!is_string($name)) {
                throw new ModelException("$file: $key must list file names");
            }
            $files[] = $name;
        }

        return $files;
    }

    /**
     * @param array<string, mixed> $json
     * @return list<mixed>
     */
    private function listOf(array $json, string $key, string $file, bool $optional = false): array
    {
        if (!array_key_exists($key, $json) || $json[$key] === null) {
            if ($optional) {
                return [];
            }
            throw new ModelException("$file: missing $key");
        }
        if (!is_array($json[$key]) || !array_is_list($json[$key])) {
            throw new ModelException("$file: $key must be a list");
        }

        return $json[$key];
    }

    /**
     * @param array<string, mixed> $json
     * @return array<string, mixed>
     */
    private function object(array $json, string $key, string $file): array
    {
        if (!isset($json[$key]) || !is_array($json[$key]) || (array_is_list($json[$key]) && $json[$key] !== [])) {
            throw new ModelException("$file: missing object $key");
        }

        return $json[$key];
    }

    /**
     * @param array<string, mixed> $json
     */
    private function string(array $json, string $key, string $file, bool $allowEmpty = false): string
    {
        if (!array_key_exists($key, $json)) {
            throw new ModelException("$file: missing $key");
        }
        if (!is_string($json[$key])) {
            throw new ModelException("$file: $key must be a string");
        }
        if (!$allowEmpty && $json[$key] === '') {
            throw new ModelException("$file: $key must not be empty");
        }

        return $json[$key];
    }

    /**
     * @param array<string, mixed> $json
     */
    private function identifier(array $json, string $key, string $file): string
    {
        $value = $this->string($json, $key, $file);
        if (preg_match(self::IDENTIFIER, $value) !== 1) {
            throw new ModelException("$file: $key " . json_encode($value) . ' is not a valid SQL/PHP identifier');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $json
     */
    private function schema(array $json, string $file): string
    {
        $schema = $this->string($json, 'schema', $file, true);
        if ($schema !== '' && preg_match(self::IDENTIFIER, $schema) !== 1) {
            throw new ModelException("$file: schema " . json_encode($schema) . ' is not a valid identifier');
        }

        return $schema;
    }

    /**
     * @param array<string, mixed> $json
     */
    private function bool(array $json, string $key, string $file): bool
    {
        if (!array_key_exists($key, $json) || !is_bool($json[$key])) {
            throw new ModelException("$file: $key must be true or false");
        }

        return $json[$key];
    }
}
