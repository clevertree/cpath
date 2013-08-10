<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Interfaces\IDatabase;
use \PDO;
abstract class PDOWhere {
    const DefaultLogicOR = 0x1; // Default logic between WHERE elements to "OR" instead of "AND"

    protected $table, $where=array(), $values=array();
    private $lastCond = true;
    private $alias = NULL;
    private $joins = array();
    protected $mFlags = 0;

    public function __construct($table) {
        $this->table = $table;
    }

    /**
     * @param String $table the table to join
     * @param String $sourceField The source field to join on. If $destField is omited, $sourceField represents the entire " ON ..." segment of the join.
     * @param String|null $destField The destination field to join on.
     * @param String|null $alias The alias for the table
     * @return $this
     */
    public function leftJoin($table, $sourceField, $destField=NULL, $alias=NULL) {
        if($destField != NULL) {
            if($alias) $table .= ' '.$alias;
            else $alias = $table;
            if(strpos($sourceField, '.') === false) {
                $sAlias = $this->alias ?: $this->table;
                $sourceField = "{$sAlias}.{$sourceField}";
            }
            if(strpos($destField, '.') === false) {
                $destField = "{$alias}.{$destField}";
            }
            $sourceField = "ON {$sourceField} = {$destField}";
        } else {
            if($alias) $table .= ' '.$alias;
        }

        $this->joins[] = "\nLEFT JOIN {$table} {$sourceField}";
        return $this;
    }

    /**
     * Adds a WHERE condition to the search
     * @param $field String the field to search. May include comparison characters.
     * Examples:
     *  ->where('myfield="myvalue"')                       // WHERE table.myfield="myvalue"
     *  ->where('myfield', 'myvalue')                       // WHERE table.myfield = 'myvalue'
     *  ->where('myfield >', 'myvalue')                     // WHERE table.myfield > 'myvalue'
     *  ->where('? LIKE {}.myfield', 'myvalue', 'myalias')  // WHERE 'myvalue' LIKE myalias.myfield
     *  ->where('myfield', 'myvalue', 'myalias')            // WHERE myalias.myfield = 'myvalue'
     * @param String|null $value The value to compare against. If null, the entire comparison must be in $field
     * @param String|null $alias The alias to set for the $field. If $value is not set or $field contains '?', then the alias will not be appended to the field.
     * @return $this returns the query instance
     * @throws \InvalidArgumentException
     */
    public function where($field, $value=NULL, $alias=NULL) {
        if(!$alias) $alias = $this->table;

        if($value !== NULL) {
            if(is_array($value)) {
                if(!$value)
                    throw new \InvalidArgumentException("An empty array was passed to Column '{$field}'");
                $this->values = array_merge($this->values, $value);
                $field .= ' in (?' . str_repeat(', ?', sizeof($this->values) - 1) . ')';
            } else {
                $this->values[] = $value;
                if(strpos($field, '?') === false) {
                    $e = '=';
                    if(preg_match('/([=<>!]+|like)\s*$/i', $field))
                        $e = ' ';
                    $field .= $e . '?';
                }
            }
        }


        if(preg_match('/^(AND|OR|\(|\))$/i', $field)) {
            if($field == '(' && !$this->lastCond)
                $this->where[] = 'AND';
            if($field != ')')
                $this->lastCond = true;
            $this->where[] = $field;
            return $this;
        } else {
            $field = str_replace('{}', $alias, $field, $c);
            if($c==0 && strpos($field, '.') === false)
                $field = $alias . '.' . $field;
        }
        if (!$this->lastCond) {
            $this->where[] = $this->mFlags & self::DefaultLogicOR ? 'OR' : 'AND';
        }
        $this->where[] = $field;
        $this->lastCond = false;
        return $this;
    }

    /**
     * Set flags for this query instance
     * @param int $flags the flag or flags to set
     * @return $this the query instance
     * @throws \InvalidArgumentException
     */
    function setFlag($flags) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("setFlags 'flags' parameter must be an integer");
        $oldFlags = $this->mFlags;
        $this->mFlags |= $oldFlags;
        return $this;
    }
    /**
     * Unset flags for this query instance
     * @param int $flags the flag or flags to unset
     * @return $this the query instance
     * @throws \InvalidArgumentException
     */
    function unsetFlag($flags) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("setFlags 'flags' parameter must be an integer");
        $oldFlags = $this->mFlags;
        $this->mFlags = $oldFlags & ~$flags;
        return $this;
    }

    public function getSQL() {
        return implode('', $this->joins)
            ."\nWHERE ".($this->where ? implode(' ', $this->where) : '1');
    }
}