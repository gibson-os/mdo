<?php
declare(strict_types=1);

namespace MDO\Test\Functional\Query;

use MDO\Dto\Query\Join;
use MDO\Dto\Record;
use MDO\Dto\Select;
use MDO\Dto\Value;
use MDO\Query\ReplaceQuery;
use MDO\Query\SelectQuery;
use MDO\Service\SelectService;
use MDO\Test\Functional\AbstractFunctionalTest;

class JoinRecordTest extends AbstractFunctionalTest
{
    public function testJoinRecord(): void
    {
        $selectService = new SelectService();
        $tableArthur = $this->tableManager->getTable('arthur');
        $tableFord = $this->tableManager->getTable('ford');

        $fordRecord = $this->replaceService->replaceAndLoadRecord(
            new ReplaceQuery(
                $tableFord,
                [
                    'name' => new Value('prefect'),
                ],
            ),
        );
        $arthurRecord = $this->replaceService->replaceAndLoadRecord(
            new ReplaceQuery(
                $tableArthur,
                [
                    'name' => new Value('dent'),
                    'ford_id' => new Value($fordRecord->get('id')->getValue()),
                    'method' => new Value('GET'),
                ],
            ),
        );

        $this->assertEquals('dent', $arthurRecord->get('name')->getValue());
        $this->assertNull($arthurRecord->get('description')->getValue());
        $this->assertEquals($fordRecord->get('id')->getValue(), $arthurRecord->get('ford_id')->getValue());
        $this->assertEquals('GET', $arthurRecord->get('method')->getValue());
        $this->assertEquals('prefect', $fordRecord->get('name')->getValue());

        $selectQuery = (new SelectQuery($tableArthur, 'a'))
            ->addJoin(new Join($tableFord, 'f', '`a`.`ford_id`=`f`.`id`'))
            ->setSelects($selectService->getSelects([
                new Select($tableArthur, 'a', 'arthur_'),
                new Select($tableFord, 'f', 'ford_'),
            ]))
        ;
        /** @var Record $record */
        $record = $this->client->execute($selectQuery)->iterateRecords()->current();

        $arthurValues = $record->getValues('arthur_');
        $this->assertEquals('dent', $arthurValues['name']->getValue());
        $this->assertNull($arthurValues['description']->getValue());
        $this->assertEquals($fordRecord->get('id')->getValue(), $arthurValues['ford_id']->getValue());
        $this->assertEquals('GET', $arthurValues['method']->getValue());

        $fordValues = $record->getValues('ford_');
        $this->assertEquals('prefect', $fordValues['name']->getValue());

        $values = $record->getValues();
        $this->assertEquals('dent', $values['arthur_name']->getValue());
        $this->assertNull($values['arthur_description']->getValue());
        $this->assertEquals($fordRecord->get('id')->getValue(), $values['arthur_ford_id']->getValue());
        $this->assertEquals('GET', $values['arthur_method']->getValue());
        $this->assertEquals('prefect', $values['ford_name']->getValue());
    }
}
