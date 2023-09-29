<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Field;
use MDO\Dto\Table;
use MDO\Enum\OrderDirection;

class SelectQuery implements QueryInterface
{
    use JoinTrait;
    use WhereTrait;
    use LimitTrait;

    /**
     * @var string[]
     */
    private array $selects = [];

    /**
     * @var string[]
     */
    private array $groups = [];

    private ?string $having = null;

    /**
     * @var array<string, OrderDirection>
     */
    private array $orders = [];

    public function __construct(private readonly Table $table, private readonly ?string $alias = null)
    {
        $this->selects = array_map(
            static fn (Field $field): string => sprintf('`%s`', $field->getName()),
            $this->table->getFields(),
        );
    }

    public function getQuery(): string
    {
        $selectString = $this->getSelectString();
        $whereString = $this->getWhereString();
        $groupString = $this->getGroupString();
        $orderString = $this->getOrderString();
        $limitString = $this->getLimitString();

        return trim(sprintf(
            'SELECT %s FROM `%s`%s %s%s%s%s%s%s',
            $selectString,
            $this->table->getTableName(),
            $this->alias === null ? '' : ' `' . $this->alias . '`',
            trim($this->getJoinsString() . ' '),
            $whereString === '' ? '' : ' WHERE ' . $whereString,
            $groupString === '' ? '' : ' GROUP BY ' . $groupString,
            $this->having === null ? '' : ' HAVING ' . $this->having,
            $orderString === '' ? '' : ' ORDER BY ' . $orderString,
            $limitString === '' ? '' : ' LIMIT ' . $limitString,
        ));
    }

    public function getSelects(): array
    {
        return $this->selects;
    }

    public function setSelects(array $selects): SelectQuery
    {
        $this->selects = $selects;

        return $this;
    }

    public function setSelect(string $select, string $alias): SelectQuery
    {
        $this->selects[$alias] = $select;

        return $this;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }

    public function setOrder(string $fieldName, OrderDirection $direction = OrderDirection::ASC): SelectQuery
    {
        $this->orders[$fieldName] = $direction;

        return $this;
    }

    public function setGroupBy(array $fieldNames, string $having = null): SelectQuery
    {
        $this->groups = $fieldNames;
        $this->having = $having;

        return $this;
    }

    private function getSelectString(): string
    {
        $selects = [];

        foreach ($this->selects as $alias => $select) {
            $selects[] = sprintf('(%s) `%s`', $select, $alias);
        }

        return implode(', ', $selects);
    }

    private function getGroupString(): string
    {
        return implode(', ', $this->groups);
    }

    private function getOrderString(): string
    {
        $orders = [];

        foreach ($this->orders as $fieldName => $direction) {
            $orders[] = sprintf('%s %s', $fieldName, $direction->value);
        }

        return implode(', ', $orders);
    }
}
