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
     *
     * @return array<string, Field>
     */
    public function loadFields(string $tableName): array
    {
        $result = $this->client->execute(sprintf('SHOW FIELDS FROM `%s`', $tableName));
        $fields = [];

        foreach ($result->iterateRecords() as $field) {
            $type = $field->get('Type')?->getValue();
            $length = 0;

            if (preg_match('/\(\d*\)/', (string) $type, $fieldLength, PREG_OFFSET_CAPTURE)) {
                $length = substr($fieldLength[0][0], 1, strlen($fieldLength[0][0]) - 2);
                $type = preg_replace('/\(\d*\)/', '', (string) $type);
            }

            $fieldName = (string) $field->get('Field')?->getValue();
            $fields[$fieldName] = new Field(
                $fieldName,
                mb_strtolower((string) $field->get('Null')?->getValue()) === 'yes',
                constant(sprintf('%s::%s', Type::class, $type)),
                (string) $field->get('Key')?->getValue(),
                $field->get('Default')?->getValue(),
                (string) $field->get('Extra')?->getValue(),
                $length,
            );
        }

        return $fields;
    }
}
