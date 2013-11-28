<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\IAPIPostCallbacks;
use CPath\Model\DB\Interfaces\IAssignAccess;
use CPath\Model\Response;

class API_Post extends API_Base {

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    final protected function setupFields() {
        $Model = $this->getModel();

        $fields = array();
        foreach($Model::findColumns($Model::INSERT ?: PDOColumn::FLAG_INSERT) as $Column)
            $fields[$Column->getName()] = $Column->generateAPIField();

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $fields = $Handler->preparePostFields($fields) ?: $fields;

        $this->addFields($fields);
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Create a new ".$this->getModel()->modelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        $Model = $this->getModel();

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAssignAccess)
                $Handler->assignAccessID($Request, IAssignAccess::INTENT_POST);

        $row = $Request->getDataPath();
        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $row = $Handler->preparePostInsert($row, $Request) ?: $row;

        if($Model instanceof PDOPrimaryKeyModel) {
            $NewModel = $Model::createAndLoad($row);
        } else {
            $NewModel = $Model::createAndFill($row);
        }

        $Response = new Response("Created " . $NewModel . " Successfully.", true, $NewModel);

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $Response = $Handler->onPostExecute($NewModel, $Request, $Response) ?: $Response;

        return $Response;
    }
}
