<?php
declare(strict_types=1);

namespace MDO\Manager;

use MDO\Dto\Table;
use MDO\Exception\ClientException;
use MDO\Loader\FieldLoader;

class TableManager
{
    /**
     * @var array<string, Table>
     */
    private array $tables = [];

    public function __construct(private readonly FieldLoader $fieldLoader)
    {
    }

    /**
     * @throws ClientException
     */
    public function getTable(string $tableName): Table
    {
        $this->tables[$tableName] ??= new Table($tableName, $this->fieldLoader->loadFields($tableName));

        return $this->tables[$tableName];
    }
}
