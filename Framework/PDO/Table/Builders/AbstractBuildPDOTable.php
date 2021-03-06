<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/16/14
 * Time: 7:06 PM
 */
namespace CPath\Framework\PDO\Table\Builders;

use CPath\Build\Code\BuildPHPClass;
use CPath\Build\Exceptions\BuildException;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\DB\PDODatabase;
use CPath\Framework\PDO\Table\Builders\Exceptions\TableArgumentNotFoundException;
use CPath\Framework\PDO\Table\Builders\Interfaces\IPDOTableBuilder;
use CPath\Framework\PDO\Table\Column\Builders\BuildPDOColumn;
use CPath\Framework\PDO\Table\Column\Template\Interfaces\IPDOColumnTemplate;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Framework\PDO\Util\PDOStringUtil;

abstract class AbstractBuildPDOTable implements IPDOTableBuilder
{
    private $mName, $mTitle, $mModelName, $mModelClassName, $mTableClassName, $mComment, $mNamespace,
        $mSearchWildCard, $mSearchLimit, $mSearchLimitMax, $mAllowHandler = false, $mTemplateID;

    /** @var BuildPDOColumn[] */
    private $mColumns;

    protected $mUnfound = array(); // TODO: private?
    protected $mArgs = array();

    /** @var IPDOColumnTemplate[] */
    private $mColumnTemplates = array();

