<?php
declare(strict_types=1);

namespace MDO\Service;

use MDO\Client;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Exception\ClientException;
use MDO\Query\ReplaceQuery;
use MDO\Query\SelectQuery;

class ReplaceService
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws ClientException
     */
    public function replaceAndLoadRecord(ReplaceQuery $replaceQuery): Record
    {
        $this->client->execute($replaceQuery);
        $result = $this->client->execute($this->getSelectQuery($replaceQuery));

        if ($result === null) {
            throw new ClientException('Query returns no result!');
        }

        return $result->iterateRecords()->current();
    }

    public function getSelectQuery(ReplaceQuery $replaceQuery, string $alias = 't'): SelectQuery
    {
        $table = $replaceQuery->getTable();
        $selectQuery = new SelectQuery($table, $alias);
        $values = $replaceQuery->getValues();

        foreach ($table->getFields() as $field) {
            $value = $values[$field->getName()] ?? null;

            if ($value === null) {
                continue;
            }

            if ($value->getValue() === null) {
                $selectQuery->addWhere(new Where(sprintf('`%s`.`%s` IS NULL', $alias, $field->getName()), []));

                continue;
            }

            if (!$value->hasParameter()) {
                continue;
            }

            $selectQuery->addWhere(new Where(sprintf('`%s`.`%s`=?', $alias, $field->getName()), [$value->getValue()]));
        }

        return $selectQuery;
    }
}
