<?php
declare(strict_types=1);

namespace unit\Manager;

use Codeception\Test\Unit;
use MDO\Dto\Field;
use MDO\Enum\Type;
use MDO\Loader\FieldLoader;
use MDO\Manager\TableManager;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class TableManagerTest extends Unit
{
    use ProphecyTrait;

    private TableManager $tableManager;

    private FieldLoader|ObjectProphecy $fieldLoader;

    protected function setUp(): void
    {
        $this->fieldLoader = $this->prophesize(FieldLoader::class);

        $this->tableManager = new TableManager($this->fieldLoader->reveal());
    }

    public function testGetTable(): void
    {
        $fields = ['arthur' => new Field('arthur', true, Type::BIGINT, '', null, '')];
        $this->fieldLoader->loadFields('galaxy')
            ->shouldBeCalledOnce()
            ->willReturn($fields)
        ;

        $table = $this->tableManager->getTable('galaxy');

        $this->assertEquals('galaxy', $table->getTableName());
        $this->assertEquals($fields, $table->getFields());
        $this->assertEquals($fields['arthur'], $table->getField('arthur'));
    }

    public function testGetKnownTable(): void
    {
        $fields = ['arthur' => new Field('arthur', true, Type::BIGINT, '', null, '')];
        $this->fieldLoader->loadFields('galaxy')
            ->shouldBeCalledOnce()
            ->willReturn($fields)
        ;

        $this->tableManager->getTable('galaxy');
        $table = $this->tableManager->getTable('galaxy');

        $this->assertEquals('galaxy', $table->getTableName());
        $this->assertEquals($fields, $table->getFields());
        $this->assertEquals($fields['arthur'], $table->getField('arthur'));
    }
}
