<?php
declare(strict_types=1);

namespace MDO\Test\Functional\Query;

use MDO\Dto\Value;
use MDO\Query\ReplaceQuery;
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
        $record = $this->replaceService->replaceAndLoadRecord($replaceQuery);

        $this->assertEquals('dent', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testEasyUtf8Record(): void
    {
        $table = $this->tableManager->getTable('arthur');

        $replaceQuery = new ReplaceQuery(
            $table,
            [
                'name' => new Value('Ärthür Dènt'),
                'ford_id' => new Value(42),
                'method' => new Value('GET'),
            ],
        );
        $this->client->execute($replaceQuery);
        $record = $this->replaceService->replaceAndLoadRecord($replaceQuery);

        $this->assertEquals('Ärthür Dènt', $record->get('name')->getValue());
        $this->assertNull($record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }

    public function testEasyBinaryRecord(): void
    {
        $table = $this->tableManager->getTable('arthur');

        $replaceQuery = new ReplaceQuery(
            $table,
            [
                'name' => new Value('arthur'),
                'description' => new Value(chr(190)),
                'ford_id' => new Value(42),
                'method' => new Value('GET'),
            ],
        );
        $this->client->execute($replaceQuery);
        $record = $this->replaceService->replaceAndLoadRecord($replaceQuery);

        $this->assertEquals('arthur', $record->get('name')->getValue());
        $this->assertEquals(chr(190), $record->get('description')->getValue());
        $this->assertEquals(42, $record->get('ford_id')->getValue());
        $this->assertEquals('GET', $record->get('method')->getValue());
    }
}
