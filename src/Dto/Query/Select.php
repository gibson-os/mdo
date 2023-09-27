<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

use MDO\Dto\Field;
use MDO\Dto\Table;
use MDO\Enum\OrderDirection;

class Select implements QueryInterface
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

    public function __construct(private readonly Table $table)
    {
        $this->selects = array_map(
            static fn(Field $field): string => sprintf('`%s`', $field->getName()),
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
            'SELECT %s FROM %s %s%s%s%s%s%s',
            $selectString,
            $this->table->getTableName(),
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

    public function setSelects(array $selects): Select
    {
        $this->selects = $selects;

        return $this;
    }

    public function setSelect(string $select, string $alias): Select
    {
        $this->selects[$alias] = $select;

        return $this;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }

    public function setOrder(string $fieldName, OrderDirection $direction): Select
    {
        $this->orders[$fieldName] = $direction;

        return $this;
    }

    private function getSelectString(): string
    {
        $selectString = '';

        foreach ($this->selects as $alias => $select) {
            $selectString .= sprintf('(%s) `%s`', $select, $alias);
        }

        return $selectString;
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