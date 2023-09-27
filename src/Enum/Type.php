<?php
declare(strict_types=1);

namespace MDO\Enum;

enum Type: string
{
    case TINYINT = 'tinyint';
    case SMALLINT = 'smallint';
    case MEDIUMINT = 'mediumint';
    case INT = 'int';
    case BIGINT = 'bigint';
    case DECIMAL = 'decimal';
    case FLOAT = 'float';
    case DOUBLE = 'double';
    case BIT = 'bit';
    case CHAR = 'char';
    case VARCHAR = 'varchar';
    case BINARY = 'binary';
    case VARBINARY = 'varbinary';
    case TINYTEXT = 'tinytext';
    case TEXT = 'text';
    case MEDIUMTEXT = 'mediumtext';
    case LONGTEXT = 'longtext';
    case JSON = 'json';
    case TINYBLOB = 'tinyblob';
    case BLOB = 'blob';
    case MEDIUMBLOB = 'mediumblob';
    case LONGBLOB = 'longblob';
    case ENUM = 'enum';
    case SET = 'set';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TIMESTAMP = 'timestamp';
    case TIME = 'time';
    case YEAR = 'year';
}