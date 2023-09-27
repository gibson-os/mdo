<?php
declare(strict_types=1);

namespace MDO\Dto;

use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Enum\Query as QueryType;

class Query
{
    public function __construct(
        private readonly Table $table,
        private readonly QueryType $type,
    ) {
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getType(): QueryType
    {
        return $this->type;
    }
}