    private $mPDOTableClass, $mPDOModelClass;

    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @return void
     * @throws TableArgumentNotFoundException if the argument was not recognized
     */
    abstract function processTableArg($arg);

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @return void
     */
    abstract function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel);


    /**
     * Create a new PDOTable builder inst
     * @param \PDO $DB
     * @param String $name the table name
     * @param String $comment the table comment
     * @param null $PDOTableClass the PDOTable class to use
     * @param null $PDOModelClass the PDOModel class to use
     * @internal param $namespace
     */
    public function __construct(\PDO $DB, $name, $comment, $PDOTableClass = null, $PDOModelClass = null) {
        $this->mColumns = array();

        $this->mName = $name;
        $this->mTitle = ucwords(str_replace('_', ' ', $this->getTableName()));
        $this->mModelName = $this->getTableTitle();

        $this->mNamespace = dirname(get_class($DB));
        $this->mModelClassName = $this->mNamespace . '\\Model\\' . str_replace(' ', '', $this->getTableTitle()) . 'Model';
        $this->mTableClassName = $this->mNamespace . '\\UIElement\\' . str_replace(' ', '', $this->getTableTitle()) . 'UIElement';

        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace'), $comment);
        if (!$this->mComment)
            $this->mComment = $comment;
        if ($this->mComment)
            $this->mComment = str_replace(';', ':', $this->mComment);

        $this->mPDOModelClass = $PDOModelClass ? : PDOModel::cls();
        $this->mPDOTableClass = $PDOTableClass ? : PDOTable::cls();
    }

    /**
     * Returns the table name
     * @return string the model class name
     */
    function getTableName()
    {
        return $this->mName;
    }

    function getTableTitle()
    {
        return $this->mTitle;
    }

    function getTableComment()
    {
        return $this->mComment;
    }

    /**
     * Returns the model class name
     * @return string the model class name
     */
    function getTableClass() {
        return $this->mTableClassName;
    }

    function setTableClass($class)
    {
        $this->mTableClassName = $class;
        return $this;
    }

    function getModelClass() {
        return $this->mModelClassName;
    }

    function setModelClass($class)
    {
        $this->mModelClassName = $class;
        return $this;
    }

    function getNamespace() {
        return $this->mNamespace;
    }

    function getModelName()
    {
        return $this->mModelName;
    }

    function setModelName($name)
    {
        $this->mModelName = $name;
        return $this;
    }

    function init() {

        foreach ($this->mArgs as $field) {
            list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
            switch ($lcName = strtolower($name)) {
                case 'i':
                case 'insert':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->setFlag(PDOColumn::FLAG_INSERT);
                    break;
                case 'u':
                case 'update':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->setFlag(PDOColumn::FLAG_UPDATE);
                    break;
                case 's':
                case 'search':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->setFlag(PDOColumn::FLAG_SEARCH);
                    break;
                case 'e':
                case 'export':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->setFlag(PDOColumn::FLAG_EXPORT);
                    break;
                case 'r':
                case 'required':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->setFlag(PDOColumn::FLAG_REQUIRED);
                    break;
                case 'sw':
                case 'searchwildcard':
                    $this->mSearchWildCard = true;
                    break;
                case 'sl':
                case 'searchlimit':
                    list($this->SearchLimit, $this->SearchLimitMax) =
                        $this->req($name, $arg, '/^(\d+):(\d+)$/', '{default limit}:{max limit}');
                    break;
                case 'c':
                case 'comment':
                    $this->mComment = $this->req($name, $arg);
                    break;
                case 'n':
                case 'name':
                    $this->setModelName($this->req($name, $arg));
                    break;
                case 'ah':
                case 'api':
                case 'allowhandler':
                    $this->mAllowHandler = true;
                    break;
                default:
                    try {
                        $this->processTableArg($field);
                    } catch (Exceptions\TableArgumentNotFoundException $ex) {
                        $this->mUnfound[] = $ex;
                    }
            }
        }
        $this->mArgs = array();

        foreach ($this->getColumns() as $Column)
            if (!$Column->hasTemplate())
                foreach ($this->mColumnTemplates as $Template)
                    if ($Template->matchColumn($Column)) {
                        $Column->setTemplate($Template);
                        $Template->addColumn($Column);
                    }


    }

    function replace(array $matches) {
        foreach (explode('|', $matches[1]) as $field) {
            list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
            switch (strtolower($name)) {
                case 't':
                case 'template':
                    $this->mTemplateID = $this->req($name, $arg);
                    break;
                default:
                    $this->mArgs[] = $field;
            }
        }
        return '';
    }

    /**
     * @return BuildPDOColumn[]
     */
    public function getColumns() {
        return $this->mColumns;
    }

    /**
     * @param $name
     * @return BuildPDOColumn
     * @throws BuildException if the column is not found
     */
    public function getColumn($name) {
        if (!isset($this->mColumns[$name])) {
            throw new BuildException("Column '{$name}' not found" . print_r($this, true));
        }
        return $this->mColumns[$name];
    }

    public function addColumn(BuildPDOColumn $Column) {
        $this->mColumns[$Column->getName()] = $Column;
    }

    /**
     * Add a custom column template to this builder
     * @param IPDOColumnTemplate $Template
     */
    public function addColumnTemplate(IPDOColumnTemplate $Template) {
        $this->mColumnTemplates[] = $Template;
    }

    /**
     * Process PHP classes for a PDO Builder
     * @param PDODatabase $DB
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Build\Exceptions\BuildException
     * @return void
     */
    function processPHP(PDODatabase $DB, BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {
        //$this->processArgs();

        if ($this->mUnfound)
            throw new BuildException("Invalid UIElement Comment Token '" . implode('| ', $this->mUnfound) . "' in UIElement '{$this->getTableName()}'");


        $PHPModel->addConst('MODEL_NAME', $this->getModelName());
        //$PHPModel->addConst('TABLE_CLASS', $this->getTableClass());


        $PHPTable->setExtend($this->mPDOTableClass);
        $PHPModel->setExtend($this->mPDOModelClass);

        $this->processPHPTableMethods($DB, $PHPTable);
        $this->processPHPTableConstants($PHPTable);
        $this->processPHPModelTableMethod($PHPModel, $PHPTable);
        $this->processPHPModelProperties($PHPModel);
        $this->processPHPModelGetSet($PHPModel);
        $this->processTemplatePHP($PHPTable, $PHPModel);

        foreach ($this->mColumns as $Column)
            $Column->processTemplatePHP($PHPTable, $PHPModel);

        foreach ($this->mColumnTemplates as $Template)
            $Template->processTemplatePHP($PHPTable, $PHPModel);
    }

    function processPHPTableMethods(PDODatabase $DB, BuildPHPClass $PHPTable) {


        $PHPTable->addUse($this->getModelClass(), 'Model');
        $construct = "\t\t\$this->setModelClass(Model::cls());";

        $construct .= "\n\t\tparent::__construct(";


        $i = 0;

        /** @var BuildPDOColumn $Column */
        foreach ($this->getColumns() as $Column) {
            //$cols[] = $Column->exportConstructor();
            if ($i++) $construct .= ",";
            $construct .= "\n\t\t\t" . $Column->exportConstructor();
        }

        $construct .= "\n\t\t);";

        $PHPTable->addUse(PDOColumn::cls());

        $PHPTable->addMethod('__construct', '', $construct, 'final');

        $PHPTable->addUse(get_class($DB), 'DB');
        $PHPTable->addMethod('getDB', '', " return DB::get(); ");
    }

    function processPHPTableConstants(BuildPHPClass $PHPTable) {
        $PHPTable->addConst('TABLE', $this->getTableName());
        $PHPTable->addConstCode();

        $PHPTable->addConstCode("// UIElement Columns ");
        foreach ($this->getColumns() as $Column)
            $PHPTable->addConst(PDOStringUtil::toTitleCase($Column->getName(), true), $Column->getName());

        foreach ($this->getColumns() as $Column)
            if ($Column->mEnumConstants) {
                $PHPTable->addConstCode();
                $PHPTable->addConstCode("// Column Enum Values for '" . $Column->getName() . "'");
                foreach ($Column->mEnumValues as $enum)
                    $PHPTable->addConst(PDOStringUtil::toTitleCase($Column->getName(), true) . '_Enum_' . PDOStringUtil::toTitleCase($enum, true), $enum);
            }

        //$PHPTable->addConst('MODEL_CLASS', $this->getModelClass());
        if ($this->mSearchWildCard)
            $PHPTable->addConst('SEARCH_WILDCARD', true);
        if ($this->mSearchLimit)
            $PHPTable->addConst('SEARCH_LIMIT', $this->mSearchLimit);
        if ($this->mSearchLimitMax)
            $PHPTable->addConst('SEARCH_LIMIT_MAX', $this->mSearchLimitMax);
        //if ($UIElement->AllowHandler)
        //$PHPTable->addImplements('CPath\Interfaces\IBuildable');
    }

    function processPHPModelProperties(BuildPHPClass $PHPModel) {
        foreach ($this->getColumns() as $Column)
            $PHPModel->addProperty($Column->getName(), null, 'protected'); // Protected for db instances
    }

    function processPHPModelTableMethod(BuildPHPClass $PHPModel, BuildPHPClass $PHPTable) {
        $PHPModel->addUse($PHPTable->getName(), 'UIElement');
        $PHPModel->addMethod('loadTable', '', ' return new UIElement; ', 'protected');
        $PHPModel->addMethodCode();
    }

    protected function req($name, $arg, $preg = NULL, $desc = NULL)
    {
        if (!$arg || ($preg && !preg_match($preg, $arg, $matches)))
            throw new BuildException("UIElement Comment Token {$name} must be in the format {{$name}:" . ($desc ? : $preg ? : 'value') . '}');
        if (!$preg)
            return $arg;
        array_shift($matches);
        return $matches;
    }

    function processPHPModelGetSet(BuildPHPClass $PHPModel)
    {
        foreach ($this->getColumns() as $Column) {
            $name = $Column->getName();
            $ucName = PDOStringUtil::toTitleCase($name, true);
            $PHPModel->addMethod('get' . $ucName, '', sprintf(' return $this->%s; ', strtolower($Column->getName())));
            $PHPModel->addMethod('set' . $ucName, '$value', sprintf(' $this->%s = $value; return $this; ', strtolower($Column->getName())));
            $PHPModel->addMethodCode();
        }
    }

    function getTemplateID() {
        return $this->mTemplateID;
    }

    /**
     * @throws \Exception
     * @return null
     */
    function getDB() {
        throw new \Exception("Not implemented");
    }

    function __toString() {
        return $this->getTableName();
    }

    // Static

    static function cls() {
        return get_called_class();
    }
}

