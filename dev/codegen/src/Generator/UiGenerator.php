<?php

declare(strict_types=1);

namespace Divengine\Codegen\Generator;

use Divengine\Codegen\Model\Table;

/**
 * User interface layer (the historical cogephpface.php): for each table the
 * gallery, grid and combo views, the gallery and combo controllers and the
 * add/set/del handlers. Only prepares data; the code is in templates/ui/*.tpl.
 */
final class UiGenerator
{
    public function __construct(private readonly Context $ctx)
    {
    }

    /**
     * @return list<GeneratedFile>
     */
    public function generate(): array
    {
        $out = $this->ctx->profile->list('output');
        $files = [];
        foreach ($this->ctx->model->tables as $table) {
            $data = $this->tableData($table);
            $c = $data['classname'];
            $files[] = $this->render('ui/gallery.tpl.tpl', "{$out['gallery_view']}/gallery_$c.tpl", $data);
            $files[] = $this->render('ui/grid.tpl.tpl', "{$out['grid_view']}/grid_$c.tpl", $data);
            $files[] = $this->render('ui/gallery-controller.php.tpl', "{$out['gallery_controller']}/get_ui_gallery_$c.php", $data);
            $files[] = $this->render('ui/combo.tpl.tpl', "{$out['combo_view']}/combo_$c.tpl", $data);
            $files[] = $this->render('ui/combo-controller.php.tpl', "{$out['combo_controller']}/get_ui_combo_$c.php", $data);
            $files[] = $this->render('ui/add.php.tpl', "{$out['add_handler']}/add_$c.php", $data);
            $files[] = $this->render('ui/set.php.tpl', "{$out['set_handler']}/set_$c.php", $data);
            $files[] = $this->render('ui/del.php.tpl', "{$out['del_handler']}/del_$c.php", $data);
        }

        return $files;
    }

    /**
     * @return array<string, mixed>
     */
    public function tableData(Table $table): array
    {
        $naming = $this->ctx->naming;
        $classname = $naming->className($table->schema, $table->name);
        $plain = [];
        $replicas = [];
        $columns = [];
        $setters = [];
        foreach ($table->fields as $field) {
            if ($field->type === 'replica') {
                $replicas[] = $field->name;
                continue;
            }
            $plain[] = $field->name;
            $columns[] = [
                'name' => $field->name,
                'css' => str_replace('_', '-', $field->name),
                'title' => $naming->title($field->name),
            ];
            $setters[] = [
                'name' => $field->name,
                'cap' => \Divengine\Codegen\Naming\Naming::capitalize($field->name),
                'md5' => $field->type === 'md5',
            ];
        }

        return [
            'classname' => $classname,
            'name' => $table->name,
            'css' => str_replace('_', '-', $table->name),
            'title' => $naming->title($table->name),
            // the UI layer looks the package up without the schema (cogePHP)
            'package' => $naming->package($classname),
            'columns' => $columns,
            'plainFields' => $plain,
            'replicaFields' => $replicas,
            'allFields' => array_map(static fn ($f) => $f->name, $table->fields),
            'setters' => $setters,
            'pk' => $table->primaryKey,
            'hasPk' => $table->primaryKey !== [],
            'compositePk' => count($table->primaryKey) > 1,
            'description' => $this->description($table),
            'deleteButton' => (string) $this->ctx->profile->get('ui.delete_button', 'Delete'),
        ];
    }

    /**
     * Combo option text of a table from the profile ("{field}" = a column),
     * split into literal and field parts. The profile key is "schema.table"
     * ("." + table for the public schema, as in cogePHP).
     *
     * @return list<array{field: bool, text: string}>
     */
    private function description(Table $table): array
    {
        $descriptions = $this->ctx->profile->list('ui.table_descriptions');
        $key = $table->schema . '.' . $table->name;
        if (!isset($descriptions[$key])) {
            return [];
        }
        $names = array_map(static fn ($f) => preg_quote($f->name, '/'), $table->fields);
        $parts = preg_split('/\{(' . implode('|', $names) . ')\}/', (string) $descriptions[$key], -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $result = [];
        foreach ($parts as $i => $text) {
            if ($text !== '') {
                $result[] = ['field' => $i % 2 === 1, 'text' => $text];
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function render(string $template, string $path, array $data): GeneratedFile
    {
        return new GeneratedFile($path, $this->ctx->renderer->render($template, $this->ctx->common() + $data));
    }
}
