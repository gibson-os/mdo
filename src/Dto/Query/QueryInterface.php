<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

interface QueryInterface
{
    public function getQuery(): string;

    public function getParameters(): array;
}