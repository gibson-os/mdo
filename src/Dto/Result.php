<?php
declare(strict_types=1);

namespace MDO\Dto;

use Generator;
use mysqli_result;
use stdClass;

class Result
{
    public function __construct(private readonly mysqli_result $result)
    {
    }

    /**
     * @return Generator<Record>
     */
    public function iterateRecords(): Generator
    {
        while ($row = $this->result->fetch_assoc()) {
            yield new Record($row);
        }
    }
}