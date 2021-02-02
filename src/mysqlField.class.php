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

    /**
     * @param mixed $value
     */
    public function setValue($value, string $type = ''): bool
    {
        $value = (string) $value;

        if (strlen($value)) {
            if (
                $type == 'FUNC' ||
                (((preg_match('/int/i', $this->type) && preg_match('/^-?\d+$/', $value)) ||                          // Ganzzahlenfeld
                (preg_match('/(float|double|decimal)/i', $this->type) && preg_match('/^-?\d+\.?\d*$/', $value)) ||  // Gleitzahlenfeld
                (preg_match('/enum/i', $this->type) && preg_match('/' . $value . '/', $this->type)) ||                    // Auswahl
                 preg_match('/(char|text|blob|time|date|year|binary)/i', $this->type)) &&                               // Alles andere
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

    public function getSQLValue(): string
    {
        if ($this->valueType == 'FUNC') {
            return (string) $this->value;
        }

        return $this->connection->escape((string) $this->value);
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

    public function getValueType(): string
    {
        return $this->valueType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDefaultValue(): string
    {
        return $this->default;
    }

    public function getLength(): float
    {
        return $this->length;
    }

    public function isAutoIncrement(): bool
    {
        return $this->extra === 'auto_increment';
    }
}
