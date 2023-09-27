<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

use MDO\Dto\Table;

class Delete implements QueryInterface
{
    use JoinTrait;
    use WhereTrait;
    use LimitTrait;

    public function __construct(private readonly Table $table)
    {
    }

    public function getQuery(): string
    {
        $whereString = $this->getWhereString();
        $limitString = $this->getLimitString();

        return sprintf(
            'DELETE `%s` FROM `%s`%s WHERE %s%s',
            $this->table->getTableName(),
            $this->table->getTableName(),
            trim($this->getJoinsString() . ' '),
            $whereString,
            $limitString === '' ? '' : ' LIMIT ' . $limitString,
        );
    }
}