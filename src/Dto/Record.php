<?php
declare(strict_types=1);

namespace MDO\Dto;

use MDO\Exception\RecordException;
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

    /**
     * @throws RecordException
     */
    public function get(string $key): Value
    {
        return $this->values[$key] ?? throw new RecordException(sprintf(
            'Key "%s" not found. Possible keys: %s',
            $key,
            implode(', ', array_keys($this->values)),
        ));
    }

    /**
     * @return array<float|int|string|null>
     */
    public function getValuesAsArray(string $prefix = ''): array
    {
        return array_map(
            static fn (Value $value): float|int|string|null => $value->getValue(),
            $this->getValues($prefix),
        );
    }
}
