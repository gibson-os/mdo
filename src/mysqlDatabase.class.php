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
    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $pass;

    /**
     * @var mysqli
     */
    public $Mysqli;

    /**
     * @var string
     */
    public $sql;

    /**
     * @var mysqli_result
     */
    public $Result;

    /**
     * @var string
     */
    private $databaseName;

    public function __construct(string $host, string $user, string $pass)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * @param string|null $database
     *
     * @return bool
     */
    public function openDB(string $database = null): bool
    {
        $this->Mysqli = new mysqli($this->host, $this->user, $this->pass);
        $this->Mysqli->query("SET NAMES 'utf8';");

        if (
            !empty($database) &&
            !$this->useDatabase($database)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $database
     *
     * @return bool
     */
    public function useDatabase(string $database): bool
    {
        if (!$this->Mysqli->select_db($database)) {
            return false;
        }

        $this->databaseName = $database;

        return true;
    }

    /**
     * @return bool
     */
    public function closeDB(): bool
    {
        return $this->Mysqli->close();
    }

    /**
     * @return string
     */
    public function error(): string
    {
        return $this->Mysqli->error;
    }

    /**
     * @param string $query
     *
     * @return bool
     */
    public function sendQuery(string $query): bool
    {
        $this->sql = $query;
        $result = $this->Mysqli->query($this->sql);

        if ($result instanceof mysqli_result) {
            $this->Result = $result;

            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function fetchArray(): array
    {
        return (array) $this->Result->fetch_array();
    }

    /**
     * @return string[]
     */
    public function fetchRow(): array
    {
        return (array) $this->Result->fetch_row();
    }

    /**
     * @return string[]
     */
    public function fetchAssoc(): array
    {
        return (array) $this->Result->fetch_assoc();
    }

    /**
     * @return stdClass|null
     */
    public function fetchObject(): ?stdClass
    {
        $object = $this->Result->fetch_object();

        if (!$object instanceof stdClass) {
            return null;
        }

        return $object;
    }

    /**
     * @param int $field
     *
     * @return string|null
     */
    public function fetchResult(int $field = 0): ?string
    {
        if ($row = $this->fetchRow()) {
            return $row[$field];
        }

        return null;
    }

    /**
     * @return array
     */
    public function fetchArrayList(): array
    {
        $rows = [];

        while ($row = $this->fetchArray()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array
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
     * @return array
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
     * @return array
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
     * @param int $field
     *
     * @return array
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
     * @param mixed $value
     *
     * @return string
     */
    public function escapeWithoutQuotes($value): string
    {
        return $this->Mysqli->real_escape_string($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function escape($value): string
    {
        $value = $this->escapeWithoutQuotes($value);

        return "'" . $value . "'";
    }

    /**
     * @param array  $pieces
     * @param string $glue
     *
     * @return string
     */
    public function implode(array $pieces, string $glue = ','): string
    {
        $data = '';

        foreach ($pieces as $piece) {
            $data .= $this->escape($piece) . $glue;
        }

        return mb_substr($data, 0, 0 - mb_strlen($glue));
    }

    /**
     * @param string $search
     *
     * @return string
     */
    public function getRegexString(string $search): string
    {
        $search = str_replace('.', '\.', $search);
        $search = str_replace('?', '.', $search);
        $search = str_replace('*', '.*', $search);

        return "'[[:<:]]" . $this->Mysqli->real_escape_string($search) . "[[:>:]]'";
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

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }
}
