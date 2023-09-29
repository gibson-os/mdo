<?php
declare(strict_types=1);

namespace MDO\Query;

interface QueryInterface
{
    public function getQuery(): string;

    public function getParameters(): array;
}
