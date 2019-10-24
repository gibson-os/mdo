<?php
/**
 * MDO
 *
 * @author Benjamin Wollenweber
 * @package MDO
 * @copyright 2013
 */
/**
 * MySQL Tabelle
 */
class mysqlTable
{
	/** @var mysqlDatabase Datenbankverbindung */
	var $connection;
	/** @var array Tabellenfelder */
	var $fields = array();
    /** @var string Selektion */
    var $selectString = '*';
    /** @var string Tabellen Joins */
    var $joins = '';
    /** @var array Select Unions */
    var $unions = array();
    /** @var string Union Funktion */
    var $unionFunc = 'ALL';
	/** @var string SQL Abfrage */
	var $sql;
	/** @var string SQL Where Klausel */
	var $where;
	/** @var string SQL Order By Klausel */
	var $orderBy;
	/** @var int SQL Limit Klausel */
	var $limit;
	/** @var string SQL Select Funktion */
	var $selectFunc;
	/** @var string SQL Group By Klausel */
	var $groupBy;
	/** @var string SQL Having Klausel */
	var $having;
    /** @var string Datenbankname */
	var $database;
    /** @var string Tabellenname */
    var $table;

	/** @var array Datensätze */
	var $records = array();
	/** @var int Anzahl an Datensätzen */
	var $countRecords = 0;
	/** @var int Ausgewählte Datensätze */
	var $selectedRecord = 0;

    /**
     * Konstruktor
     *
     * @param mysqlDatabase $connection Datenbankverbindung
     * @param string $table Tabellenname
     * @param string $database Datenbankname
     */
    public function __construct($connection, $table, $database)
	{
		$this->database = $database;
		$this->table = $table;
		$this->setConnection($connection);
		
        $registry = mysqlRegistry::getInstance();
        
        if ($registry->exists('mdo_' . $table)) {
            foreach ($registry->get('mdo_' . $table) as $field) {
                $this->fields[] = $field[0];
                $this->{$field[0]} = new mysqlField($field, $this->connection);
            }
        } else {
            $this->connection->sendQuery("SHOW FIELDS FROM `" . $database . "`.`" . $table . "`;");
		    $fields = array();
            
		    while ($field = $this->connection->fetchRow()) {
			    if (preg_match('/\(\d*\)/', $field[1], $length, PREG_OFFSET_CAPTURE)) {
				    $field[] = substr($length[0][0], 1, strlen($length[0][0]) - 2);
				    $field[1] = preg_replace('/\(\d*\)/', '', $field[1]);
			    }
			    
			    $this->fields[] = $field[0];
			    $this->{$field[0]} = new mysqlField($field, $this->connection);
                $fields[] = $field;
		    }
            
            $registry->set('mdo_' . $table, $fields);
        }
        
        $this->selectString = $this->_quoteSelectArray($this->fields, $this->table);
	}

	/**
	* Setzt die Datenbankverbindung für diese Tabelle
	*
	* @param mysqlDatabase $connection Datenbankverbindung
	*/
	public function setConnection($connection)
	{
		$this->connection = $connection;
	}

    /**
     * Reset
     *
     * Resetet das Select Query
     */
    public function reset()
    {
        $this->load();
        $this->setWhere();
        $this->setGroupBy();
        $this->setOrderBy();
        $this->setLimit();
        $this->clearJoin();
    }

	/**
	* Lädt ein Datensatz in das Objekt.
     *
	* Der Datensatz kann dabei ein indiziertes Array, ein Assoziatives Array oder ein Objekt dieser Klasse sein.
	*
	* @param mixed $record Datensatz
	* @return boolean Wenn erfolgreich true
	*/
	public function load($record = null)
	{
		if (is_object($record)) {
			foreach ($this->fields as $field) {
				if (isset($record->{$field})) {
                    $this->{$field}->setValue($record->{$field});
                }
            }
		} else if (is_array($record)) {
			if (key($record)) {
				foreach ($this->fields as $field) {
					if (
                        isset($record[$field]) ||
                        is_null($record[$field])
                    ) {
                        $this->{$field}->setValue($record[$field]);
                    }
                }
            } else {
				foreach ($this->fields as $index => $field) {
					if (
                        isset($record[$index]) ||
                        is_null($record[$field])
                    ) {
                        $this->{$field}->setValue($record[$index]);
                    }
                }
            }
		} else {
			foreach ($this->fields as $field) {
				$this->{$field}->setDefaultValue();
            }

			return false;
		}

		return true;
	}

