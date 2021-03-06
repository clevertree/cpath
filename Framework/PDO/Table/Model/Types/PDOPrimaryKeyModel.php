<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Model\Types;

use CPath\Framework\PDO\Table\Model\Interfaces\IPDOPrimaryKeyModel;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Log;

abstract class PDOPrimaryKeyModel extends PDOModel implements IPDOPrimaryKeyModel {

    private $mCommit = NULL;

    /**
     * INSERT or UPDATE column values for this Model.
     * Note: if the primary key column value is null, INSERT will be used on the first commit, and UPDATE thereafter
     * @return int the number of columns updated
     * @throws \Exception if no primary key exists
     */
    function commitColumns() {

        /** @var PDOPrimaryKeyTable $Table */
        $Table = $this->table();
        $primary = $Table::COLUMN_PRIMARY;
        $id = $this->$primary;

        if(!$this->mCommit) {
            Log::v(get_called_class(), "No Fields Updated for " . $this);
            return 0;
        }

        if($id === null) {
            // Insert the model
            $this->$primary = $Table
                ->insert(array_keys($this->mCommit))
                ->requestInsertID($Table::COLUMN_PRIMARY)
                ->values(array_values($this->mCommit))
                ->getInsertID();
            Log::v(get_called_class(), "Created " . $this);
        } else {
            // Update the model
            $Table->update(array_keys($this->mCommit))
                ->where($primary, $id)
                ->values(array_values($this->mCommit));
            Log::v(get_called_class(), "Updated " . $this);
        }


//        $set = '';
//        $DB = static::getDB();
//        foreach($this->mCommit as $column=>$value)
//            $set .= ($set ? ",\n\t" : '') . "{$column} = ".$DB->quote($value);
//        $SQL = "UPDATE ".static::TABLE
//            ."\n SET {$set}"
//            ."\n WHERE ".static::PRIMARY." = ".$DB->quote($id);
//        $DB->exec($SQL);

        $c = sizeof($this->mCommit);
        $this->mCommit = NULL;
        //if(static::CACHE_ENABLED)
        //    static::$mCache->store(get_called_class() . ':id:' . $id, $this, static::CACHE_TTL);
        return $c;
    }

    /**
     * UPDATE a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    function updateColumn($column, $value, $commit=true) {
        if($this->$column == $value)
            return $this;
        $this->mCommit[$column] = $value;
        if($commit)
            $this->commitColumns();
        $this->$column = $value;
        return $this;
    }


    /**
     * Load column values for an active inst
     * @param String $_columns a varargs of strings representing columns
     * @return Array an array of column values
     * @throws \Exception if there is no PRIMARY key for this table
     */
    function loadColumnValues($_columns) {
        /** @var PDOPrimaryKeyTable $Table */
        $Table = $this->table();

        return $Table->select(func_get_args())
            ->where($Table::COLUMN_PRIMARY, $this->columnValue($Table::COLUMN_PRIMARY))
            ->fetch();
    }

    /**
     * Remove this inst from the database
     */
    function remove() {
        /** @var PDOPrimaryKeyTable $T */
        $T = $this->table();
        $primary = $T::COLUMN_PRIMARY;
        $id = $this->$primary;

        $T->removeByPrimary($id);
    }

    public function __toString() {
        /** @var PDOPrimaryKeyTable $Table */
        $Table = $this->table();

        if($id = $Table::COLUMN_TITLE ?: $Table::COLUMN_PRIMARY)
            return static::modelName() . " '" . $this->$id . "'";
        return parent::modelName();
    }

}
