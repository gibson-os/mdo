<?php
declare(strict_types=1);

namespace MDO\Enum;

enum OrderDirection: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';
}
