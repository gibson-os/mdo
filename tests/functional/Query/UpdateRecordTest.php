<?php
declare(strict_types=1);

namespace MDO\Test\Functional\Query;

use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Value;
use MDO\Query\ReplaceQuery;
use MDO\Query\SelectQuery;
use MDO\Test\Functional\AbstractFunctionalTest;

class UpdateRecordTest extends AbstractFunctionalTest
{
    public function testUpdateRecordWithId(): void
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
        $record = $this->replaceService->replaceAndLoadRecord($replaceQuery);
        $id = $record->get('id')->getValue();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());

        $record->get('description')->setValue('galaxy');
        $replaceQuery = new ReplaceQuery($table, $record->getValues());
        $this->client->execute($replaceQuery);

        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`name`=?', ['dent']))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertEquals($id, $record->get('id')->getValue());
        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertEquals('galaxy', $record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testUpdateRecordWithIdUniqueChanged(): void
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
        $record = $this->replaceService->replaceAndLoadRecord($replaceQuery);
        $id = $record->get('id')->getValue();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());

        $record->get('name')->setValue('galaxy');
        $replaceQuery = new ReplaceQuery($table, $record->getValues());
        $this->client->execute($replaceQuery);

        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`name`=?', ['galaxy']))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertEquals($id, $record->get('id')->getValue());
        $this->assertEquals('galaxy', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testUpdateRecordWithoutId(): void
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
        $record = $this->replaceService->replaceAndLoadRecord($replaceQuery);
        $id = $record->get('id')->getValue();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());

        $record->get('description')->setValue('galaxy');
        $newValues = [];

        foreach ($record->getValues() as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            $newValues[$key] = $value;
        }

        $replaceQuery = new ReplaceQuery($table, $newValues);
        $this->client->execute($replaceQuery);

        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`name`=?', ['dent']))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertEquals($id, $record->get('id')->getValue());
        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertEquals('galaxy', $record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testUpdateRecordWithoutIdUniqueChanged(): void
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
        $record = $this->replaceService->replaceAndLoadRecord($replaceQuery);
        $id = $record->get('id')->getValue();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());

        $record->get('name')->setValue('galaxy');
        $newValues = [];

        foreach ($record->getValues() as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            $newValues[$key] = $value;
        }

        $replaceQuery = new ReplaceQuery($table, $newValues);
        $this->client->execute($replaceQuery);

        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`name`=?', ['galaxy']))
        ;
        $result = $this->client->execute($selectQuery);
        /** @var Record $record */
        $record = $result->iterateRecords()->current();

        $this->assertNotEquals($id, $record->get('id')->getValue());
        $this->assertEquals('galaxy', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }
}
