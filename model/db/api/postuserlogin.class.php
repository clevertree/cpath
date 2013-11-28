<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\Api\PasswordField;
use CPath\Handlers\API;
use CPath\Handlers\Api\RequiredParam;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IUserSession;
use CPath\Model\Response;

interface IPostLoginExecute {

    /**
     * Perform on successful API_Get execution
     * @param PDOUserModel $User the logged in user account instance
     * @param IUserSession $Session the logged in user session
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onPostLoginExecute(PDOUserModel $User, IUserSession $Session, IRequest $Request, IResponse $Response);
}

class API_PostUserLogin extends API_Base {
    private $mUser;

    /**
     * Construct an instance of this API
     * @param PDOUserModel $Model the user source object for this API
     */
    function __construct(PDOUserModel $Model) {
        parent::__construct($Model);
        $this->mUser = $Model;
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupFields() {
        $this->addField('name', new RequiredParam("Username or Email Address"));
        $this->addField('password', new PasswordField("Password"));
        $this->generateFieldShorts();
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Log in as a ".$this->mUser->modelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        $User = $this->mUser;
        $Session = $User::login($Request['name'], $Request['password'], NULL, $User);
        $Response = new Response("Logged in as user '".$User->getUsername()."' successfully", true, array(
            'user' => $User,
            'session' => $Session,
        ));

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IPostLogoutExecute)
                $Response = $Handler->onPostLogoutExecute($User, $Session, $Request, $Response) ?: $Response;

        return $Response;
    }
}
