<?php
declare(strict_types=1);

namespace MDO\Test\Unit\Query;

use Codeception\Test\Unit;
use MDO\Dto\Field;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Enum\JoinType;
use MDO\Enum\OrderDirection;
use MDO\Enum\Type;
use MDO\Query\DeleteQuery;

class DeleteQueryTest extends Unit
{
    private Table $table;

    protected function setUp(): void
    {
        $this->table = new Table('galaxy', [
            'arthur' => new Field('arthur', true, Type::BIGINT, '', null, ''),
        ]);
    }

    public function testGetQuery(): void
    {
        $query = new DeleteQuery($this->table);

        $this->assertEquals(
            'DELETE `galaxy` FROM `galaxy`  WHERE',
            $query->getQuery(),
        );
        $this->assertEquals(
            [],
            $query->getParameters(),
        );
    }

    public function testGetQueryWithAlias(): void
    {
        $query = new DeleteQuery($this->table, 'g');

        $this->assertEquals(
            'DELETE `g` FROM `galaxy` `g`  WHERE',
            $query->getQuery(),
        );
        $this->assertEquals(
            [],
            $query->getParameters(),
        );
    }

    public function testGetQueryWithWhere(): void
    {
        $query = new DeleteQuery($this->table);
        $query->addWhere(new Where('`arthur`=?', ['dent']));

        $this->assertEquals(
            'DELETE `galaxy` FROM `galaxy`  WHERE (`arthur`=?)',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryWithJoin(): void
    {
        $query = new DeleteQuery($this->table);
        $query
            ->addWhere(new Where('`arthur`=?', ['dent']))
            ->addJoin(new Join(new Table('marvin', []), 'm', '`g`.`id`=`m`.`galaxy_id`'))
            ->addJoin(new Join(new Table('42', []), 'z', '`on`', JoinType::LEFT))
        ;

        $this->assertEquals(
            'DELETE `galaxy` FROM `galaxy` ' .
            'JOIN `marvin` `m` ON `g`.`id`=`m`.`galaxy_id` ' .
            'LEFT JOIN `42` `z` ON `on` ' .
            'WHERE (`arthur`=?)',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryWithOrderBy(): void
    {
        $query = new DeleteQuery($this->table);
        $query
            ->addWhere(new Where('`arthur`=?', ['dent']))
            ->setOrder('`marvin`')
            ->setOrder('`dent`', OrderDirection::DESC)
        ;

        $this->assertEquals(
            'DELETE `galaxy` FROM `galaxy`  WHERE (`arthur`=?) ORDER BY `marvin` ASC, `dent` DESC',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryFull(): void
    {
        $query = new DeleteQuery($this->table, 'g');
        $query
            ->addWhere(new Where('`arthur`=?', ['dent']))
            ->addJoin(new Join(new Table('marvin', []), 'm', '`g`.`id`=`m`.`galaxy_id`'))
            ->addJoin(new Join(new Table('42', []), 'z', '`on`', JoinType::LEFT))
            ->setOrder('`marvin`')
            ->setOrder('`dent`', OrderDirection::DESC)
        ;

        $this->assertEquals(
            'DELETE `g` FROM `galaxy` `g` JOIN `marvin` `m` ON `g`.`id`=`m`.`galaxy_id` LEFT JOIN `42` `z` ON `on` WHERE (`arthur`=?) ORDER BY `marvin` ASC, `dent` DESC',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['dent'],
            $query->getParameters(),
        );
    }

    public function testGetQueryWithLimit(): void
    {
        $query = new DeleteQuery($this->table);
        $query
            ->addWhere(new Where('`arthur`=?', []))
            ->setLimit(1, 42)
        ;

        $this->assertEquals(
            'DELETE `galaxy` FROM `galaxy`  WHERE (`arthur`=?) LIMIT 42, 1',
            $query->getQuery(),
        );

        $query->setOffset(0);

        $this->assertEquals(
            'DELETE `galaxy` FROM `galaxy`  WHERE (`arthur`=?) LIMIT 1',
            $query->getQuery(),
        );

        $query->setRowCount(42);

        $this->assertEquals(
            'DELETE `galaxy` FROM `galaxy`  WHERE (`arthur`=?) LIMIT 42',
            $query->getQuery(),
        );
    }
}
