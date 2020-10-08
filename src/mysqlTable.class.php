<?php
/** @noinspection SqlNoDataSourceInspection */
declare(strict_types=1);

class mysqlTable
{
    /**
     * @var mysqlDatabase
     */
    public $connection;

    /**
     * @var array
     */
    public $fields = [];

    /**
     * @var string
     */
    public $selectString = '*';

    /**
     * @var string
     */
    public $joins = '';

    /**
     * @var array
     */
    public $unions = [];

    /**
     * @var string
     */
    public $unionFunc = 'ALL';

    /**
     * @var string
     */
    public $sql;

    /**
     * @var string
     */
    public $where;

    /**
     * @var string
     */
    public $orderBy;

    /**
     * @var string
     */
    public $limit;

    /**
     * @var string
     */
    public $selectFunc;

    /**
     * @var string
     */
    public $groupBy;

    /**
     * @var string
     */
    public $having;

    /**
     * @var string
     */
    public $database;

    /**
     * @var string
     */
    public $table;

    /**
     * @var array
     */
    public $records = [];

    /**
     * @var int
     */
    public $countRecords = 0;

    /**
     * @var int
     */
    public $selectedRecord = 0;

    /**
     * @var array
     */
    private $whereParameters = [];

    /**
     * mysqlTable constructor.
     */
    public function __construct(mysqlDatabase $connection, string $table)
    {
        $this->database = $connection->getDatabaseName();
        $this->table = $table;
        $this->setConnection($connection);

        $registry = mysqlRegistry::getInstance();

        if ($registry->exists('mdo_' . $table)) {
            $fieldValue = $registry->get('mdo_' . $table);

            if (is_array($fieldValue)) {
                foreach ($fieldValue as $field) {
                    $this->fields[] = $field[0];
                    $this->{$field[0]} = new mysqlField($field, $this->connection);
                }
            }
        } else {
            $this->connection->sendQuery('SHOW FIELDS FROM `' . $this->database . '`.`' . $table . '`;');
            $fields = [];

            while ($field = $this->connection->fetchRow()) {
                if (preg_match('/\(\d*\)/', $field[1], $length, PREG_OFFSET_CAPTURE)) {
                    $field[] = substr($length[0][0], 1, strlen($length[0][0]) - 2);
                    $field[1] = preg_replace('/\(\d*\)/', '', $field[1]);
                }

                $this->fields[] = $field[0];
                $this->{$field[0]} = new mysqlField($field, $this->connection);
                $fields[] = $field;
            }

            $registry->set('mdo_' . $table, $fields);
        }

        $this->selectString = $this->quoteSelectArray($this->fields, $this->table);
    }

    public function setConnection(mysqlDatabase $connection): void
    {
        $this->connection = $connection;
    }

    public function reset()
    {
        $this->load();
        $this->setWhere();
        $this->setGroupBy();
        $this->setOrderBy();
        $this->setLimit();
        $this->clearJoin();
    }

    /**
     * @param mixed|null $record
     */
    public function load($record = null): bool
    {
        if (is_object($record)) {
            foreach ($this->fields as $field) {
                if (isset($record->{$field})) {
                    $this->{$field}->setValue($record->{$field});
                }
            }
        } elseif (is_array($record)) {
            if (key($record)) {
                foreach ($this->fields as $field) {
                    if (array_key_exists($field, $record)) {
                        $this->{$field}->setValue($record[$field]);
                    }
                }
            } else {
                foreach ($this->fields as $index => $field) {
                    if (
                        isset($record[$index]) ||
                        $record[$field] === null
                    ) {
                        $this->{$field}->setValue($record[$index]);
                    }
                }
            }
        } else {
            foreach ($this->fields as $field) {
                $this->{$field}->setDefaultValue();
            }

            return false;
        }

        return true;
    }

    /**
     * @param string|array $select
     */
    public function appendSelectString($select, string $table = null): void
    {
        if (is_array($select)) {
            $this->selectString .= ',' . $this->quoteSelectArray($select, $table);
        } else {
            $this->selectString .= ',' . $select;
        }
    }

    /**
     * @param string|array|null $select
     */
    public function setSelectString($select = null, string $table = null): void
    {
        if ($select) {
            if (is_array($select)) {
                $this->selectString = $this->quoteSelectArray($select, $table);
            } else {
                $this->selectString = $select;
            }
        } else {
            $this->selectString = $this->quoteSelectArray($this->fields, $this->table);
        }
    }

    private function quoteSelectArray(array $select, string $table = null): string
    {
        if ($table) {
            return '`' . $table . '`.`' . implode('`, `' . $table . '`.`', $select) . '`';
        }

        return '`' . implode('`, `', $select) . '`';
    }

    public function update(string $set): bool
    {
        $this->sql = 'UPDATE `' . $this->database . '`.`' . $this->table . '` SET ' . $set . ' ' . $this->where;

        return $this->connection->sendQuery($this->sql);
    }

