<?php
declare(strict_types=1);

namespace MDO\Dto;

use MDO\Enum\ValueType;

class Value
{
    public function __construct(
        private string|int|float|null $value,
        private ValueType $type = ValueType::RAW,
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

    public function setValue(float|int|string|null $value, ValueType $type = ValueType::RAW): Value
    {
        $this->value = $value;

        return $this;
    }

    public function hasParameter(): bool
    {
        return $this->value !== null && $this->type === ValueType::RAW;
    }
}