    /**
     * Erweitert die Auswahl
     *
     * Erweitert die Auswahl um weitere Felder.
     *
     * @param string|array $select Auswahl
     * @param string|null $table Tabellenname
     */
    public function appendSelectString($select, $table = null)
    {
        if (is_array($select)) {
            $this->selectString .= ',' . $this->_quoteSelectArray($select, $table);
        } else {
            $this->selectString .= ',' . $select;
        }
    }

    /**
     * Setzt die Auswahl
     *
     * Setzt die Auswahl des SQL Query.
     *
     * @param string|array|null $select Auswahl
     * @param string|null $table Tabellenname
     */
    public function setSelectString($select = null, $table = null)
    {
        if ($select) {
            if (is_array($select)) {
                $this->selectString = $this->_quoteSelectArray($select, $table);
            } else {
                $this->selectString = $select;
            }
        } else {
            $this->selectString = $this->_quoteSelectArray($this->fields, $this->table);
        }
    }

    /**
     * Quotet Auswahl
     *
     * Quotet die Auswahl.
     *
     * @param array $select Auswahl
     * @param string|null $table Tabellenname
     * @return string
     */
    private function _quoteSelectArray($select, $table = null)
    {
        if ($table) {
            return "`" . $table . "`.`" . implode("`, `" . $table . "`.`", $select) . "`";
        } else {
            return "`" . implode("`, `", $select) . "`";
        }
    }

    /**
     * @param string $set
     * @return bool
     */
    public function update($set)
    {
        $this->sql = 'UPDATE `' . $this->database . '`.`' . $this->table . '` SET ' . $set . ' ' . $this->where;

        return $this->connection->sendQuery($this->sql);
    }

    /**
     * Gibt Select Query zurück
     *
     * Gibt MySQL Select Query zurück.
     *
     * @param string|null $select Auswahl
     * @param bool $union Select Union
     * @return string
     */
    public function getSelect($select = null, $union = false)
    {
        if (!$select) {
            $select = $this->selectString;
        }
        
        if (
            $union &&
            count($this->unions) > 1
        ) {
            return '(' . trim(implode(') UNION ' . $this->unionFunc . ' (', $this->unions)) . ') ' . $this->orderBy . $this->limit . ';';
        } else {
            return trim("SELECT " . $this->selectFunc . $select . " FROM `" . $this->database . "`.`" . $this->table . "`" . $this->joins . " " . $this->where . $this->groupBy . $this->having . $this->orderBy . $this->limit) . ";";
        }
    }

    /**
     * Führt SQL Query aus
     *
     * Führt ein SQL Query aus mit dem Ziel Datensätze zu erhalten.
     *
     * @param bool $loadRecord Wenn true werden die Datensätze in die Eigenschaft records geladen
     * @param null $select Gibt an welche Felder oder MySQL Funktion selektiert werden sollen
     * @param bool $union Select Union
     * @return bool|int Anzahl der Datensätze. Im Fehlerfall false
     */
    public function select($loadRecord = true, $select = null, $union = false)
	{
        $this->sql = $this->getSelect($select, $union);
        
		if ($this->connection->sendQuery($this->sql)) {
			if ($loadRecord) {
				$this->records = $this->connection->fetchAssocList();

				if ($this->first()) {
					$this->countRecords = count($this->records);
				} else {
					$this->countRecords = 0;
                }
			} else {
                return true;
            }

			return $this->countRecords;
		} else {
			unset($this->records);
			$this->countRecords = 0;

			return false;
		}
	}

    /**
     * Führt SQL Query aus
     *
     * Führt ein SQL Query mit Union aus mit dem Ziel Datensätze zu erhalten.
     *
     * @param bool $loadRecord Wenn true werden die Datensätze in die Eigenschaft records geladen
     * @return bool|int
     */
    public function selectUnion($loadRecord = true)
    {
	    return $this->select($loadRecord, null, true);
	}

    /**
     * Führt SQL Query aus
     *
     * Führt ein SQL Query aus mit dem Ziel das Ergebniss einer oder mehrerer Aggregatfunktionen zu erhalten.
     *
     * @param string $function Aggregatfunktionen
     * @return array|bool Mit der Rückgabe der Aggregatfunktionen
     */
    public function selectAggregate($function)
	{
		if (!$this->select(false, $function)) {
            return false;
        }

		return $this->connection->fetchRow();
	}

