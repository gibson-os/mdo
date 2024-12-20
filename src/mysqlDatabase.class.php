<?php
declare(strict_types=1);
/**
 * @deprecated
 */
class mysqlDatabase
{
    public mysqli $Mysqli;

    public string $sql;

    public ?mysqli_result $result;

    private string $databaseName;

    private bool $transaction = false;

    public function __construct(public string $host, public string $user, public string $pass)
    {
    }

    public function openDB(?string $database = null): bool
    {
        $this->Mysqli = new mysqli($this->host, $this->user, $this->pass);
        $this->Mysqli->query("SET NAMES 'utf8';");
        $this->Mysqli->query('SET CHARACTER SET utf8;');

        if (
            !empty($database)
            && !$this->useDatabase($database)
        ) {
            return false;
        }

        return true;
    }

    public function useDatabase(string $database): bool
    {
        if (!$this->Mysqli->select_db($database)) {
            return false;
        }

        $this->databaseName = $database;

        return true;
    }

    public function closeDB(): bool
    {
        return $this->Mysqli->close();
    }

    public function error(): string
    {
        return $this->Mysqli->error;
    }

    public function sendQuery(string $query): bool
    {
        $this->sql = $query;
        $result = $this->Mysqli->query($this->sql);
        $this->result = null;

        if ($result === true) {
            return true;
        }

        if (!$result instanceof mysqli_result) {
            return false;
        }

        $this->result = $result;

        return true;
    }

    public function execute(string $query, array $parameters): bool
    {
        $this->sql = $query;
        $statement = $this->Mysqli->prepare($this->sql);

        if (!$statement instanceof mysqli_stmt) {
            return false;
        }

        if (count($parameters)) {
            $parameterTypes = '';
            $longData = [];

            preg_match_all('/:\w+/', $query, $namedParameters);
            $namedParameters = $namedParameters[0];

            if (count($namedParameters)) {
                preg_replace('/:\w+/', '?', $query);
                $newParameters = [];

                foreach ($namedParameters as $namedParameter) {
                    $newParameters[] = $parameters[$namedParameter];
                }

                $parameters = $newParameters;
            }

            foreach ($parameters as $index => $parameter) {
                if (is_int($parameter)) {
                    $parameterTypes .= 'i';
                } elseif (is_float($parameter)) {
                    $parameterTypes .= 'd';
                } elseif (is_object($parameter) && enum_exists($parameter::class)) {
                    $parameterTypes .= 's';
                    $parameters[$index] = $parameter->name;
                } else {
                    $length = strlen($parameter);

                    for ($i = 0; $i < $length; ++$i) {
                        if (ord($parameter[$i]) > 127) {
                            $parameterTypes .= 'b';
                            $longData[$index] = $parameter;

                            continue 2;
                        }
                    }

                    $parameterTypes .= 's';
                }
            }

            if (!$statement->bind_param($parameterTypes, ...$parameters)) {
                return false;
            }

            foreach ($longData as $index => $data) {
                if (!$statement->send_long_data($index, $data)) {
                    return false;
                }
            }
        }

        if (!$statement->execute()) {
            return false;
        }

        $result = $statement->get_result();

        $this->result = $result === false ? null : $result;

        return true;
    }

    public function fetchArray(): array|false|null
    {
        return $this->result?->fetch_array();
    }

    public function fetchRow(): array|false|null
    {
        return $this->result?->fetch_row();
    }

    public function fetchAssoc(): array|false|null
    {
        return $this->result?->fetch_assoc();
    }

    public function fetchObject(): ?stdClass
    {
        $object = $this->result?->fetch_object();

        if (!$object instanceof stdClass) {
            return null;
        }

        return $object;
    }

    public function fetchResult(int $field = 0): string|int|float|false|null
    {
        if ($row = $this->fetchRow()) {
            return $row[$field];
        }

        return null;
    }

    public function fetchArrayList(): array
    {
        $rows = [];

        while ($row = $this->fetchArray()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<array-key, array<array-key, string|int|float|false|null>>
     */
    public function fetchRowList(): array
    {
        $rows = [];

        while ($row = $this->fetchRow()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<array-key, array<0|string, string|int|float|false|null>>
     */
    public function fetchAssocList(): array
    {
        $rows = [];

        while ($row = $this->fetchAssoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<array-key, stdClass>
     */
    public function fetchObjectList(): array
    {
        $rows = [];

        while ($row = $this->fetchObject()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<array-key, string|int|float|null>
     */
    public function fetchResultList(int $field = 0): array
    {
        $rows = [];

        while ($row = $this->fetchResult($field)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @deprecated
     */
    public function escapeWithoutQuotes(string $value): string
    {
        return $this->Mysqli->real_escape_string($value);
    }

    /**
     * @deprecated
     */
    public function escape(string $value): string
    {
        $value = $this->escapeWithoutQuotes($value);

        return "'" . $value . "'";
    }

    /**
     * @deprecated
     */
    public function implode(array $pieces, string $glue = ','): string
    {
        $data = '';

        foreach ($pieces as $piece) {
            $data .= $this->escape((string) $piece) . $glue;
        }

        return mb_substr($data, 0, 0 - mb_strlen($glue));
    }

    public function getRegexString(string $search): string
    {
        $search = str_replace('.', '\.', $search);
        $search = str_replace('?', '.', $search);
        $search = str_replace('*', '.*', $search);

        return "'[[:<:]]" . $this->Mysqli->real_escape_string($search) . "[[:>:]]'";
    }

    public function getUnescapedRegexString(string $search): string
    {
        $search = str_replace('.', '\.', $search);
        $search = str_replace('?', '.', $search);
        $search = str_replace('*', '.*', $search);

        return '[[:<:]]' . $search . '[[:>:]]';
    }

    public function startTransaction(): void
    {
        $this->sendQuery('START TRANSACTION');
        $this->transaction = true;
    }

    public function commit(): void
    {
        $this->sendQuery('COMMIT');
        $this->transaction = false;
    }

    public function rollback(): void
    {
        $this->sendQuery('ROLLBACK');
        $this->transaction = false;
    }

    public function isTransaction(): bool
    {
        return $this->transaction;
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }
}