    public function getSelect(string $select = null, bool $union = false): string
    {
        if (!$select) {
            $select = $this->selectString;
        }

        if (
            $union &&
            count($this->unions) > 1
        ) {
            return '(' . trim(implode(') UNION ' . $this->unionFunc . ' (', $this->unions)) . ') ' . $this->orderBy . $this->limit . ';';
        }

        return trim('SELECT ' . $this->selectFunc . $select . ' FROM `' . $this->database . '`.`' . $this->table . '`' . $this->joins . ' ' . $this->where . $this->groupBy . $this->having . $this->orderBy . $this->limit) . ';';
    }

    /**
     * @return bool|int Anzahl der Datensätze. Im Fehlerfall false
     */
    public function select(bool $loadRecord = true, string $select = null, bool $union = false)
    {
        $this->sql = $this->getSelect($select, $union);

        if ($this->connection->sendQuery($this->sql)) {
            if ($loadRecord) {
                $this->records = $this->connection->fetchAssocList();

                if ($this->first()) {
                    $this->countRecords = count($this->records);
                } else {
                    $this->countRecords = 0;
                }
            } else {
                return true;
            }

            return $this->countRecords;
        }

        unset($this->records);
        $this->countRecords = 0;

        return false;
    }

    /**
     * @return bool|int Anzahl der Datensätze. Im Fehlerfall false
     */
    public function selectPrepared(bool $loadRecord = true, string $select = null, bool $union = false)
    {
        $this->sql = $this->getSelect($select, $union);

        if ($this->connection->execute($this->sql, $this->whereParameters)) {
            if ($loadRecord) {
                $this->records = $this->connection->fetchAssocList();

                if ($this->first()) {
                    $this->countRecords = count($this->records);
                } else {
                    $this->countRecords = 0;
                }
            } else {
                return true;
            }

            return $this->countRecords;
        }

        unset($this->records);
        $this->countRecords = 0;

        return false;
    }

    /**
     * Führt SQL Query aus.
     *
     * Führt ein SQL Query mit Union aus mit dem Ziel Datensätze zu erhalten.
     *
     * @param bool $loadRecord Wenn true werden die Datensätze in die Eigenschaft records geladen
     *
     * @return bool|int
     */
    public function selectUnion(bool $loadRecord = true)
    {
        return $this->select($loadRecord, null, true);
    }

    /**
     * @return string[]|null
     */
    public function selectAggregate(string $function): ?array
    {
        if (!$this->select(false, $function)) {
            return null;
        }

        return $this->connection->fetchRow();
    }

    public function getSave(): string
    {
        $sql = 'INSERT INTO `' . $this->database . '`.`' . $this->table . '` SET ';
        $fieldString = '';

        foreach ($this->fields as $field) {
            /** @var mysqlField $fieldObject */
            $fieldObject = $this->{$field};

            if (
                $fieldObject->isAutoIncrement() &&
                empty($fieldObject->getValue())
            ) {
                continue;
            }

            $fieldString .= '`' . $field . '`=' . $fieldObject->getSQLValue() . ', ';
        }

        $fieldString = mb_substr($fieldString, 0, -2);

        return $sql . $fieldString . ' ON DUPLICATE KEY UPDATE ' . $fieldString;
    }

    /**
     * @throws Exception
     */
    public function save(): bool
    {
        $this->sql = $this->getSave();

        if (!$this->connection->sendQuery($this->sql)) {
            throw new Exception('Error: ' . $this->connection->error() . PHP_EOL . 'Query: ' . $this->sql);
        }

        return true;
    }

    public function getReplacedRecord(): bool
    {
        $where = '';

        foreach ($this->fields as $field) {
            if ($this->{$field}->getValue()) {
                $where .= '`' . $field . '`=' . $this->{$field}->getSQLValue() . ' && ';
            }
        }

        $where = substr($where, 0, strlen($where) - 4);

        $this->setWhere($where);
        $this->select();
        $this->setWhere();

        if ($this->countRecords() == 1) {
            return true;
        }

        return false;
    }

    public function getDelete(): string
    {
        if (!empty($this->where) && strlen($this->where)) {
            $sql = 'DELETE FROM `' . $this->database . '`.`' . $this->table . '` ' . $this->where . ';';
        } else {
            $sql = 'DELETE FROM `' . $this->database . '`.`' . $this->table . '` WHERE ';

            foreach ($this->fields as $field) {
                if (null === $this->{$field}->getValue()) {
                    $sql .= '`' . $field . '` IS NULL AND ';
                } else {
                    $sql .= '`' . $field . '`=' . $this->{$field}->getSQLValue() . ' AND ';
                }
            }

            $sql = substr($sql, 0, strlen($sql) - 5) . ';';
            // Datensatz aus Array löschen!
        }

        return $sql;
    }

