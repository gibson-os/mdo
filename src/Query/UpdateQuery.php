<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Table;
use MDO\Dto\Value;

class UpdateQuery implements QueryInterface
{
    use WhereTrait {
        getParameters as getWhereParameters;
    }
    use LimitTrait;
    use JoinTrait;
    use SetTrait;
    use OrderByTrait;

    /**
     * @param Value[] $values
     */
    public function __construct(
        private readonly Table $table,
        private array $values,
        private readonly ?string $alias = null,
    ) {
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): UpdateQuery
    {
        $this->values = $values;

        return $this;
    }

    public function getQuery(): string
    {
        $setString = $this->getSetString($this->table, $this->values, $this->alias);
        $whereString = $this->getWhereString();
        $orderString = $this->getOrderString();
        $limitString = $this->getLimitString();

        return trim(sprintf(
            'UPDATE `%s`%s %s SET %s WHERE %s%s%s',
            $this->table->getTableName(),
            $this->alias === null ? '' : ' `' . $this->alias . '`',
            trim($this->getJoinsString()),
            $setString,
            $whereString,
            $orderString === '' ? '' : ' ORDER BY ' . $orderString,
            $limitString === '' ? '' : ' LIMIT ' . $limitString,
        ));
    }

    public function getParameters(): array
    {
        $parameters = $this->getWhereParameters();

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
