<?php
declare(strict_types=1);

namespace MDO\Loader;

use MDO\Client;
use MDO\Dto\Field;
use MDO\Enum\Type;
use MDO\Exception\ClientException;

class FieldLoader
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws ClientException
     */
    public function loadFields(string $tableName): array
    {
        $result = $this->client->execute(sprintf('SHOW FIELDS FROM `%s`', $tableName));
        $fields = [];

        foreach ($result->iterateRecords() as $field) {
            $type = $field->get('Type');
            $length = 0;

            if (preg_match('/\(\d*\)/', (string) $field->get('Type'), $fieldLength, PREG_OFFSET_CAPTURE)) {
                $length = substr($fieldLength[0][0], 1, strlen($fieldLength[0][0]) - 2);
                $type = preg_replace('/\(\d*\)/', '', (string) $field->get('Type'));
            }

            $fields[] = new Field(
                (string) $field->get('Field'),
                mb_strtolower((string) $field->get('Null')) === 'yes',
                constant(sprintf('%s::%s', Type::class, $type)),
                (string) $field->get('Key'),
                $field->get('Default'),
                (string) $field->get('Extra'),
                $length,
            );
        }

        return $fields;
    }
}