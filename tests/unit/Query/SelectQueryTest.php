<?php
declare(strict_types=1);

namespace unit\Query;

use Codeception\Test\Unit;
use MDO\Dto\Field;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Enum\JoinType;
use MDO\Enum\OrderDirection;
use MDO\Enum\Type;
use MDO\Query\SelectQuery;

class SelectQueryTest extends Unit
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
        $query = new SelectQuery($this->table);

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy`',
            $query->getQuery(),
        );
    }

    public function testGetQueryOverwriteSelect(): void
    {
        $query = new SelectQuery($this->table);
        $query->setSelect('MAX(`arthur`)', 'arthur');

        $this->assertEquals(
            'SELECT (MAX(`arthur`)) `arthur` FROM `galaxy`',
            $query->getQuery(),
        );
    }

    public function testGetQueryAddSelect(): void
    {
        $query = new SelectQuery($this->table);
        $query->setSelect('MAX(`dent`)', 'dent');

        $this->assertEquals(
            'SELECT (`arthur`) `arthur`, (MAX(`dent`)) `dent` FROM `galaxy`',
            $query->getQuery(),
        );
    }

    public function testGetQuerySetSelects(): void
    {
        $query = new SelectQuery($this->table);
        $query->setSelects([
            'arthur' => 'MIN(`arthur`)',
            'dent' => 'MAX(`dent`)',
        ]);

        $this->assertEquals(
            'SELECT (MIN(`arthur`)) `arthur`, (MAX(`dent`)) `dent` FROM `galaxy`',
            $query->getQuery(),
        );
    }

    public function testGetQueryJoin(): void
    {
        $query = new SelectQuery($this->table, 'g');
        $query
            ->setJoin(new Join(new Table('marvin', []), 'm', '`g`.`id`=`m`.`galaxy_id`'))
            ->setJoin(new Join(new Table('42', []), 'z', '`on`', JoinType::LEFT))
        ;

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy` `g` ' .
            'JOIN `marvin` `m` ON `g`.`id`=`m`.`galaxy_id` ' .
            'LEFT JOIN `42` `z` ON `on`',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithWhere(): void
    {
        $query = new SelectQuery($this->table);
        $query->addWhere(new Where('`arthur`=?', []));

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy`  WHERE (`arthur`=?)',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithGroupBy(): void
    {
        $query = new SelectQuery($this->table);
        $query->setGroupBy(['`arthur`', '`dent`']);

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy`  GROUP BY `arthur`, `dent`',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithGroupByHaving(): void
    {
        $query = new SelectQuery($this->table);
        $query->setGroupBy(['`arthur`', '`dent`'], '`marvin`=?');

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy`  GROUP BY `arthur`, `dent` HAVING `marvin`=?',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithOrder(): void
    {
        $query = new SelectQuery($this->table);
        $query
            ->setOrder('`marvin`')
            ->setOrder('`dent`', OrderDirection::DESC)
        ;

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy`  ORDER BY `marvin` ASC, `dent` DESC',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithLimit(): void
    {
        $query = new SelectQuery($this->table);
        $query->setLimit(1, 42);

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy`  LIMIT 42, 1',
            $query->getQuery(),
        );

        $query->setOffset(0);

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy`  LIMIT 1',
            $query->getQuery(),
        );

        $query->setRowCount(42);

        $this->assertEquals(
            'SELECT (`arthur`) `arthur` FROM `galaxy`  LIMIT 42',
            $query->getQuery(),
        );
    }
}
