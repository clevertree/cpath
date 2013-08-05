<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\DataBase\PostGreSQL;
use CPath\BuildException;
use CPath\Base;
use CPath\Build;
use CPath\Interfaces\IBuilder;
use CPath\Log;


class BuildMySQLTables extends BuildPDOTables {
    const DB_CLASSNAME = "CPath\\Model\\DB\\MysqlDatabase";

    const TMPL_TABLE_CLASS = <<<PHP
<?php
namespace %s;
use CPath\Model\DB\MySQLTable;
class %s extends MySQLTable {
%s}
PHP;


    /**
     * @param \PDO $DB
     * @return BuildPDOTable[]
     */
    protected function getTables(\PDO $DB){
        $tables = array();
        foreach($DB->query("SHOW TABLE STATUS") as $row) {
            $tables[] = BuildPDOTable::create($row['Name'], $row['Comment']);
        }
        return $tables;
    }

    /**
     * @param \PDO $DB
     * @param BuildPDOTable $Table
     * @return void
     */
    protected function getIndexes(\PDO $DB, BuildPDOTable $Table) {
        foreach($DB->query("SHOW KEYS FROM `{$Table->Name}`;") as $row) {
            $name = $row['Column_name'];
            $Column = $Table->getColumn($name);
            $Column->Index = true;
            if(stripos($row['Key_name'], 'PRIMARY') === 0)
                $Column->Primary = true;
        }
    }

    /**
     * @param \PDO $DB
     * @param BuildPDOTable $Table
     * @return void
     */
    protected function getColumns(\PDO $DB, BuildPDOTable $Table) {
        foreach($DB->query("SHOW FULL COLUMNS FROM `{$Table->Name}`;") as $row) {
            $name = $row['Field'];
            if(preg_match('/^enum\((.*)\)$/', $row['Type'], $matches)) {
                $enum = array();
                foreach( explode(',', $matches[1]) as $value )
                    $enum[] = trim( $value, "'" );
                $type = $enum;
            } else {
                $type = stripos($row['Type'], 'int') !== false ? 'i' : 's';
            }
            $Column = new BuildPDOColumn($name, $type, $row['Comment']);
            $Column->AutoInc = $row['Extra'] == 'auto_increment';
            $Table->addColumn($Column);
        }
    }

    protected function getProcs(\PDO $DB) {
        $procs = array();
        foreach($DB->query(
                    "SELECT r.routine_name"
                        ."  FROM information_schema.routines r"
                        ."  LEFT JOIN information_schema.parameters p on r.specific_name = p.specific_name"
                        ."  WHERE p.parameter_mode = 'IN'"
                        ."  ORDER BY r.specific_name, p.ordinal_position") as $row) {
            $name = $row['routine_name'];
            $sname = $row['specific_name'];
            if(empty($procs[$sname])) $procs[$sname] = array($name);
            if($row['parameter_name'])
                $procs[$sname][] = $row['parameter_name'];
        }
        return $procs;
    }
}
