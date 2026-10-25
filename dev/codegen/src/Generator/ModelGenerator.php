<?php

declare(strict_types=1);

namespace Divengine\Codegen\Generator;

use Divengine\Codegen\Model\Field;
use Divengine\Codegen\Model\Table;
use Divengine\Codegen\Naming\Naming;

/**
 * Data access layer (the historical cogephp.php): SQL creator and dropper
 * scripts, PartModel, <Schema>DatabaseViews, and for each table the
 * <Class>Model, <Class>Collection and <Class> item classes, and the
 * <Schema>Functions wrappers.
 *
 * This class only prepares the data of each template; the code itself is in
 * templates/model/*.tpl.
 */
final class ModelGenerator
{
    public function __construct(private readonly Context $ctx)
    {
    }

    /**
     * @return list<GeneratedFile>
     */
    public function generate(): array
    {
        $files = [];
        $files[] = $this->sqlScript('creator', $this->ctx->profile->string('output.sql_creator'));
        $files[] = $this->sqlScript('dropper', $this->ctx->profile->string('output.sql_dropper'));
        $files[] = $this->render(
            'model/PartModel.php.tpl',
            $this->ctx->naming->folder('PartModel') . '/PartModel.php',
            [],
        );
        foreach ($this->ctx->model->views as $schema => $views) {
            $files[] = $this->databaseViews((string) $schema, $views);
        }
        foreach ($this->ctx->model->tables as $table) {
            $data = $this->tableData($table);
            $folder = $this->ctx->naming->folder($data['classname'], $table->schema);
            $files[] = $this->render('model/Model.php.tpl', "$folder/{$data['classname']}Model.php", $data);
            $files[] = $this->render('model/Collection.php.tpl', "$folder/{$data['collectionname']}.php", $data);
            $files[] = $this->render('model/Item.php.tpl', "$folder/{$data['classname']}.php", $data);
        }
        foreach ($this->ctx->model->functions as $schema => $functions) {
            $files[] = $this->functions((string) $schema, $functions);
        }

        return $files;
    }

    private function sqlScript(string $kind, string $file): GeneratedFile
    {
        $tables = [];
        $foreignKeys = [];
        foreach ($this->ctx->model->tables as $table) {
            $qname = $this->qualifiedName($table->schema, $table->name);
            $fields = [];
            foreach ($table->fields as $field) {
                $sqlType = $this->ctx->types->sqlType($field->type);
                $fields[] = [
                    'name' => $field->name,
                    'sqlType' => $sqlType,
                    'varchar' => $sqlType === 'varchar',
                    'long' => $field->long,
                    'notNull' => !$field->allowNull,
                    'unique' => $field->unique,
                ];
            }
            $tables[] = ['qname' => $qname, 'pk' => $table->primaryKey, 'fields' => $fields];
            foreach ($table->foreignKeys as $fk) {
                $foreignKeys[] = [
                    'qname' => $qname,
                    'foreignQname' => $this->qualifiedName($fk->foreignSchema, $fk->foreignTable),
                    'locals' => array_map(static fn ($c) => $c->local, $fk->columns),
                    'foreigns' => array_map(static fn ($c) => $c->foreign, $fk->columns),
                    'onDelete' => $fk->onDeleteCascade,
                    'onUpdate' => $fk->onUpdateCascade,
                ];
            }
        }
        $dir = $this->ctx->profile->string('output.sql_dir');

        return $this->render("model/$kind.sql.tpl", "$dir/$file", ['tables' => $tables, 'foreignKeys' => $foreignKeys]);
    }

    /**
     * @param list<\Divengine\Codegen\Model\View> $views
     */
    private function databaseViews(string $schema, array $views): GeneratedFile
    {
        $class = Naming::capitalize($schema) . 'DatabaseViews';
        $items = [];
        foreach ($views as $view) {
            $items[] = ['method' => Naming::capitalize($view->name), 'name' => $view->name];
        }
        $folder = $this->ctx->naming->folder($class, (string) $this->ctx->profile->get('package_selectors.views', ''));

        return $this->render('model/DatabaseViews.php.tpl', "$folder/$class.php", ['class' => $class, 'views' => $items]);
    }

