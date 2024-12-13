<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Field;
use MDO\Dto\Table;
use MDO\Dto\Value;

trait SetTrait
{
    private function getSetString(Table $table, array $values, ?string $alias = null): string
    {
        $set = [];

        foreach ($table->getFields() as $field) {
            $fieldName = $field->getName();

            if (!array_key_exists($fieldName, $values)) {
                continue;
            }

            $set[] = $this->getFieldSetString($field, $values[$fieldName], $alias);
        }

        return implode(', ', $set);
    }

    private function getFieldSetString(Field $field, ?Value $value, ?string $alias = null): string
    {
        $fieldName = sprintf('`%s`', $field->getName());

        if ($alias !== null) {
            $fieldName = sprintf('`%s`.%s', $alias, $fieldName);
        }

        if ($value === null) {
            return sprintf('`%s`=NULL', $fieldName);
        }

        $valueValue = $value->getValue();

        if ($valueValue === null) {
            return sprintf('%s=NULL', $fieldName);
        }

        if ($value->hasParameter()) {
            return sprintf('%s=:%s', $fieldName, $field->getName());
        }

        return sprintf('%s=%s', $fieldName, $valueValue);
    }
}
