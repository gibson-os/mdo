<?php
declare(strict_types=1);

namespace MDO\Test\Unit\Service;

use Codeception\Test\Unit;
use MDO\Dto\Field;
use MDO\Dto\Select;
use MDO\Dto\Table;
use MDO\Enum\Type;
use MDO\Service\SelectService;

class SelectServiceTest extends Unit
{
    private SelectService $selectService;

    protected function setUp(): void
    {

        $this->selectService = new SelectService();
    }

    /**
     * @dataProvider getData
     */
    public function testGetSelects(array $selects, array $expected): void
    {
        $this->assertEquals($expected, $this->selectService->getSelects($selects));
    }

    public function getData(): array
    {
        $table = new Table('galaxy', [
            'arthur' => new Field('arthur', true, Type::BIGINT, 'PRI', null, ''),
            'ford' => new Field('ford', true, Type::VARCHAR, '', null, ''),
        ]);

        return [
            'easy' => [
                [new Select($table, 'g', '')],
                ['arthur' => '`g`.`arthur`', 'ford' => '`g`.`ford`'],
            ],
            'prefix' => [
                [new Select($table, 'g', 'galaxy_')],
                ['galaxy_arthur' => '`g`.`arthur`', 'galaxy_ford' => '`g`.`ford`'],
            ],
            'two tables' => [
                [new Select($table, 'g', 'galaxy_'), new Select($table, 'm', 'marvin_')],
                [
                    'galaxy_arthur' => '`g`.`arthur`', 'galaxy_ford' => '`g`.`ford`',
                    'marvin_arthur' => '`m`.`arthur`', 'marvin_ford' => '`m`.`ford`',
                ],
            ],
        ];
    }
}
