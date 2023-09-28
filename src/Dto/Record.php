<?php
declare(strict_types=1);

namespace MDO\Dto;

use UnexpectedValueException;

class Record
{
    /**
     * @param Value[] $values
     */
    public function __construct(private readonly array $values = [])
    {
    }

    /**
     * @return Value[]
     */
    public function getValues(string $prefix = ''): array
    {
        $values = [];

        foreach ($this->values as $key => $value) {
            if (mb_strpos($key, $prefix) !== 0) {
                continue;
            }

            $keyWithoutPrefix = str_replace($prefix, '', $key);

            if (is_array($keyWithoutPrefix)) {
                throw new UnexpectedValueException('Key without prefix is array');
            }

            $values[(string) $keyWithoutPrefix] = $value;
        }

        return $values;
    }

    public function get(string $key): ?Value
    {
        return $this->values[$key] ?? null;
    }
}
