<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\Api\Exceptions\APIException;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;

interface IAPIPostUserCallbacks {

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return \CPath\Framework\Api\Field\\CPath\Framework\Api\Field\Interfaces\IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     * @throws APIException
     */
    function preparePostUserFields(Array &$fields);

    /**
     * Perform on successful GetAPI execution
     * @param \CPath\Framework\PDO\Templates\User\Model\PDOUserModel $NewUser the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onPostUserExecute(PDOUserModel $NewUser, IRequest $Request, IResponse $Response);
}