<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

use MDO\Query\SelectQuery;

class With
{
    public function __construct(
        private readonly string $name,
        private readonly SelectQuery $query,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuery(): SelectQuery
    {
        return $this->query;
    }
}
