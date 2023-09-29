<?php
declare(strict_types=1);

namespace MDO\Query;

use MDO\Dto\Table;

class DeleteQuery implements QueryInterface
{
    use JoinTrait;
    use WhereTrait;
    use LimitTrait;

    public function __construct(private readonly Table $table, private readonly ?string $alias = null)
    {
    }

    public function getQuery(): string
    {
        $whereString = $this->getWhereString();
        $limitString = $this->getLimitString();
        $alias = $this->alias;

        return trim(sprintf(
            'DELETE `%s` FROM `%s`%s%s WHERE %s%s',
            $alias === null ? $this->table->getTableName() : $alias,
            $this->table->getTableName(),
            $alias === null ? '' : ' `' . $alias . '`',
            trim($this->getJoinsString()),
            $whereString,
            $limitString === '' ? '' : ' LIMIT ' . $limitString,
        ));
    }
}
