<?php
declare(strict_types=1);
/**
 * MDO.
 *
 * @author Benjamin Wollenweber
 *
 * @package MDO
 *
 * @copyright 2013
 */
/**
 * MySQL Tabellenfeld.
 */
class mysqlField
{
    public string $name;

    public string $type;

    public string $null;

    public string $key;

    public string|int|float|null $default;

    public string $extra;

    public string|int|float|null $value = null;

    public float|int|string $length = 0;

    public string $valueType = '';

    public function __construct(array $field, public mysqlDatabase $connection)
    {
        $this->name = $field[0];
        $this->type = $field[1];
        $this->null = $field[2];
        $this->key = $field[3];
        $this->default = $field[4];
        $this->extra = $field[5];

        if (isset($field[6])) {
            $this->length = $field[6];
        }

        $this->setDefaultValue();
    }

    public function setDefaultValue(): void
    {
        if ($this->default == 'CURRENT_TIMESTAMP') {
            $this->setValue($this->default, 'FUNC');
        } elseif (
            $this->default === null &&
            $this->null == 'YES'
        ) {
            $this->setValue('NULL', 'FUNC');
        } elseif (!empty($this->default)) {
            if ($this->default === 'current_timestamp()') {
                $this->setValue('current_timestamp()', 'FUNC');

                return;
            }

            $this->setValue($this->default);
        } elseif (mb_stripos($this->getType(), 'int')) {
            $this->value = 0;
        } else {
            $this->value = '';
        }
    }

    public function setValue(string|int|float|null $value, string $type = ''): bool
    {
        if ($value === null) {
            $this->setDefaultValue();

            return true;
        }

        $value = (string) $value;

        if (
            $type == 'FUNC' ||
            (((preg_match('/int/i', $this->type) && preg_match('/^-?\d+$/', $value)) ||                          // Ganzzahlenfeld
            (preg_match('/(float|double|decimal)/i', $this->type) && preg_match('/^-?\d+\.?\d*$/', $value)) ||  // Gleitzahlenfeld
            (preg_match('/enum/i', $this->type) && preg_match('/' . $value . '/', $this->type)) ||                    // Auswahl
             preg_match('/(char|text|blob|time|date|year|binary)/i', $this->type)) &&                               // Alles andere
            (strlen($value) <= $this->length || $this->length === 0))                      // Länge des Feldes
        ) {
            $this->value = $value;
            $this->valueType = $type;
        } else {
            return false;
        }

        return true;
    }

    /**
     * @deprecated
     */
    public function getSQLValue(): string
    {
        if ($this->valueType == 'FUNC') {
            return (string) $this->value;
        }

        return $this->connection->escape((string) $this->value);
    }

    public function getValue(): string|int|float|null
    {
        if ($this->value === 'NULL') {
            return null;
        }

        return $this->value;
    }

    public function getValueType(): string
    {
        return $this->valueType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDefaultValue(): string|int|float|null
    {
        return $this->default;
    }

    public function getLength(): float|int|string
    {
        return $this->length;
    }

    public function isAutoIncrement(): bool
    {
        return $this->extra === 'auto_increment';
    }
}
