<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Table\Builders\Templates\User;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Table\Builders\AbstractBuildPDOPKTable;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Column\Template\Types\PDOSimpleColumnTemplate;
use CPath\Framework\PDO\Templates\User\Role\Model\PDOUserRoleModel;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;

class BuildPDOUserRoleTable extends AbstractBuildPDOPKTable {

    /**
     * Create a new BuildPDOUserRoleTable builder inst
     * @param \PDO $DB
     * @param String $name the table name
     * @param String $comment the table comment
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     */
    public function __construct(\PDO $DB, $name, $comment, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($DB, $name, $comment,
            $PDOTableClass ?: PDOUserRoleTable::cls(),
            $PDOModelClass ?: PDOUserRoleModel::cls()
        );
        BuildPDOUserTable::addUserRoleTable($this);

        $this->addColumnTemplate(new PDOSimpleColumnTemplate('user_id'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('class'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('data'));
    }
    /**
     * Process unrecognized table comment arguments
     * @param String $field the argument to process
     * @return void
     * @throws \InvalidArgumentException if the argument was not recognized
     */
    function processTableArg($field) {
        throw new \InvalidArgumentException("Arg not found : " . $field);
    }

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Build\Exceptions\BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {
//        $PHPModel->addMethod('getUserID', '', self::PHP_GETUSERID);
//        $PHPModel->addMethod('getData', '', self::PHP_GETUSERID);
//        $PHPModel->addMethod('getRoleClass', '', self::PHP_GETUSERID);
//
//        foreach($this->getColumns() as $Column) {
//            if(!$this->Column_User_ID && preg_match('/user.*id/i', $Column->Name))
//                $this->Column_User_ID = $Column->Name;
//            if(!$this->Column_Data && stripos($Column->Name, 'data') !== false)
//                $this->Column_Data = $Column->Name;
//            if(!$this->Column_Class && stripos($Column->Name, 'class') !== false)
//                $this->Column_Class = $Column->Name;
//        }

//        foreach(get_object_vars($this) as $field => $value) {
//            if(!$value)
//                throw new BuildException("The field name for {$field} could not be determined for ".__CLASS__);
//            $TablePHP->addConst(strtoupper($field), $value);
//        }

    }
}