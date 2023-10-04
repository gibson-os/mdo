<?php
declare(strict_types=1);

namespace MDO\Dto;

class Select
{
    public function __construct(
        private readonly Table $table,
        private readonly string $alias,
        private readonly string $prefix,
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

    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
