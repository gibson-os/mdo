<?php
declare(strict_types=1);

namespace MDO\Test\Unit\Service;

use Codeception\Test\Unit;
use MDO\Client;
use MDO\Dto\Field;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Result;
use MDO\Dto\Table;
use MDO\Dto\Value;
use MDO\Enum\Type;
use MDO\Query\ReplaceQuery;
use MDO\Query\SelectQuery;
use MDO\Service\ReplaceService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ReplaceServiceTest extends Unit
{
    use ProphecyTrait;

    private Table $table;

    private Client|ObjectProphecy $client;

    private ReplaceService $replaceService;

    protected function setUp(): void
    {
        $this->table = new Table('galaxy', [
            'arthur' => new Field('arthur', true, Type::BIGINT, 'PRI', null, ''),
            'ford' => new Field('ford', true, Type::VARCHAR, '', null, ''),
        ]);
        $this->client = $this->prophesize(Client::class);

        $this->replaceService = new ReplaceService($this->client->reveal());
    }

    public function testReplaceAndLoadRecord(): void
    {

        $values = ['arthur' => new Value('42'), 'ford' => new Value(null)];
        $replaceQuery = new ReplaceQuery($this->table, $values);
        $this->client->execute($replaceQuery)
            ->shouldBeCalledOnce()
            ->willReturn(null)
        ;
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`t`.`arthur`=?', [42]))
            ->addWhere(new Where('`t`.`ford` IS NULL', []))
        ;
        $result = $this->prophesize(Result::class);
        $record = new Record($values);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;

        $this->assertEquals($record, $this->replaceService->replaceAndLoadRecord($replaceQuery));
    }
}