    /**
     * @param list<\Divengine\Codegen\Model\DbFunction> $functions
     */
    private function functions(string $schema, array $functions): GeneratedFile
    {
        $class = Naming::capitalize($schema) . 'Functions';
        $items = [];
        $seen = [];
        foreach ($functions as $function) {
            // overloaded functions: Name, Name_v2, Name_v3, ...
            $method = $this->ctx->naming->functionName($function->name);
            $seen[$method] = ($seen[$method] ?? 0) + 1;
            $params = [];
            foreach ($function->params as $param) {
                $params[] = ['name' => $param->name, 'kind' => $this->ctx->types->kind($param->dataType)];
            }
            $items[] = [
                'name' => $function->name,
                'method' => $method . ($seen[$method] === 1 ? '' : '_v' . $seen[$method]),
                'qname' => Naming::qualify($schema) . $function->name,
                // a function that returns rows of a user-defined type is selected FROM
                'rowset' => $function->return === 'USER-DEFINED',
                'params' => $params,
            ];
        }
        $folder = $this->ctx->naming->folder($class, $schema);

        return $this->render('model/Functions.php.tpl', "$folder/$class.php", [
            'schema' => $schema,
            'class' => $class,
            'functions' => $items,
        ]);
    }

    /**
     * Data of the Model, Collection and Item templates of a table.
     *
     * @return array<string, mixed>
     */
    public function tableData(Table $table): array
    {
        $naming = $this->ctx->naming;
        $classname = $naming->className($table->schema, $table->name);
        $tname = $this->qualifiedName($table->schema, $table->name);

        $fields = [];
        $plain = [];
        $replicas = [];
        $bools = [];
        $insertBinds = [];
        foreach ($table->fields as $field) {
            $fields[] = [
                'name' => $field->name,
                'cap' => Naming::capitalize($field->name),
                'replica' => $field->type === 'replica',
                'md5' => $field->type === 'md5',
                'kind' => $this->ctx->types->kind($field->type),
            ];
            if ($field->type === 'replica') {
                $replicas[] = $field->name;
            } else {
                $plain[] = $field->name;
            }
            if ($field->type === 'bool' || $field->type === 'boolean') {
                $bools[] = $field->name;
            }
            $insertBinds[] = [
                'name' => $field->name,
                'md5' => $field->type === 'md5',
                'kind' => $this->ctx->types->kind($field->type),
            ];
        }

        $pkWhere = [];
        $pkSet = [];
        $pkBinds = [];
        foreach ($table->primaryKey as $i => $column) {
            $pkWhere[] = ['col' => $column, 'n' => $i + 1];
            $pkSet[] = ['col' => $column, 'n' => $i + 2];
            $pkBinds[] = ['col' => $column, 'kind' => $this->ctx->types->kind($this->fieldType($table, $column))];
        }

        return $this->ctx->common() + [
            'classname' => $classname,
            'collectionname' => $classname . 'Collection',
            'tname' => $tname,
            'tdesc' => Naming::qualify($table->schema, '_') . $table->name,
            'fields' => $fields,
            'sqlFields' => array_map(static fn (Field $f) => $f->name, $table->fields),
            'addParams' => $plain,
            'replicaFields' => $replicas,
            // constructor arguments of the item class: plain fields, then replicas
            'ctorFields' => array_merge($plain, $replicas),
            'boolFields' => $bools,
            'hasBool' => $bools !== [],
            'insertBinds' => $insertBinds,
            'hasPk' => $table->primaryKey !== [],
            'pk' => $table->primaryKey,
            'pkCaps' => array_map(Naming::capitalize(...), $table->primaryKey),
            'pkWhere' => $pkWhere,
            'pkSet' => $pkSet,
            'pkBinds' => $pkBinds,
            'pkBindsThis' => $pkBinds,
            'joins' => $this->joins($table, $tname),
            'referencedBy' => $this->referencedBy($table),
        ];
    }

