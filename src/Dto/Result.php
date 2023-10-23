<?php
declare(strict_types=1);

namespace MDO\Dto;

use Generator;
use mysqli_result;

class Result
{
    public function __construct(private readonly ?mysqli_result $result)
    {
    }

    /**
     * @return Generator<Record>
     */
    public function iterateRecords(): Generator
    {
        if ($this->result === null) {
            return;
        }

        while ($row = $this->result->fetch_assoc()) {
            yield new Record(array_map(
                static fn (float|int|string|null $value): Value => new Value($value),
                $row,
            ));
        }
    }
}
