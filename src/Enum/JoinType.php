<?php
declare(strict_types=1);

namespace MDO\Enum;

enum JoinType: string
{
    case INNER = '';
    case LEFT = 'LEFT';
    case RIGHT = 'RIGHT';
    case CROSS = 'CROSS';
}
