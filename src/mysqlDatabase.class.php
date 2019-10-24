<?php
/**
 * MDO
 *
 * @author Benjamin Wollenweber
 * @package MDO
 * @copyright 2013
 */
/**
 * MySQL Datenbank
 */
class mysqlDatabase
{
	/** @var string MySQL Host */
	public $host;
	/** @var string MySQL User */
	public $user;
	/** @var string MySQL Passwort */
	public $pass;
	/** @var mysqli */
	public $Mysqli;
	/** @var string SQL Abfrage */
	public $sql;
	/** @var mysqli_result SQL Result */
	public $Result;

    /**
     * Konstruktor
     *
     * Setzt Datenbank Verbindungsdaten.
     *
     * @param string $host Host
     * @param string $user Benutzer
     * @param string $pass Passwort
     */
    public function __construct($host, $user, $pass)
	{
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->Mysqli = NULL;
	}

    /**
     * Öffnet Datenbank
     *
     * Öffnet die Datenbankverbindung.
     *
     * @param string|null $database Datenbankname
     * @return bool
     */
    public function openDB($database = null)
	{
		if ($this->Mysqli = new mysqli($this->host, $this->user, $this->pass)) {
            $this->Mysqli->query("SET NAMES 'utf8';");
            
            if (
                mb_strlen($database) &&
                !$this->useDatabase($database)
            ) {
                return false;
            }
            
			return true;
		}

		return false;
	}

    /**
     * Selektiert Datenbank
     *
     * Selektiert eine Datenbank.
     *
     * @param string $database Datenbankname
     * @return bool Im Erfolgsfall true
     */
    public function useDatabase($database)
    {
        return $this->Mysqli->select_db($database);
    }

    /**
     * Schließt Datenbank
     *
     * Schließt die Datenbankverbindung.
     *
     * @return bool Im Erfolgsfall true
     */
    public function closeDB()
	{
		return $this->Mysqli->close();
	}

    /**
     * Gibt MySQL Fehlermeldung zurück
     *
     * Gibt die MySQL Fehlermeldung zurück.
     *
     * @return string MySQL Fehlermeldung
     */
    public function error()
	{
		return $this->Mysqli->error;
	}

    /**
     * Sendet MySQL Query
     *
     * Sendet ein MySQL Query.
     *
     * @param string $query MySQL Query
     * @return bool Im Erfolgsfall true
     */
    public function sendQuery($query)
	{
		$this->sql = $query;

        $this->Result = $this->Mysqli->query($this->sql);
        
		if ($this->Result === false) {
			return false;
		}

        return true;
	}

    /**
     * Gibt Datensatz zurück
     *
     * Gibt den aktuellen Datensatz als Indexiertes und Associatives Array zurück.
     *
     * @return array Datensatz
     */
    public function fetchArray()
	{
        return $this->Result->fetch_array();
	}

    /**
     * Gibt Datensatz zurück
     *
     * Gibt den aktuellen Datensatz als Indexiertes Array zurück.
     *
     * @return array Datensatz
     */
    public function fetchRow()
	{
        return $this->Result->fetch_row();
	}

    /**
     * Gibt Datensatz zurück
     *
     * Gibt den aktuellen Datensatz als Associatives Array zurück.
     *
     * @return array Datensatz
     */
    public function fetchAssoc()
	{
        return $this->Result->fetch_assoc();
	}

    /**
     * Gibt Datensatz zurück
     *
     * Gibt den aktuellen Datensatz als Objekt zurück.
     *
     * @return object|stdClass Datensatz
     */
    public function fetchObject()
	{
        return $this->Result->fetch_object();
	}

    /**
     * Gibt Feld zurück
     *
     * Gibt ein Feld des aktuellen Datensatz zurück.<br>
     * Das Feld ist als nummerischer Index zu übergeben.
     *
     * @param int $field Arrayindex
     * @return string|bool Im Fehlerfall false
     */
    public function fetchResult($field = 0)
	{
		if ($row = $this->fetchRow()) {
			return $row[$field];
        }

		return false;
	}

    /**
     * Gibt Datensätze zurück
     *
     * Gibt alle Datensätze als Indexiertes und Associatives Array zurück.
     *
     * @return array Datensätze
     */
    public function fetchArrayList()
	{
		$rows = array();
        
        while ($row = $this->fetchArray()) {
			$rows[] = $row;
        }

		return $rows;
	}

    /**
     * Gibt Datensätze zurück
     *
     * Gibt alle Datensätze als Indexiertes Array zurück.
     *
     * @return array Datensätze
     */
    public function fetchRowList()
	{
		$rows = array();
        
        while ($row = $this->fetchRow()) {
			$rows[] = $row;
        }

		return $rows;
	}

    /**
     * Gibt Datensätze zurück
     *
     * Gibt alle Datensätze als Associatives Array zurück.
     *
     * @return array Datensätze
     */
    public function fetchAssocList()
	{
		$rows = array();
        
        while ($row = $this->fetchAssoc()) {
			$rows[] = $row;
        }

		return $rows;
	}

    /**
     * Gibt Datensätze zurück
     *
     * Gibt alle Datensätze als Objekte zurück.
     *
     * @return array Datensätze
     */
    public function fetchObjectList()
	{
		$rows = array();
        
        while ($row = $this->fetchObject()) {
			$rows[] = $row;
        }

		return $rows;
	}

    /**
     * Gibt Felder zurück
     *
     * Gibt ein Feld aller Datensäte zurück.
     * Das Feld ist als nummerischer Index zu übergeben.
     *
     * @param int $field Arrayindex
     * @return array Felder
     */
    public function fetchResultList($field = 0)
	{
		$rows = array();
        
        while ($row = $this->fetchResult($field)) {
			$rows[] = $row;
        }
        
		return $rows;
	}

    /**
     * Maskiert und quotet
     *
     * Maskiert und qoutet einen Wert.
     *
     * @param mixed $value Wert
     * @return string
     */
    public function escape($value, $withQuotes = true)
    {
        $value = $this->Mysqli->real_escape_string($value);

        if (!$withQuotes) {
            return $value;
        }

        return "'" . $value . "'";
    }

    /**
     * Maskiert und quotet
     *
     * Maskiert und qoutet ein Array mit Werten.
     *
     * @param array $pieces Werte
     * @param string $glue Trennzeichen
     * @return string
     */
    public function implode($pieces, $glue = ',')
    {
        $data = "";
        
        foreach ($pieces as $piece) {
            $data .= $this->escape($piece) . $glue;
        }
        
        return mb_substr($data, 0, 0-mb_strlen($glue));
    }

    /**
     * Gibt MySQL RexEx String zurück
     *
     * Gibt einen MySQL RexEx String zurück.
     *
     * @param string $search Suchstring
     * @return string MySQL RexEx String
     */
    public function getRegexString($search)
    {
        $search = str_replace('.', '\.', $search);
        $search = str_replace('?', '.', $search);
        $search = str_replace('*', '.*', $search);

        return "'[[:<:]]" . $this->Mysqli->real_escape_string($search) . "[[:>:]]'";
    }

    public function startTransaction()
    {
        $this->sendQuery('START TRANSACTION');
    }

    public function commit()
    {
        $this->sendQuery('COMMIT');
    }

    public function rollback()
    {
        $this->sendQuery('ROLLBACK');
    }
}
?>