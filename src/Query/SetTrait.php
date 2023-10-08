<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Field;
use MDO\Dto\Table;
use MDO\Dto\Value;

trait SetTrait
{
    private function getSetString(Table $table, array $values): string
    {
        $set = [];

        foreach ($table->getFields() as $field) {
            if (!array_key_exists($field->getName(), $values)) {
                continue;
            }

            $set[] = $this->getFieldSetString($field, $values[$field->getName()]);
        }

        return implode(', ', $set);
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
}
