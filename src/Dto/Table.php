<?php
declare(strict_types=1);

namespace MDO\Dto;

use MDO\Exception\TableException;

readonly class Table
{
    /**
     * @param array<string, Field> $fields
     */
    public function __construct(
        private string $tableName,
        private array $fields,
    ) {
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return array<string, Field>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $fieldName): Field
    {
        return $this->fields[$fieldName] ?? throw new TableException(sprintf(
            'Field %s does not exist in table %s',
            $fieldName,
            $this->tableName,
        ));
    }
}