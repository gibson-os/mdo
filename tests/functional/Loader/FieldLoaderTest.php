<?php
declare(strict_types=1);

namespace MDO\Test\Functional\Loader;

use MDO\Dto\Field;
use MDO\Enum\Type;
use MDO\Loader\FieldLoader;
use MDO\Test\Functional\AbstractFunctionalTest;

class FieldLoaderTest extends AbstractFunctionalTest
{
    private FieldLoader $fieldLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fieldLoader = new FieldLoader($this->client);
    }

    public function testLoadFields(): void
    {
        $this->assertEquals(
            [
                'id' => new Field('id', false, Type::BIGINT, 'PRI', null, 'auto_increment', 20),
                'name' => new Field('name', false, Type::VARCHAR, 'MUL', null, '', 32),
                'description' => new Field('description', true, Type::LONGTEXT, '', null, ''),
                'ford_id' => new Field('ford_id', false, Type::BIGINT, '', null, '', 20),
                'method' => new Field('method', false, Type::ENUM, '', null, ''),
            ],
            $this->fieldLoader->loadFields('arthur'),
        );
        $this->assertEquals(
            [
                'id' => new Field('id', false, Type::BIGINT, 'PRI', null, 'auto_increment', 20),
                'name' => new Field('name', false, Type::VARCHAR, 'UNI', null, '', 32),
            ],
            $this->fieldLoader->loadFields('ford'),
        );
    }
}
