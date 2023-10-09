<?php
declare(strict_types=1);

namespace MDO\Test\Unit\Query;

use Codeception\Test\Unit;
use MDO\Dto\Field;
use MDO\Dto\Table;
use MDO\Dto\Value;
use MDO\Enum\Type;
use MDO\Enum\ValueType;
use MDO\Query\ReplaceQuery;

class ReplaceQueryTest extends Unit
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
        $query = new ReplaceQuery($this->table, []);

        $this->assertEquals(
            'INSERT INTO `galaxy` SET  ON DUPLICATE KEY UPDATE',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithValues(): void
    {
        $query = new ReplaceQuery($this->table, [
            'marvin' => new Value(42),
            'arthur' => new Value('dent'),
            'ford' => new Value(null),
            'zaphord' => new Value('bebblebrox'),
            '42' => new Value('42', ValueType::FUNCTION),
        ]);

        $this->assertEquals(
            'INSERT INTO `galaxy` SET `marvin`=:marvin, `arthur`=:arthur, `ford`=NULL, `42`=42 ON DUPLICATE KEY UPDATE `marvin`=:marvin, `arthur`=:arthur, `ford`=NULL, `42`=42',
            $query->getQuery(),
        );
        $this->assertEquals(
            ['marvin' => 42, 'arthur' => 'dent'],
            $query->getParameters(),
        );
    }
}
