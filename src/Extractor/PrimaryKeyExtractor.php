<?php
declare(strict_types=1);

namespace MDO\Extractor;

use MDO\Dto\Field;
use MDO\Dto\Record;
use MDO\Dto\Table;

class PrimaryKeyExtractor
{
    public function extractFromRecord(Table $table, Record $record, string $prefix = ''): array
    {
        return array_map(
            fn (Field $field): string|int|float|null => $record->get($prefix . $field->getName())->getValue(),
            $table->getPrimaryFields(),
        );
    }
}