    /**
     * Gibt Speicher Query zurück
     *
     * Gibt MySQL Speicher Query zurück.
     *
     * @return string
     */
    public function getSave()
    {
        $sql = 'INSERT INTO `' . $this->database . '`.`' . $this->table . '` SET ';
        $fieldString = null;

        foreach ($this->fields as $field) {
            /** @var mysqlField $fieldObject */
            $fieldObject = $this->{$field};

            if (
                $fieldObject->isAutoIncrement() &&
                empty($fieldObject->getValue())
            ) {
                continue;
            }

            $fieldString .= '`' . $field . '`=' . $fieldObject->getSQLValue() . ', ';
        }

        $fieldString = mb_substr($fieldString, 0, -2);
        
        return $sql . $fieldString . ' ON DUPLICATE KEY UPDATE ' . $fieldString;
    }

    /**
     * Führt SQL Query aus
     *
     * Führt ein Query aus mit dem Ziel einen Datensatz zu speichern (SQL REPLACE).
     *
     * @return bool Wenn erfolgreich true
     * @throws Exception
     */
    public function save()
	{
        $this->sql = $this->getSave();

        if (!$this->connection->sendQuery($this->sql)) {
            throw new Exception('Error: ' . $this->connection->error() . PHP_EOL . 'Query: ' . $this->sql);
        }

        return true;
	}

    /**
     * Lädt Datensatz
     *
     * Lädt den zuletzt gespeicherten Datensatz.
     *
     * @return bool Wenn erfolgreich true
     */
    public function getReplacedRecord()
	{
        $where = null;
        
		foreach ($this->fields as $field) {
			if ($this->{$field}->getValue()) {
                $where .= "`" . $field . "`=" . $this->{$field}->getSQLValue() . " && ";
            }
		}
        
		$where = substr($where, 0, strlen($where) - 4);

		$this->setWhere($where);
		$this->select();
		$this->setWhere();
		
		if ($this->countRecords() == 1) {
			return true;
        } else {
			return false;
        }
	}

    /**
     * Gibt Query zurück
     *
     * Gibt Delete Query zurück.
     *
     * @return string Delete Query
     */
    public function getDelete()
    {
        if (strlen($this->where)) {
            $sql = "DELETE FROM `" . $this->database . "`.`" . $this->table . "` " . $this->where . ";";
        } else {
            $sql = "DELETE FROM `" . $this->database . "`.`" . $this->table . "` WHERE ";
            
            foreach ($this->fields as $field) {
                if (is_null($this->{$field}->getValue())) {
                    $sql .= "`" . $field . "` IS NULL AND ";
                } else {
                    $sql .= "`" . $field . "`=" . $this->{$field}->getSQLValue() . " AND ";
                }
            }
            
            $sql = substr($sql, 0, strlen($sql) - 5) . ";";
            // Datensatz aus Array löschen!
        }
        
        return $sql;
    }

    /**
     * Führt SQL Query aus
     *
     * Führt ein Query aus mit dem Ziel Datensätze zu löschen.
     *
     * @return bool
     */
    public function delete()
	{
        $this->sql = $this->getDelete();
        return $this->connection->sendQuery($this->sql);
	}

    /**
     * Lädt Datensatz
     *
     * Lädt den ersten selektierten Datensatz.
     *
     * @return bool Wenn erfolgreich true
     */
    public function first()
	{
		if (isset($this->records[0]) && $this->load($this->records[0])) {
            $this->selectedRecord = 0;
			return true;
        }
            
		return false;
	}

    /**
     * Lädt Datensatz
     *
     * Lädt den letzten selektierten Datensatz.
     *
     * @return bool Wenn erfolgreich true
     */
    public function last()
	{
		if (
            isset($this->records[$this->countRecords - 1]) &&
            $this->load($this->records[$this->countRecords - 1])
        ) {
            $this->selectedRecord = $this->countRecords - 1;
			return true;
        }
            
		return false;
	}

    /**
     * Lädt Datensatz
     *
     * Lädt den nächsten selektierten Datensatz.
     *
     * @return bool Wenn erfolgreich true
     */
	public function next()
	{
		if ($this->selectedRecord < $this->countRecords) {
			if(
                isset($this->records[$this->selectedRecord + 1]) &&
                $this->load($this->records[$this->selectedRecord + 1])
            ) {
                $this->selectedRecord++;
				return true;
            }
		}
        
		return false;
	}

    /**
     * Lädt Datensatz
     *
     * Lädt den voherigen selektierten Datensatz.
     *
     * @return bool Wenn erfolgreich true
     */
	public function previous()
	{
		if ($this->selectedRecord != 0) {
			if (
                isset($this->records[$this->selectedRecord - 1]) &&
                $this->load($this->records[$this->selectedRecord - 1])
            ) {
                $this->selectedRecord--;
				return true;
            }
		}
        
		return false;
	}

