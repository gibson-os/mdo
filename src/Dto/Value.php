<?php
declare(strict_types=1);

namespace MDO\Dto;

use MDO\Enum\ValueType;

class Value
{
    public function __construct(
        private readonly string|int|float|null $value,
        private readonly ValueType $type = ValueType::RAW,
    ) {
    }

    public function getValue(): float|int|string|null
    {
        return $this->value;
    }

    public function getType(): ValueType
    {
        return $this->type;
    }

    public function hasParameter(): bool
    {
        return $this->value !== null && $this->type === ValueType::RAW;
    }
}
