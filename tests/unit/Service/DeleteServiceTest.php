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
use MDO\Query\DeleteQuery;
use MDO\Service\DeleteService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DeleteServiceTest extends Unit
{
    use ProphecyTrait;

    private ObjectProphecy|Client $client;

    private Table $table;

    private DeleteService $deleteService;

    protected function setUp(): void
    {
        $this->table = new Table('galaxy', [
            'arthur' => new Field('arthur', true, Type::BIGINT, 'PRI', null, ''),
            'ford' => new Field('ford', true, Type::VARCHAR, '', null, ''),
        ]);
        $this->client = $this->prophesize(Client::class);

        $this->deleteService = new DeleteService($this->client->reveal());
    }

    public function testDeleteRecord(): void
    {
        $deleteQuery = (new DeleteQuery($this->table))
            ->addWhere(new Where('`arthur`=?', [42]))
        ;
        $this->client->execute($deleteQuery)
            ->shouldBeCalledOnce()
            ->willReturn(new Result(null))
        ;
        $this->deleteService->deleteRecord($this->table, new Record(['arthur' => new Value(42), 'ford' => new Value(null)]));
    }
}
