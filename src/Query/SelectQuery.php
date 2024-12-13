<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Field;
use MDO\Dto\Table;

class SelectQuery implements QueryInterface
{
    use JoinTrait;
    use WhereTrait {
        getParameters as getWhereParameters;
    }
    use WithTrait;
    use LimitTrait;
    use OrderByTrait;

    /**
     * @var string[]
     */
    private array $selects = [];

    /**
     * @var string[]
     */
    private array $groups = [];

    private ?string $having = null;

    private bool $distinct = false;

    private array $parameters = [];

    public function __construct(private readonly Table $table, private readonly ?string $alias = null)
    {
        $this->selects = array_map(
            fn (Field $field): string => sprintf(
                '%s`%s`',
                $this->alias === null ? '' : '`' . $this->alias . '`.',
                $field->getName(),
            ),
            $this->table->getFields(),
        );
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getQuery(): string
    {
        $selectString = $this->getSelectString();
        $withString = $this->getWithString();
        $whereString = $this->getWhereString();
        $groupString = $this->getGroupString();
        $orderString = $this->getOrderString();
        $limitString = $this->getLimitString();

        return trim(sprintf(
            '%s SELECT %s%s FROM `%s`%s %s%s%s%s%s%s',
            $withString,
            $this->distinct ? 'DISTINCT ' : '',
            $selectString,
            $this->table->getTableName(),
            $this->alias === null ? '' : ' `' . $this->alias . '`',
            trim($this->getJoinsString()),
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

    public function setGroupBy(array $fieldNames, ?string $having = null): SelectQuery
    {
        $this->groups = $fieldNames;
        $this->having = $having;

        return $this;
    }

    public function setDistinct(bool $distinct): SelectQuery
    {
        $this->distinct = $distinct;

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

    public function getParameters(): array
    {
        return array_merge($this->getWhereParameters(), $this->parameters);
    }

    public function addParameters(array $parameters): SelectQuery
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }
}