    /**
     * Inner joins along the foreign keys of a table.
     *
     * @return list<array<string, mixed>>
     */
    private function joins(Table $table, string $tname): array
    {
        $joins = [];
        $names = [];
        foreach ($table->foreignKeys as $fk) {
            $tforeign = $this->qualifiedName($fk->foreignSchema, $fk->foreignTable);
            $schema = Naming::capitalize($fk->foreignSchema);
            if ($schema === 'Public') {
                $schema = '';
            }
            // two keys to the same table: getJoinWithX, getJoinWithXA, ...
            $fname = 'getJoinWith' . $schema . $this->ctx->model->prefix . Naming::capitalize($fk->foreignTable);
            while (isset($names[$fname])) {
                $fname .= 'A';
            }
            $names[$fname] = true;

            // a search phrase looks in every column of both tables
            $columns = [];
            foreach ($table->fields as $field) {
                $columns[] = "$tname.{$field->name}";
            }
            foreach ($this->ctx->model->tables as $other) {
                if (Naming::sameSchema($other->schema, $fk->foreignSchema) && $other->name === $fk->foreignTable) {
                    foreach ($other->fields as $field) {
                        $columns[] = "$tforeign.{$field->name}";
                    }
                }
            }
            $joins[] = [
                'fname' => $fname,
                'tforeign' => $tforeign,
                'on' => array_map(static fn ($c) => ['local' => $c->local, 'foreign' => $c->foreign], $fk->columns),
                'phraseColumns' => $columns,
            ];
        }

        return $joins;
    }

    /**
     * The tables whose foreign keys reference this table (add<Table>() and
     * get<Table>() of the item class).
     *
     * @return list<array<string, mixed>>
     */
    private function referencedBy(Table $table): array
    {
        $result = [];
        foreach ($this->ctx->model->tables as $other) {
            if ($other->name === $table->name && $other->schema === $table->schema) {
                continue;
            }
            $fks = [];
            foreach ($other->foreignKeys as $fk) {
                if (Naming::sameSchema($fk->foreignSchema, $table->schema) && $fk->foreignTable === $table->name) {
                    $fks[] = $fk;
                }
            }
            if ($fks === []) {
                continue;
            }
            $locals = [];
            $assigns = [];
            $conds = [];
            foreach ($fks as $fk) {
                foreach ($fk->columns as $c) {
                    $locals[$c->local] = true;
                    $assigns[] = ['local' => $c->local, 'foreign' => $c->foreign];
                    $conds[] = [
                        'local' => $c->local,
                        'foreign' => $c->foreign,
                        'kind' => $this->ctx->types->kind($this->fieldType($other, $c->local)),
                    ];
                }
            }
            $params = [];
            $realParams = [];
            foreach ($other->fields as $field) {
                if ($field->type === 'replica') {
                    continue;
                }
                if (!isset($locals[$field->name])) {
                    $params[] = $field->name;
                }
                $realParams[] = $field->name;
            }
            $result[] = [
                'suffix' => Naming::capitalize($other->schema) . Naming::capitalize($other->name),
                'params' => $params,
                'realParams' => $realParams,
                'assigns' => $assigns,
                'conds' => $conds,
            ];
        }

        return $result;
    }

    private function fieldType(Table $table, string $name): string
    {
        return $table->field($name)?->type ?? 'string';
    }

    private function qualifiedName(string $schema, string $name): string
    {
        return Naming::qualify($schema) . $this->ctx->model->prefix . $name;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function render(string $template, string $path, array $data): GeneratedFile
    {
        return new GeneratedFile($path, $this->ctx->renderer->render($template, $this->ctx->common() + $data));
    }
}
