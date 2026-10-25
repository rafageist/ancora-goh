<?php

/**
 * Áncora GOH profile for Divengine Codegen.
 *
 * Everything that is specific to Áncora GOH lives here: the project name,
 * where the model is, which schemas and packages exist, the naming and
 * spelling tables, the output layout and the runtime conventions of the
 * generated code. The values were moved from the historical cogePHP
 * configuration (dev/cogephp.conf, 2010) without changing them, so the
 * generated code is unchanged.
 *
 * Paths are relative to the repository root.
 */

declare(strict_types=1);

return [
    'name' => 'AncoraGOH',

    // Model inputs (written by `divengine-codegen introspect`)
    'model' => [
        'dir' => 'dev/src',
        'file' => 'ancoragoh.json',
        'services_dir' => 'dev/services',
    ],

    // Database introspection (`introspect`); never point it at a database
    // that is not disposable. Credentials come from the environment
    // (DIVENGINE_CODEGEN_DB_*, falling back to COGEPHP_DB_*).
    'introspect' => [
        'database' => 'ancoragoh',
        'user' => 'ancoragoh',
        'schemas' => ['public', 'matrix', 'util', 'prioriza', 'seguridad', 'portal', 'generador'],
        'ignore_tables' => [],
        'ignore_columns' => [
            'public.plantilla_de_actividad.actualizacion_interna',
            'public.lugar_para_actividad.actualizacion_interna',
        ],
        // a column with this name and model type "string" gets another type
        'special_columns' => [
            'id' => 'replica',
            'contrasena' => 'md5',
        ],
    ],

    // Naming of the generated classes
    'naming' => [
        // schemas whose name is not added to the class name
        'ignore_schema_in_class_name' => ['datos', 'seguridad', 'portal'],
        // add the schema name after the table name (true) or before it
        'schema_as_suffix' => true,
        // no schema name when it equals the table name (and no public table has it)
        'ignore_schema_equal_to_table' => true,
        // function names that are PHP keywords get this prefix
        'php_keywords' => ['echo', 'print', 'if', 'else', 'function', 'class', 'public', 'private', 'fputs', 'print_r'],
        'keyword_prefix' => 'go',
    ],

    // Packages: package => list of class names or "{schema}" selectors.
    // "{}" selects the classes without schema (public tables, PartModel).
    // The first package that matches wins; the default is used otherwise.
    'packages' => [
        'model.data' => ['{}', '{public}'],
        'model.data.services' => ['{services}'],
        'model.data.views' => ['{vistas}'],
        'model.security' => ['{seguridad}'],
        'model.generator' => ['{generador}'],
        'model.util' => ['{prioriza}', '{tiempo}', '{matrix}'],
    ],
    'default_package' => 'model',
    // selectors used for the database views and the service classes
    'package_selectors' => [
        'views' => 'vistas',
        'services' => 'services',
    ],

    // Model types
    'types' => [
        // database type => model type, used by `introspect` (the historical
        // $reverse_mapa_type). The SQL creator script looks the model types
        // up in this same map, as cogePHP did, so most model types are
        // written unchanged there (kept for byte parity).
        'database' => [
            'character varying' => 'string',
            'integer' => 'integer',
            'boolean' => 'bool',
            'bool' => 'bool',
            'array' => 'codificador',
            'real' => 'real',
            '"char"' => 'char',
            'double precision' => 'double',
            'varchar' => 'string',
            'bigint' => 'bigint',
        ],
        // model type => written between quotes (text) or not (number)
        'quoted' => [
            'string' => true,
            'integer' => false,
            'codificador' => true,
            'replica' => true,
            'memo' => true,
            'long' => false,
            'boolean' => false,
            'bool' => false,
            'md5' => true,
            'datetime' => true,
            'date' => true,
            'double' => false,
            'bigint' => false,
        ],
        // other model types accepted in table fields (handled as text)
        'other' => ['timestamp without time zone', 'real', 'char', 'text', 'numeric'],
    ],

    // User interface texts (Spanish, HTML entities)
    'ui' => [
        'spelling' => [
            'cion' => 'ci&oacute;n',
            'Dia ' => 'D&iacute;a ',
        ],
        'titles' => [
            'comision' => 'Comisi&oacute;n',
        ],
        // combo option text per "schema.table"; "{field}" is a column value
        'table_descriptions' => [
            'planificacion' => '{descripcion}',
        ],
        'delete_button' => 'Eliminar',
    ],

    // Output layout (relative to the output root)
    'output' => [
        'sql_dir' => 'model',
        'sql_creator' => 'AncoraGOH.creator.sql',
        'sql_dropper' => 'AncoraGOH.dropper.sql',
        'gallery_view' => 'view/gallery',
        'grid_view' => 'view/grid',
        'combo_view' => 'view/combo',
        'gallery_controller' => 'control/view/gallery',
        'combo_controller' => 'control/view/combo',
        'add_handler' => 'control/data/add',
        'set_handler' => 'control/data/set',
        'del_handler' => 'control/data/del',
    ],

    // Runtime conventions of the generated code (paths are relative to www/)
    'runtime' => [
        'db_global' => '$dbconnection',
        'replica_function' => 'getReplica',
        'password_hash' => 'PasswordUtil::hash',
        'view_path' => '../view',
        'fdm_path' => '../control/data/fdms',
    ],

    // Not used by the current templates; kept from the historical
    // configuration for the UI work of #35 (descriptor column of a foreign key).
    'foreign_key_descriptor' => [
        'actividad_docente' => ['id' => 'dia'],
        'actividad_colectiva' => ['id' => 'descripcion'],
        'lugar' => ['id' => 'nombre'],
    ],
];
