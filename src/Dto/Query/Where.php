<?php
declare(strict_types=1);

namespace MDO\Dto\Query;

class Where
{
    public function __construct(
        private readonly string $condition,
        private readonly array $parameters,
    ) {
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
