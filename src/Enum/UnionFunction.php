<?php
declare(strict_types=1);

namespace MDO\Enum;

enum UnionFunction: string
{
    case UNION = 'UNION';
    case UNION_ALL = 'UNION ALL';
}
