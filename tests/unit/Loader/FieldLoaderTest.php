<?php
declare(strict_types=1);

namespace unit\Loader;

use Codeception\Test\Unit;
use MDO\Client;
use MDO\Dto\Field;
use MDO\Dto\Result;
use MDO\Enum\Type;
use MDO\Loader\FieldLoader;
use mysqli_result;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class FieldLoaderTest extends Unit
{
    use ProphecyTrait;

    private FieldLoader $fieldLoader;

    private Client|ObjectProphecy $client;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(Client::class);

        $this->fieldLoader = new FieldLoader($this->client->reveal());
    }

    public function testLoadFields(): void
    {
        $result = $this->prophesize(mysqli_result::class);
        $this->client->execute('SHOW FIELDS FROM `galaxy`')
            ->shouldBeCalledOnce()
            ->willReturn(new Result($result->reveal()))
        ;
        $result->fetch_assoc()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                [
                    'Field' => 'arthur',
                    'Type' => 'bigint(20) unsigned',
                    'Null' => 'NO',
                    'Key' => 'PRI',
                    'Default' => null,
                    'Extra' => 'auto_increment',
                ],
                false,
            )
        ;

        $this->assertEquals(
            ['arthur' => new Field('arthur', false, Type::BIGINT, 'PRI', null, 'auto_increment', 20)],
            $this->fieldLoader->loadFields('galaxy'),
        );
    }
}