    /**
     * Erweitert das Select Query
     *
     * Erweitert das Select Query um eine weitere Tabelle.
     *
     * @param string $table Tabelle die gejoint wird
     * @param string $on Bedingung des joins
     */
    public function appendJoin($table, $on)
    {
        $this->joins .= " JOIN " . $table . " ON " . $on;
    }

    /**
     * Entfernt Joins
     *
     * Entfernt alle Joins.
     */
    public function clearJoin()
    {
        $this->joins = '';
    }

    /**
     * Erweitert das Select Query
     *
     * Erweitert das Select Query um eine weitere Tabelle.
     *
     * @param string $table Tabelle die gejoint wird
     * @param string $on Bedingung des joins
     */
    public function appendJoinLeft($table, $on)
    {
        $this->joins .= " LEFT JOIN " . $table . " ON " . $on;
    }

    /**
     * Erweitert das Select Query
     *
     * Erweitert Query um ein oder mehrere Selects.
     *
     * @param string|null $query Select Query
     * @param string|null $select Auswahl
     */
    public function appendUnion($query = null, $select = null)
    {
        if ($query) {
            $query = preg_replace('/;/', '', $query);
        } else {
            $query = mb_substr($this->getSelect($select), 0, -1);
        }
        
        $this->unions[] = $query;
    }

    /**
     * Setzt SQL Select Funktion
     *
     * Setzt eine SQL Select Funktion die beim select mit ausgeführt wird.
     *
     * @param string|null $function Funktion
     */
    public function setSelectFunc($function = null)
	{
		if ($function) {
			$this->selectFunc = $function . " ";
		} else {
			$this->selectFunc = "";
        }
	}

    /**
     * Setzt SQL Where
     *
     * Setzt die SQL Where Klausel.
     *
     * @param string|null $where SQL Where Klausel
     */
    public function setWhere($where = null)
	{
		if ($where) {
			$this->where = "WHERE " . $where . " ";
        } else {
			$this->where = "";
        }
	}

    /**
     * Setzt SQL Group By
     *
     * Setzt eine SQL Group By Klausel.
     *
     * @param string|bool $groupBy SQL Group By Klausel
     * @param string|bool $having Having Klausel
     */
    public function setGroupBy($groupBy = false, $having = false)
	{
		if ($groupBy) {
			$this->groupBy = "GROUP BY " . $groupBy . " ";

			if ($having) {
				$this->having = "HAVING " . $having . " ";
            } else {
				$this->having = "";
            }
		} else {
			$this->groupBy = "";
			$this->having = "";
		}
	}

    /**
     * Setzt SQL Order By
     *
     * Setzt eine SQL Order By Klausel.
     *
     * @param string|bool $orderBy SQL Order By Klausel
     */
    public function setOrderBy($orderBy = false)
	{
		if ($orderBy) {
			$this->orderBy = "ORDER BY " . $orderBy . " ";
        } else {
			$this->orderBy = "";
        }
	}

    /**
     * Setzt SQL Limit
     *
     * Setzt eine SQL Limit Klausel.
     *
     * @param int|bool $rows Anzahl an Datensätzen
     * @param int|bool $from Angefangen von Datensatz
     */
    public function setLimit($rows = false, $from = false)
	{
		if ($from) {
			$this->limit = "LIMIT " . $from . ", " . $rows;
        } else if ($rows) {
			$this->limit = "LIMIT " . $rows;
        } else {
			$this->limit = "";
        }
	}

    /**
     * Gibt Datensätze zurück.
     *
     * Gibt ein Array mit allen selektierten Datensätzen zurück.
     *
     * @return array Datensätze
     */
    public function getRecords()
	{
		return $this->records;
	}

    /**
     * Gibt aktuellen Datensatz zurück.
     *
     * Gibt ein Array mit dem aktuellen Datensatz zurück.
     *
     * @return array Datensatz
     */
    public function getSelectedRecord()
    {
        return $this->records[$this->selectedRecord];
    }

    /**
     * Gibt Anzahl an Datensätzen zurück.
     *
     * Gibt die Anzahl der selektierten Datensätze zurück.
     *
     * @return int Anzahl an Datensätzen
     */
    public function countRecords()
	{
		return $this->countRecords;
	}

    /**
     * Gibt Datenbankname zurück
     *
     * Gibt den Datenbanknamen zurück.
     *
     * @return string Datenbankname
     */
    public function getDBName()
	{
		return $this->database;
	}

    /**
     * Gibt Tabellennamen zurück
     *
     * Gibt den Tabellennamen zurück.
     *
     * @return string Tabellenname
     */
    public function getTableName()
	{
		return $this->table;
	}
}
?>
