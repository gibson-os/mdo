<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

use MDO\Dto\Table;
use MDO\Enum\Join as JoinType;

class Join
{
    public function __construct(
        private readonly Table $table,
        private readonly string $alias,
        private readonly string $on,
        private readonly JoinType $type = JoinType::INNER,
    ) {
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getOn(): string
    {
        return $this->on;
    }

    public function getType(): JoinType
    {
        return $this->type;
    }
}
