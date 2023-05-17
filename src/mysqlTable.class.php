<?php
/** @noinspection SqlNoDataSourceInspection */
declare(strict_types=1);

class mysqlTable
{
    public mysqlDatabase $connection;

    public array $fields = [];

    public string $selectString = '*';

    public string $joins = '';

    public array $unions = [];

    public string $unionFunc = 'ALL';

    public string $sql = '';

    public string $where = '';

    public string $orderBy = '';

    public string $limit = '';

    public string $selectFunc = '';

    public string $groupBy = '';

    public string $having = '';

    public string $database = '';

    public array $records = [];

    public int $countRecords = 0;

    public int $selectedRecord = 0;

    private array $whereParameters = [];

    public function __construct(mysqlDatabase $connection, public string $table)
    {
        $this->database = $connection->getDatabaseName();
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
            $this->connection->sendQuery('SHOW FIELDS FROM `' . $this->database . '`.`' . $table . '`');
            $fields = [];

            while ($field = $this->connection->fetchRow()) {
                if (preg_match('/\(\d*\)/', (string) $field[1], $length, PREG_OFFSET_CAPTURE)) {
                    $field[] = substr($length[0][0], 1, strlen($length[0][0]) - 2);
                    $field[1] = preg_replace('/\(\d*\)/', '', (string) $field[1]);
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
        $this->unions = [];
        $this->unionFunc = 'ALL';
        $this->load();
        $this->setWhere();
        $this->setWhereParameters([]);
        $this->setGroupBy();
        $this->setOrderBy();
        $this->setLimit();
        $this->clearJoin();
    }

    public function load(array|object $record = null): bool
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

    public function appendSelectString(string|array $select, string $table = null): void
    {
        if (is_array($select)) {
            $this->selectString .= ',' . $this->quoteSelectArray($select, $table);
        } else {
            $this->selectString .= ',' . $select;
        }
    }

    public function setSelectString(string|array $select = null, string $table = null): void
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

        return $this->connection->execute($this->sql, $this->whereParameters);
    }

    public function getSelect(string $select = null): string
    {
        if (!$select) {
            $select = $this->selectString;
        }

        if (count($this->unions) > 0) {
            $unions = $this->unions;
            array_unshift(
                $unions,
                trim('SELECT ' . $this->selectFunc . $select . ' FROM `' . $this->database . '`.`' . $this->table . '`' . $this->joins . ' ' . $this->where . $this->groupBy . $this->having)
            );

            return '(' . trim(implode(') UNION ' . $this->unionFunc . ' (', $unions)) . ') ' . $this->orderBy . $this->limit;
        }

        return trim('SELECT ' . $this->selectFunc . $select . ' FROM `' . $this->database . '`.`' . $this->table . '`' . $this->joins . ' ' . $this->where . $this->groupBy . $this->having . $this->orderBy . $this->limit);
    }

    public function select(bool $loadRecord = true, string $select = null): bool|int
    {
        $this->sql = $this->getSelect($select);

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

    public function selectPrepared(bool $loadRecord = true, string $select = null): bool|int
    {
        $this->sql = $this->getSelect($select);

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
     * @deprecated Use selectPrepared
     */
    public function selectUnion(bool $loadRecord = true): bool|int
    {
        return $this->selectPrepared($loadRecord);
    }

    /**
     * @deprecated
     */
    public function selectAggregate(string $function): array|null|false
    {
        if (!$this->select(false, $function)) {
            return null;
        }

        return $this->connection->fetchRow();
    }

    public function selectAggregatePrepared(string $function): array|null|false
    {
        if (!$this->selectPrepared(false, $function)) {
            return null;
        }

        return $this->connection->fetchRow();
    }

    /**
     * @return array{query: string, parameters: array<array-key, int|float|string|null>}
     */
    public function getSave(): array
    {
        $sql = 'INSERT INTO `' . $this->database . '`.`' . $this->table . '` SET ';
        $fieldString = '';
        $parameters = [];

        foreach ($this->fields as $field) {
            /** @var mysqlField $fieldObject */
            $fieldObject = $this->{$field};

            if (
                $fieldObject->isAutoIncrement() &&
                empty($fieldObject->getValue())
            ) {
                continue;
            }

            if ($fieldObject->getValueType() === 'FUNC') {
                $fieldString .= '`' . $field . '`=' . $fieldObject->getSQLValue() . ', ';

                continue;
            }

            $fieldString .= '`' . $field . '`=?, ';
            $parameters[] = $fieldObject->getValue();
        }

        $fieldString = mb_substr($fieldString, 0, -2);

        return [
            'query' => $sql . $fieldString . ' ON DUPLICATE KEY UPDATE ' . $fieldString,
            'parameters' => array_merge($parameters, $parameters),
        ];
    }

    /**
     * @throws Exception
     */
    public function save(): bool
    {
        $saveStatement = $this->getSave();
        $this->sql = $saveStatement['query'];

        if (!$this->connection->execute($this->sql, $saveStatement['parameters'])) {
            throw new Exception(
                'Error: ' . $this->connection->error() . PHP_EOL .
                'Query: ' . $this->sql . PHP_EOL .
                'Parameters: [' . implode(', ', $saveStatement['parameters']) . ']'
            );
        }

        return true;
    }

    public function getReplacedRecord(): bool
    {
        $wheres = [];

        foreach ($this->fields as $field) {
            /** @var mysqlField $fieldObject */
            $fieldObject = $this->{$field};
            $value = $fieldObject->getValue() ?? $fieldObject->getDefaultValue();

            if (
                ($fieldObject->isAutoIncrement() && !$value) ||
                $value === 'current_timestamp()'
            ) {
                continue;
            }

            if ($value === null) {
                $wheres[] = '`' . $field . '` IS NULL';

                continue;
            }

            $wheres[] = '`' . $field . '`=?';
            $this->addWhereParameter($value);
        }

        $this->setWhere(implode(' AND ', $wheres));
        $this->selectPrepared();
        $this->setWhere();
        $this->setWhereParameters([]);

        if ($this->countRecords() == 1) {
            return true;
        }

        return false;
    }

    public function getDelete(): string
    {
        if (!empty($this->where) && strlen($this->where)) {
            $sql = 'DELETE `' . $this->table . '` FROM `' . $this->database . '`.`' . $this->table . '`' . $this->joins . ' ' . $this->where;
        } else {
            $sql = 'DELETE FROM `' . $this->database . '`.`' . $this->table . '` WHERE ';

            foreach ($this->fields as $field) {
                if (null === $this->{$field}->getValue()) {
                    $sql .= '`' . $field . '` IS NULL AND ';
                } else {
                    $sql .= '`' . $field . '`=' . $this->{$field}->getSQLValue() . ' AND ';
                }
            }

            $sql = substr($sql, 0, strlen($sql) - 5);
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
            $sql = 'DELETE `' . $this->table . '` FROM `' . $this->database . '`.`' . $this->table . '`' . $this->joins . ' ' . $this->where;
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

            $sql = substr($sql, 0, strlen($sql) - 5);
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
            $query = $this->getSelect($select);
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
     * @return array<array-key, array<string, string>>
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return array<string, string>
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

    public function getWhereParameters(): array
    {
        return $this->whereParameters;
    }

    public function setWhereParameters(array $whereParameters): mysqlTable
    {
        $this->whereParameters = $whereParameters;

        return $this;
    }

    public function addWhereParameter($value, string $key = null): mysqlTable
    {
        if ($key === null) {
            $this->whereParameters[] = $value;
        } else {
            $this->whereParameters[$key] = $value;
        }

        return $this;
    }

    public function getParametersString(array $parameters, string $separator = ', ', string $value = '?'): string
    {
        if ($value === '?') {
            return implode($separator, array_fill(0, count($parameters), $value));
        }

        $namedParameters = [];
        $i = 0;

        foreach ($parameters as $parameter) {
            $namedParameters[] = ':' . $value . $i++;
        }

        return implode($separator, $namedParameters);
    }
}
