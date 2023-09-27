<?php
declare(strict_types=1);

namespace MDO\Dto;

use MDO\Exception\TableException;

class Record
{
    public function __construct(private readonly array $data = [])
    {
    }

    public function getData(string $prefix = ''): array
    {
        $data = [];

        foreach ($this->data as $key => $value) {
            if (mb_strpos($key, $prefix) !== 0) {
                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    public function get(string $key): null|string|int|float
    {
        return $this->data[$key] ?? null;
    }
}