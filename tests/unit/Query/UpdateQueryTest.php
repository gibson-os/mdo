<?php
declare(strict_types=1);

namespace MDO\Test\Unit\Query;

use Codeception\Test\Unit;
use MDO\Dto\Field;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Dto\Value;
use MDO\Enum\JoinType;
use MDO\Enum\OrderDirection;
use MDO\Enum\Type;
use MDO\Query\UpdateQuery;

class UpdateQueryTest extends Unit
{
    private Table $table;

    protected function setUp(): void
    {
        $this->table = new Table('galaxy', [
            'marvin' => new Field('marvin', true, Type::BIGINT, 'PRI', null, ''),
            'arthur' => new Field('arthur', true, Type::VARCHAR, '', null, ''),
            'ford' => new Field('ford', true, Type::VARCHAR, '', null, ''),
            'trillian' => new Field('trillian', true, Type::VARCHAR, '', null, ''),
            '42' => new Field('42', true, Type::VARCHAR, '', null, ''),
        ]);
    }

    public function testGetQuery(): void
    {
        $query = new UpdateQuery($this->table, ['arthur' => new Value('dent')]);

        $this->assertEquals(
            'UPDATE `galaxy`  SET `arthur`=:arthur WHERE',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['arthur' => 'dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryWithWhere(): void
    {
        $query = (new UpdateQuery($this->table, ['arthur' => new Value('dent')]))
            ->addWhere(new Where('`arthur`=?', ['arthur']))
        ;

        $this->assertEquals(
            'UPDATE `galaxy`  SET `arthur`=:arthur WHERE (`arthur`=?)',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['arthur', 'arthur' => 'dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryWithOrder(): void
    {
        $query = (new UpdateQuery($this->table, ['arthur' => new Value('dent')]))
            ->setOrder('`arthur`', OrderDirection::DESC)
        ;

        $this->assertEquals(
            'UPDATE `galaxy`  SET `arthur`=:arthur WHERE  ORDER BY `arthur` DESC',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['arthur' => 'dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryWithLimit(): void
    {
        $query = (new UpdateQuery($this->table, ['arthur' => new Value('dent')]))
            ->setLimit(1, 42)
        ;

        $this->assertEquals(
            'UPDATE `galaxy`  SET `arthur`=:arthur WHERE  LIMIT 42, 1',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['arthur' => 'dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryJoin(): void
    {
        $query = (new UpdateQuery($this->table, ['arthur' => new Value('dent')], 'g'))
            ->addJoin(new Join(new Table('marvin', []), 'm', '`g`.`id`=`m`.`galaxy_id`'))
            ->addJoin(new Join(new Table('42', []), 'z', '`on`', JoinType::LEFT))
        ;

        $this->assertEquals(
            'UPDATE `galaxy` `g` ' .
            'JOIN `marvin` `m` ON `g`.`id`=`m`.`galaxy_id` ' .
            'LEFT JOIN `42` `z` ON `on` ' .
            'SET `g`.`arthur`=:arthur WHERE',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['arthur' => 'dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryFull(): void
    {
        $query = (new UpdateQuery($this->table, ['arthur' => new Value('dent')], 'g'))
            ->addJoin(new Join(new Table('marvin', []), 'm', '`g`.`id`=`m`.`galaxy_id`'))
            ->addJoin(new Join(new Table('42', []), 'z', '`on`', JoinType::LEFT))
            ->addWhere(new Where('`arthur`=?', ['arthur']))
            ->setLimit(1, 42)
            ->setOrder('`arthur`', OrderDirection::DESC)
        ;

        $this->assertEquals(
            'UPDATE `galaxy` `g` ' .
            'JOIN `marvin` `m` ON `g`.`id`=`m`.`galaxy_id` ' .
            'LEFT JOIN `42` `z` ON `on` ' .
            'SET `g`.`arthur`=:arthur ' .
            'WHERE (`arthur`=?) ' .
            'ORDER BY `arthur` DESC ' .
            'LIMIT 42, 1',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['arthur', 'arthur' => 'dent'],
            $query->getParameters(),
        );
    }
}
