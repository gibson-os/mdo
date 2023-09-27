<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

use MDO\Dto\Table;
use phpDocumentor\Reflection\Type;

class Select
{
    use JoinTrait;
    use WhereTrait;

    private array $selects = [];

    public function __construct(
        private readonly Table $table,
    ) {
        foreach ($table->getFields() as $field) {
            $this->selects[$field->getName()] = sprintf('`%s`', $field->getName());
        }
    }

    public function getQuery(): string
    {
        $selectString = '';

        foreach ($this->selects as $alias => $select) {
            $selectString .= sprintf('(%s) `%s`', $select, $alias);
        }

        $whereString = $this->getWhereString();

        return trim(sprintf(
            'SELECT %s FROM %s %s%s%s%s%s',
            $selectString,
            $this->table->getTableName(),
            trim($this->getJoinsString() . ' '),
            $whereString === '' ? '' : 'WHERE ' . $whereString,
            'group by',
            'order',
            'limit',
        ));
    }

    public function __toString(): string
    {
        return $this->getQuery();
    }
}