    public function delete(): bool
    {
        $this->sql = $this->getDelete();

        return $this->connection->sendQuery($this->sql);
    }

    public function getDeletePrepared(): string
    {
        if (!empty($this->where) && strlen($this->where)) {
            $sql = 'DELETE FROM `' . $this->database . '`.`' . $this->table . '` ' . $this->where . ';';
        } else {
            $sql = 'DELETE FROM `' . $this->database . '`.`' . $this->table . '` WHERE ';

            foreach ($this->fields as $field) {
                if (null === $this->{$field}->getValue()) {
                    $sql .= '`' . $field . '` IS NULL AND ';
                } else {
                    $sql .= '`' . $field . '`=? AND ';
                    $this->addWhereParameter($this->{$field}->getValue());
                }
            }

            $sql = substr($sql, 0, strlen($sql) - 5) . ';';
            // Datensatz aus Array löschen!
        }

        return $sql;
    }

    public function deletePrepared(): bool
    {
        $this->sql = $this->getDeletePrepared();

        return $this->connection->execute($this->sql, $this->whereParameters);
    }

    public function first(): bool
    {
        if (isset($this->records[0]) && $this->load($this->records[0])) {
            $this->selectedRecord = 0;

            return true;
        }

        return false;
    }

    public function last(): bool
    {
        if (
            isset($this->records[$this->countRecords - 1]) &&
            $this->load($this->records[$this->countRecords - 1])
        ) {
            $this->selectedRecord = $this->countRecords - 1;

            return true;
        }

        return false;
    }

    public function next(): bool
    {
        if ($this->selectedRecord < $this->countRecords) {
            if (
                isset($this->records[$this->selectedRecord + 1]) &&
                $this->load($this->records[$this->selectedRecord + 1])
            ) {
                ++$this->selectedRecord;

                return true;
            }
        }

        return false;
    }

    public function previous(): bool
    {
        if ($this->selectedRecord != 0) {
            if (
                isset($this->records[$this->selectedRecord - 1]) &&
                $this->load($this->records[$this->selectedRecord - 1])
            ) {
                --$this->selectedRecord;

                return true;
            }
        }

        return false;
    }

    public function appendJoin(string $table, string $on): mysqlTable
    {
        $this->joins .= ' JOIN ' . $table . ' ON ' . $on;

        return $this;
    }

    public function clearJoin(): mysqlTable
    {
        $this->joins = '';

        return $this;
    }

    public function appendJoinLeft(string $table, string $on): mysqlTable
    {
        $this->joins .= ' LEFT JOIN ' . $table . ' ON ' . $on;

        return $this;
    }

    public function appendUnion(string $query = null, string $select = null): mysqlTable
    {
        if ($query) {
            $query = preg_replace('/;/', '', $query);
        } else {
            $query = mb_substr($this->getSelect($select), 0, -1);
        }

        $this->unions[] = $query;

        return $this;
    }

    public function setSelectFunc(string $function = null): mysqlTable
    {
        if ($function) {
            $this->selectFunc = $function . ' ';
        } else {
            $this->selectFunc = '';
        }

        return $this;
    }

    public function setWhere(string $where = null): mysqlTable
    {
        if ($where) {
            $this->where = 'WHERE ' . $where . ' ';
        } else {
            $this->where = '';
        }

        return $this;
    }

    public function setGroupBy(string $groupBy = null, string $having = null): mysqlTable
    {
        if ($groupBy === null) {
            $this->groupBy = '';
            $this->having = '';
        } else {
            $this->groupBy = 'GROUP BY ' . $groupBy . ' ';
            $this->having = $having === null ? '' : 'HAVING ' . $having . ' ';
        }

        return $this;
    }

    public function setOrderBy(string $orderBy = null): mysqlTable
    {
        $this->orderBy = $orderBy === null ? '' : 'ORDER BY ' . $orderBy . ' ';

        return $this;
    }

    public function setLimit(int $rows = null, int $from = null): mysqlTable
    {
        if (!empty($from)) {
            $this->limit = 'LIMIT ' . $from . ', ' . $rows;
        } elseif (!empty($rows)) {
            $this->limit = 'LIMIT ' . $rows;
        } else {
            $this->limit = '';
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return string[]
     */
    public function getSelectedRecord(): array
    {
        return $this->records[$this->selectedRecord];
    }

    public function countRecords(): int
    {
        return $this->countRecords;
    }

    public function getDBName(): string
    {
        return $this->database;
    }

    public function getTableName(): string
    {
        return $this->table;
    }

    public function setWhereParameters(array $whereParameters): mysqlTable
    {
        $this->whereParameters = $whereParameters;

        return $this;
    }

    public function addWhereParameter($value): mysqlTable
    {
        $this->whereParameters[] = $value;

        return $this;
    }
}
