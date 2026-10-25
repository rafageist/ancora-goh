<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A table of the model. The schema is "" for the public schema. */
final readonly class Table
{
    /**
     * @param list<Field> $fields
     * @param list<string> $primaryKey
     * @param list<ForeignKey> $foreignKeys
     */
    public function __construct(
        public string $schema,
        public string $name,
        public array $fields,
        public array $primaryKey,
        public array $foreignKeys,
    ) {
    }

    public function field(string $name): ?Field
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }

        return null;
    }
}
