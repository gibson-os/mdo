<?php
declare(strict_types=1);

namespace MDO\Service;

use InvalidArgumentException;
use MDO\Client;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Table;
use MDO\Exception\ClientException;
use MDO\Query\DeleteQuery;

class DeleteService
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws ClientException
     */
    public function deleteRecord(Table $table, Record $record): void
    {
        $this->client->execute($this->getDeleteQuery($table, $record));
    }

    public function getDeleteQuery(Table $table, Record $record): DeleteQuery
    {
        $deleteQuery = new DeleteQuery($table);
        $values = $record->getValues();

        foreach ($table->getFields() as $field) {
            if (!$field->isPrimary()) {
                continue;
            }

            $fieldName = $field->getName();
            $value = $values[$fieldName]?->getValue();

            if ($value === null) {
                throw new InvalidArgumentException(sprintf('Primary key %s is null', $fieldName));
            }

            $deleteQuery->addWhere(new Where(sprintf('`%s`=?', $fieldName), [$value]));
        }

        return $deleteQuery;
    }
}
