<?php
/**
 * MDO
 *
 * @author Benjamin Wollenweber
 * @package MDO
 * @copyright 2013
 */
/**
 * MySQL Tabellenfeld
 */
class mysqlField
{
	/** @var mysqlDatabase Verbindung */
	var $connection;
	/** @var string Feldname */
	var $name;
	/** @var string Feldtyp */
	var $type;
	/** @var string Null erlaubt */
	var $null;
	/** @var string Schlüssel */
	var $key;
	/** @var string Standardwert */
	var $default;
	/** @var string Extra */
	var $extra;
	/** @var string Feldwert */
	var $value;
	/** @var float Feldlänge */
	var $length;
	/** @var string Typ des Feldwerts */
	var $valueType;

	/**
	* Nimmt ein Array mit allen relevaten Feldwerten entgegen und setzt die Eigenschaften.
	* 
	* @param array $field Feldeigenschaften
    * @param mysqlDatabase $connection
	*/
	public function __construct($field, $connection)
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

	/**
	* Setzt den Standardwert des Feldes.
	*/
	public function setDefaultValue()
	{
		if ($this->default == 'CURRENT_TIMESTAMP') {
			$this->setValue($this->default, 'FUNC');
        } else if (
            !strlen($this->default) &&
            $this->null == 'YES'
        ) {
            $this->setValue('NULL', 'FUNC');
        } else if (strlen($this->default)) {
		    if ($this->default === 'current_timestamp()') {
                $this->setValue('current_timestamp()', 'FUNC');
                return;
            }

			$this->setValue($this->default);
        } else if (mb_stripos($this->getType(), 'int')) {
			$this->value = 0;
        } else {
            $this->value = '';
        }
	}

	/**
	* Überprüft ob der zu setztende Wert im Datenbankfeld eingetragen werden kann.
	*
	* @param mixed $value Wert
	* @param string $type Typ des Wertes. Für MySQL Funktionen ist 'FUNC' zu übergeben
	* @return bool Erfolg
	*/
	public function setValue($value, $type = '')
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
	* Gibt den Wert so zurück das er für ein MySQL Query verwendet werden kann
	*
	* @param bool Geben sie an ob die Werte durch Magic Quotes escaped wurden
	* @return string Für MySQL escapedes Attribut
	*/
	public function getSQLValue()
	{
		if ($this->valueType == 'FUNC') {
			return $this->value;
        } else {
			return $this->connection->escape($this->value);
        }
	}

	/**
	* Gibt den Wert zurück
	*
	* @return string Wert
	*/
	public function getValue()
	{
		if ($this->value == 'NULL') {
            return null;
        }
        
        return $this->value;
	}
	
	/**
	* Gibt den Typ des Wertes zurück.
	* Also ob es sich um 'FUNC' handelt oder nicht.
	*
	* @return string Typ des Wertes
	*/
	public function getValueType()
	{
		return $this->valueType;
	}
	
	/**
	* Gibt des Feldtype zurück
	*
	* @return string Feldtyp
	*/
	public function getType()
	{
		return $this->type;
	}
	
	/**
	* Gibt den Standardwert zurück
	*
	* @return mixed Standardwert
	*/
	public function getDefaultValue()
	{
		return $this->default;
	}
	
	/**
	* Gibt die Länge des Feldes zurück
	*
	* @return int Feldlänge
	*/
	public function getLength()
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
?>
