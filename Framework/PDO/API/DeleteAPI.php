<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;


use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\PDO\Interfaces\IAPIGetCallbacks;
use CPath\Framework\PDO\Interfaces\IWriteAccess;
use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

class DeleteAPI extends GetAPI implements IAPIGetCallbacks {

    /**
     * Construct an inst of the GET API
     * @param PDOPrimaryKeyTable $Table the table inst
     * @param string|array $searchColumns a column or array of columns that may be used to search for Models.
     * PRIMARY key is already included
     */
    function __construct(PDOPrimaryKeyTable $Table, $searchColumns=NULL) {
        parent::__construct($Table);
    }

    /**
     * Get the Object Description
     * @return \CPath\Data\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Delete a ".$this->getTable()->getModelName();
    }

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     */
    function prepareGetFields(Array &$fields) {
    }

    /**
     * Perform on successful GetAPI execution
     * @param \CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel $Model the returned model
     * @param IRequest $Request
     * @param \CPath\Response\IResponse $Response
     * @return IResponse|null
     */
    function onGetExecute(PDOPrimaryKeyModel $Model, IRequest $Request, IResponse $Response) {

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IWriteAccess)
                $Handler->assertWriteAccess($Model, $Request, IWriteAccess::INTENT_DELETE);

        $Model->remove();

        return new DataResponse("Removed {$Model}", true, $Model);
    }
}
