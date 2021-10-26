<?php
declare(strict_types=1);
/**
 * MDO.
 *
 * @author Benjamin Wollenweber
 *
 * @package MDO
 *
 * @copyright 2013
 */
/**
 * MySQL Datenbank.
 */
class mysqlDatabase
{
    public mysqli $Mysqli;

    public string $sql;

    public mysqli_result|null $result;

    private string $databaseName;

    public function __construct(public string $host, public string $user, public string $pass)
    {
    }

    public function openDB(string $database = null): bool
    {
        $this->Mysqli = new mysqli($this->host, $this->user, $this->pass);
        $this->Mysqli->query("SET NAMES 'utf8';");
        $this->Mysqli->query('SET CHARACTER SET utf8;');

        if (
            !empty($database) &&
            !$this->useDatabase($database)
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

        if ($result === false) {
            return false;
        }

        $this->result = null;

        if ($result instanceof mysqli_result) {
            $this->result = $result;
        }

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

            foreach ($parameters as $parameter) {
                if (is_int($parameter)) {
                    $parameterTypes .= 'i';
                } elseif (is_float($parameter)) {
                    $parameterTypes .= 'd';
                } else {
                    $parameterTypes .= 's';
                }
            }

            if (!$statement->bind_param($parameterTypes, ...$parameters)) {
                return false;
            }
        }

        if (!$statement->execute()) {
            return false;
        }

        $result = $statement->get_result();

        $this->result = $result === false ? null : $result;

        return true;
    }

    /**
     * @return array<array-key, string|int|float>
     */
    public function fetchArray(): array
    {
        return (array) $this->result?->fetch_array();
    }

    /**
     * @return array<array-key, string|int|float>
     */
    public function fetchRow(): array
    {
        return (array) $this->result?->fetch_row();
    }

    /**
     * @return array<string, string|int|float>
     */
    public function fetchAssoc(): array
    {
        return (array) $this->result?->fetch_assoc();
    }

    public function fetchObject(): ?stdClass
    {
        $object = $this->result?->fetch_object();

        if (!$object instanceof stdClass) {
            return null;
        }

        return $object;
    }

    public function fetchResult(int $field = 0): string|int|float|null
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
     * @return array<array-key, array<array-key, string|int|float>>
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
     * @return array<array-key, array<string, string|int|float>>
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
     * @return array<array-key, object>
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
    }

    public function commit(): void
    {
        $this->sendQuery('COMMIT');
    }

    public function rollback(): void
    {
        $this->sendQuery('ROLLBACK');
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }
}
