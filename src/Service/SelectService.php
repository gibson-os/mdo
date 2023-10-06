<?php
declare(strict_types=1);

namespace MDO\Service;

use MDO\Dto\Select;

class SelectService
{
    /**
     * @param Select[] $selects
     *
     * @return array<string, string>
     */
    public function getSelects(array $selects): array
    {
        $selectFields = [];

        foreach ($selects as $select) {
            foreach (array_keys($select->getTable()->getFields()) as $fieldName) {
                $selectFields[$select->getPrefix() . $fieldName] = sprintf(
                    '`%s`.`%s`',
                    $select->getAlias(),
                    $fieldName,
                );
            }
        }

        return $selectFields;
    }

    public function getUnescapedRegexString(string $search): string
    {
        $search = str_replace('.', '\.', $search);
        $search = str_replace('?', '.', $search);
        $search = str_replace('*', '.*', $search);

        return '[[:<:]]' . $search . '[[:>:]]';
    }

    public function getParametersString(array $parameters, string $separator = ', ', string $value = '?'): string
    {
        if ($value === '?') {
            return implode($separator, array_fill(0, count($parameters), $value));
        }

        $namedParameters = [];
        $i = 0;

        foreach ($parameters as $parameter) {
            $namedParameters[] = ':' . $value . $i++;
        }

        return implode($separator, $namedParameters);
    }
}
