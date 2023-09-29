<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Field;
use MDO\Dto\Table;
use MDO\Dto\Value;

class ReplaceQuery implements QueryInterface
{
    /**
     * @param Value[] $values
     */
    public function __construct(private readonly Table $table, private array $values)
    {
    }

    public function getQuery(): string
    {
        return trim(sprintf(
            'INSERT INTO `%s` SET %s ON DUPLICATE KEY UPDATE %s',
            $this->table->getTableName(),
            $this->getSetString(),
            $this->getPrimaryString(),
        ));
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): ReplaceQuery
    {
        $this->values = $values;

        return $this;
    }

    private function getSetString(): string
    {
        $set = [];

        foreach ($this->table->getFields() as $field) {
            if (!array_key_exists($field->getName(), $this->values)) {
                continue;
            }

            $set[] = $this->getFieldSetString($field, $this->values[$field->getName()]);
        }

        return implode(', ', $set);
    }

    private function getPrimaryString(): string
    {
        $primaries = [];

        foreach ($this->table->getPrimaryFields() as $field) {
            $primaries[] = $this->getFieldSetString($field, $this->values[$field->getName()] ?? null);
        }

        return implode(', ', $primaries);
    }

    private function getFieldSetString(Field $field, ?Value $value): string
    {
        if ($value === null) {
            return sprintf('`%s`=NULL', $field->getName());
        }

        $valueValue = $value->getValue();

        if ($valueValue === null) {
            return sprintf('`%s`=NULL', $field->getName());
        }

        if ($value->hasParameter()) {
            return sprintf('`%s`=?', $field->getName());
        }

        return sprintf('`%s`=%s', $field->getName(), $valueValue);
    }

    public function getParameters(): array
    {
        $parameters = [];

        foreach ($this->table->getFields() as $field) {
            if (!isset($this->values[$field->getName()])) {
                continue;
            }

            $value = $this->values[$field->getName()];

            if (!$value->hasParameter()) {
                continue;
            }

            $parameters[] = $value->getValue();
        }

        foreach ($this->table->getPrimaryFields() as $field) {
            $parameters[] = $this->values[$field->getName()]?->getValue();
        }

        return $parameters;
    }
}
