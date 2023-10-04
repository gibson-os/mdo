<?php
declare(strict_types=1);

namespace MDO\Test\Functional\Query;

use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Value;
use MDO\Query\ReplaceQuery;
use MDO\Query\SelectQuery;
use MDO\Test\Functional\AbstractFunctionalTest;

class EasyRecordTest extends AbstractFunctionalTest
{
    public function testEasyRecord(): void
    {
        $table = $this->tableManager->getTable('arthur');

        $replaceQuery = new ReplaceQuery(
            $table,
            [
                'name' => new Value('dent'),
                'ford_id' => new Value(42),
                'method' => new Value('GET'),
            ],
        );
        $this->client->execute($replaceQuery);

        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`name`=?', ['dent']))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }
}
