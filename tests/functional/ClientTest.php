<?php
declare(strict_types=1);

namespace MDO\Test\Functional;

use MDO\Dto\Query\Where;
use MDO\Dto\Value;
use MDO\Query\ReplaceQuery;
use MDO\Query\SelectQuery;

class ClientTest extends AbstractFunctionalTest
{
    public function testNamedParameter(): void
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
            ->addWhere(new Where('`name`=:name', ['name' => 'dent']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testNamedParameters(): void
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
            ->addWhere(new Where('`name`=:name AND `method`=:method', ['method' => 'GET', 'name' => 'dent']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testNamedParametersMultipleUse(): void
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
            ->addWhere(new Where('`name`=:name AND `name`=:name', ['name' => 'dent']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testMixedParameters(): void
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
            ->addWhere(new Where('`name`=? AND `method`=:method', ['dent', 'method' => 'GET']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testMixedParametersReverseParameter(): void
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
            ->addWhere(new Where('`name`=? AND `method`=:method', ['method' => 'GET', 'dent']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testMixedParametersChangedOrder(): void
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
            ->addWhere(new Where('`method`=:method AND `name`=?', ['method' => 'GET', 'dent']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testMixedParametersChangedOrderReverseParameter(): void
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
            ->addWhere(new Where('`method`=:method AND `name`=?', ['dent', 'method' => 'GET']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testMixedParametersMultipleWhere(): void
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
            ->addWhere(new Where('`method`=:method', ['method' => 'GET']))
            ->addWhere(new Where('`name`=?', ['dent']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testMixedParametersMultipleWhereReverseOrder(): void
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
            ->addWhere(new Where('`method`=:method', ['method' => 'GET']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testMultipleWhere(): void
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
            ->addWhere(new Where('`name`=:name', ['name' => 'dent']))
            ->addWhere(new Where('`method`=:method', ['method' => 'GET']))
        ;
        $result = $this->client->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }
}
