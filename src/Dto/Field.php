<?php
declare(strict_types=1);

namespace MDO\Dto;

use MDO\Enum\Type;

readonly class Field
{
    public function __construct(
        private string $name,
        private bool $nullable,
        private Type $type,
        private string $key,
        private string|int|float|null $default,
        private string $extra,
        private float|int|string $length = 0,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDefault(): float|int|string|null
    {
        return $this->default;
    }

    public function getExtra(): string
    {
        return $this->extra;
    }

    public function getLength(): float|int|string
    {
        return $this->length;
    }

    public function isPrimary(): bool
    {
        return mb_strtolower($this->key) === 'pri';
    }
}
