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
            static fn (Field $field): string|int|float|null => $record->get($prefix . $field->getName())->getValue(),
            $table->getPrimaryFields(),
        );
    }

    public function extractNames(Table $table): array
    {
        return array_map(
            static fn (Field $field): string => $field->getName(),
            $table->getPrimaryFields(),
        );
    }
}
