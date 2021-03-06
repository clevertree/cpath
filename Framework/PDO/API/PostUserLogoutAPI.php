<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;

use CPath\Base;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Framework\User\Session\SessionNotFoundException;
use CPath\Request\IRequest;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;


interface IPostLogoutExecute {

    /**
     * Perform on successful GetAPI execution
     * @param PDOUserModel $User the logged out user account inst
     * @param IRequest $Request
     * @param \CPath\Response\IResponse $Response
     * @return \CPath\Response\IResponse|null
     */
    function onPostLogoutExecute(PDOUserModel $User, IRequest $Request, IResponse $Response);
}


class PostUserLogoutAPI extends AbstractPDOAPI
{
    private $mTable, $mLoggedIn = false;

    /**
     * Construct an inst of this API
     * @param \CPath\Framework\PDO\Templates\User\Table\PDOUserTable $Table the user source object for this API
     */
    function __construct(PDOUserTable $Table)
    {
        if (!Base::isCLI() && $SessionUser = $Table->loadBySession(false, false)) {
            $this->mLoggedIn = true;
        }
        $this->mTable = $Table;
        parent::__construct($this->mTable);
    }

    protected function setupFields()
    {
        //$this->addField('password', new RequiredParam("Password"));
        //$this->generateFieldShorts();
    }

    /**
     * Get the Object Description
     * @return \CPath\Data\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable()
    {
        if ($this->mLoggedIn)
            return "Log out as " . $this->mTable;
        return "Log out (Requires user session)";
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and args
     * @param Array $args additional arguments for this execution
     * @return \CPath\Response\IResponse|mixed the api call response with data, message, and status
     */
    final function execute(IRequest $Request, $args)
    {
        $Session = $this->mTable->session()->loadBySession();
        $User = $this->mTable->loadBySession(true, false);
        try {
            $Session->endSession();
            $Response = new DataResponse("Logged out successfully", true);
        } catch (SessionNotFoundException $ex) {
            $Response = new ExceptionResponse($ex);
        }

        foreach ($this->getHandlers() as $Handler)
            if ($Handler instanceof IPostLogoutExecute)
                $Response = $Handler->onPostLogoutExecute($User, $Request, $Response) ? : $Response;

        return $Response;
    }
}
