<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Database;
use CPath\Interfaces\IDatabase;
use \PDO;
class PDOUpdate {
    private $DB, $stmt=NULL, $table, $fields=array(), $where=array(), $limit='1';
    public function __construct($table, \PDO $DB, Array $fields) {
        $this->DB = $DB;
        $this->table = $table;
        $this->fields = $fields;
    }

    public function addField($field) {
        $this->fields[] = $field;
        $this->stmt = NULL;
        return $this;
    }

    public function addWhere($field) {
        if(strpos($field, '?') === false)
            $field .= '=?';
        $this->where[] = $field;
        $this->stmt = NULL;
        return $this;
    }

    public function where($field, $value=NULL) {
        if(!is_int($value))
            $value = $this->DB->quote($value);
        if(strpos($field, '?') === false)
            $field .= '=' . $value;
        else
            $field = str_replace('?', $value, $field);
        $this->where[] = $field;
        $this->stmt = NULL;
        return $this;
    }

//    public function limit($limit) {
//        $this->limit = $limit;
//        $this->stmt = NULL;
//        return $this;
//    }

    public function values($_values) {
        if(!is_array($_values)) $_values = func_get_args();
        if(!$this->stmt) $this->stmt = $this->DB->prepare($this->getSQL());
        $this->stmt->execute($_values);
        return $this;
    }

    public function getLastAffectedRows() { return $this->stmt->rowCount(); }

    public function getSQL() {
        if(!$this->where)
            throw new \Exception("method addWhere() was not called");
        return "UPDATE ".$this->table
            ."\nSET ".implode('=?, ',$this->fields).'=?'
            ."\nWHERE ".($this->where ? implode(' AND ', $this->where) : '1');
    }
}