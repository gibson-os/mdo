<?php
declare(strict_types=1);

namespace MDO;

use MDO\Dto\Result;
use MDO\Exception\ClientException;
use MDO\Query\QueryInterface;
use mysqli;
use mysqli_stmt;

class Client
{
    private mysqli $mysqli;

    private string $databaseName;

    public function __construct(
        private readonly string $host,
        private readonly string $user,
        private readonly string $password,
        string $databaseName = null,
    ) {
        $this->mysqli = new mysqli($this->host, $this->user, $this->password);
        $this->execute('SET NAMES "utf8"');
        $this->execute('SET CHARACTER SET utf8');

        if ($databaseName !== null) {
            $this->useDatabase($databaseName);
        }
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
    public function execute(string|QueryInterface $query, array $parameters = []): ?Result
    {
        if ($query instanceof QueryInterface) {
            $parameters = $query->getParameters();
            $query = $query->getQuery();
        }

        $statement = $this->mysqli->prepare($query);

        if (!$statement instanceof mysqli_stmt) {
            throw new ClientException(sprintf(
                'Prepare error. Query: %s',
                $query,
            ));
        }

        if (count($parameters)) {
            $this->setParameters($query, $parameters, $statement);
        }

        if (!$statement->execute()) {
            throw new ClientException(sprintf(
                'Error: %s | Query: %s',
                $this->getError(),
                $query,
            ));
        }

        $result = $statement->get_result();

        return $result === false ? null : new Result($result);
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * @throws ClientException
     */
    private function setParameters(string $query, array $parameters, mysqli_stmt $statement): void
    {
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
                    'Send long data error. Indes: %d | Data: %s | Query: %s',
                    $index,
                    $data,
                    $query,
                ));
            }
        }
    }

    private function getParameterTypes(array $parameters, array $longData): string
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
}
