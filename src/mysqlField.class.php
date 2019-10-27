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
    /**
     * @var mysqlDatabase
     */
    public $connection;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $null;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $default;

    /**
     * @var string
     */
    public $extra;

    /**
     * @var string|int
     */
    public $value;

    /**
     * @var float
     */
    public $length;

    /**
     * @var string
     */
    public $valueType;

    /**
     * mysqlField constructor.
     *
     * @param array         $field
     * @param mysqlDatabase $connection
     */
    public function __construct(array $field, mysqlDatabase $connection)
    {
        $this->connection = $connection;
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
            !strlen($this->default) &&
            $this->null == 'YES'
        ) {
            $this->setValue('NULL', 'FUNC');
        } elseif (strlen($this->default)) {
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

    /**
     * @param string $value
     * @param string $type
     *
     * @return bool
     */
    public function setValue(string $value, string $type = ''): bool
    {
        $value = trim(stripslashes($value));

        if (strlen($value)) {
            if (
                $type == 'FUNC' ||
                (((preg_match('/int/i', $this->type) && preg_match('/^-?\d+$/', $value)) ||                          // Ganzzahlenfeld
                (preg_match('/(float|double|decimal)/i', $this->type) && preg_match('/^-?\d+\.?\d*$/', $value)) ||  // Gleitzahlenfeld
                (preg_match('/enum/i', $this->type) && preg_match('/' . $value . '/', $this->type)) ||                    // Auswahl
                 preg_match('/(char|text|blob|time|date|year)/i', $this->type)) &&                               // Alles andere
                (strlen($value) <= $this->length || $this->length == 0))                      // Länge des Feldes
            ) {
                $this->value = $value;
                $this->valueType = $type;
            } else {
                return false;
            }
        } else {
            $this->setDefaultValue();
        }

        return true;
    }

    /**
     * @return string
     */
    public function getSQLValue(): string
    {
        if ($this->valueType == 'FUNC') {
            return (string) $this->value;
        }

        return $this->connection->escape($this->value);
    }

    /**
     * @return string|int|null
     */
    public function getValue()
    {
        if ($this->value === 'NULL') {
            return null;
        }

        return $this->value;
    }

    /**
     * @return string
     */
    public function getValueType(): string
    {
        return $this->valueType;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDefaultValue(): string
    {
        return $this->default;
    }

    /**
     * @return float
     */
    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->extra === 'auto_increment';
    }
}
