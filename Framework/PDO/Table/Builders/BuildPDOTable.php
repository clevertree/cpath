<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Table\Builders;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Table\Builders\Exceptions\TableArgumentNotFoundException;

class BuildPDOTable extends AbstractBuildPDOTable
{

    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @return void
     * @throws TableArgumentNotFoundException if the argument was not recognized
     */
    function processTableArg($arg) {
        throw new TableArgumentNotFoundException("Arg not found for table '" . $this->getTableName() . "': " . $arg);
    }

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {}
}