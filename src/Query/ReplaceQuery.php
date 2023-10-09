<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Table;
use MDO\Dto\Value;

class ReplaceQuery implements QueryInterface
{
    use SetTrait;

    /**
     * @param Value[] $values
     */
    public function __construct(private readonly Table $table, private array $values)
    {
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getQuery(): string
    {
        $setString = $this->getSetString($this->table, $this->values);

        return trim(sprintf(
            'INSERT INTO `%s` SET %s ON DUPLICATE KEY UPDATE %s',
            $this->table->getTableName(),
            $setString,
            $setString,
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

    public function getParameters(): array
    {
        $parameters = [];

        foreach ($this->table->getFields() as $field) {
            $fieldName = $field->getName();

            if (!isset($this->values[$fieldName])) {
                continue;
            }

            $value = $this->values[$fieldName];

            if (!$value->hasParameter()) {
                continue;
            }

            $parameters[$fieldName] = $value->getValue();
        }

        return $parameters;
    }
}
