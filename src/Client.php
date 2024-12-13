<?php
declare(strict_types=1);

namespace MDO;

use MDO\Dto\Result;
use MDO\Exception\ClientException;
use MDO\Query\QueryInterface;
use mysqli;
use mysqli_sql_exception;
use mysqli_stmt;

class Client
{
    private mysqli $mysqli;

    private ?string $databaseName = null;

    private bool $transaction = false;

    /**
     * @throws ClientException
     */
    public function __construct(string $host, string $user, string $password, ?string $databaseName = null)
    {
        $this->connect($host, $user, $password);

        if ($databaseName !== null) {
            $this->useDatabase($databaseName);
        }
    }

    public function connect(string $host, string $user, string $password): void
    {
        $this->mysqli = new mysqli($host, $user, $password);
        $this->mysqli->set_charset('utf8');
    }

    public function useDatabase(string $databaseName): bool
    {
        if (!$this->mysqli->select_db($databaseName)) {
            return false;
        }

        $this->databaseName = $databaseName;

        return true;
    }

    public function close(): bool
    {
        return $this->mysqli->close();
    }

    public function getError(): string
    {
        return $this->mysqli->error;
    }

    /**
     * @throws ClientException
     */
    public function execute(string|QueryInterface $query, array $parameters = []): Result
    {
        if ($query instanceof QueryInterface) {
            $parameters = $query->getParameters();
            $query = $query->getQuery();
        }

        $statement = $this->mysqli->prepare($this->replaceNamedParameters($query));

        if (!$statement instanceof mysqli_stmt) {
            throw new ClientException(sprintf(
                'Prepare error. Query: %s',
                $query,
            ));
        }

        if (count($parameters)) {
            $this->setParameters($query, $parameters, $statement);
        }

        try {
            if (!$statement->execute()) {
                throw new ClientException(sprintf(
                    'Error: %s | Query: %s',
                    $this->getError(),
                    $query,
                ));
            }
        } catch (mysqli_sql_exception $exception) {
            throw new ClientException($exception->getMessage(), previous: $exception);
        }

        $result = $statement->get_result();

        return new Result($result ?: null);
    }

    public function getDatabaseName(): ?string
    {
        return $this->databaseName;
    }

    private function replaceNamedParameters(string $query): string
    {
        return preg_replace('/:\w+/', '?', $query);
    }

    /**
     * @throws ClientException
     */
    private function setParameters(string $query, array $parameters, mysqli_stmt $statement): void
    {
        $longData = [];

        preg_match_all('/(:\w+|\?)/', $query, $namedParameters);
        $namedParameters = $namedParameters[0];

        if (count($namedParameters)) {
            $parameterCount = 0;
            $newParameters = [];

            foreach ($namedParameters as $namedParameter) {
                if ($namedParameter === '?') {
                    $newParameters[] = $parameters[$parameterCount++];

                    continue;
                }

                $newParameters[] = $parameters[mb_substr($namedParameter, 1)];
            }

            $parameters = $newParameters;
        }

        $parameterTypes = $this->getParameterTypes($parameters, $longData);

        if (!$statement->bind_param($parameterTypes, ...$parameters)) {
            throw new ClientException(sprintf(
                'Parameter bind error. Types: %s | Parameters: %s | Query: %s',
                $parameterTypes,
                implode(', ', $parameters),
                $query,
            ));
        }

        foreach ($longData as $index => $data) {
            if (!$statement->send_long_data($index, $data)) {
                throw new ClientException(sprintf(
                    'Send long data error. %s. Indes: %d | Data: %s | Query: %s',
                    $statement->error,
                    $index,
                    $data,
                    $query,
                ));
            }
        }
    }

    private function getParameterTypes(array $parameters, array &$longData): string
    {
        $parameterTypes = '';

        foreach ($parameters as $index => $parameter) {
            if (is_int($parameter)) {
                $parameterTypes .= 'i';
            } elseif (is_float($parameter)) {
                $parameterTypes .= 'd';
            } elseif (is_object($parameter) && enum_exists($parameter::class)) {
                $parameterTypes .= 's';
                $parameters[$index] = $parameter->name;
            } elseif (mb_detect_encoding($parameter) !== false) {
                $parameterTypes .= 's';
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

        return $parameterTypes;
    }

    public function startTransaction(): void
    {
        if ($this->transaction) {
            return;
        }

        $this->mysqli->query('START TRANSACTION');
        $this->transaction = true;
    }

    public function commit(): void
    {
        if (!$this->transaction) {
            return;
        }

        $this->mysqli->query('COMMIT');
        $this->transaction = false;
    }

    public function rollback(): void
    {
        if (!$this->transaction) {
            return;
        }

        $this->mysqli->query('ROLLBACK');
        $this->transaction = false;
    }

    public function isTransaction(): bool
    {
        return $this->transaction;
    }
}
