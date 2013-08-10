<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIRequiredField;
use CPath\Handlers\APIRequiredParam;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\InvalidAPIException;
use CPath\Model\DB\Interfaces\ILimitApiQuery;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\DB\Interfaces\ISecurityPolicy;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;

/**
 * Class Policy_Public implements a 'public' security policy that asserts no permissions
 * @package CPath\Model\DB
 */
class Policy_Owner implements ISecurityPolicy {
    private $mUser;
    private $mReadOther;
    private $mDelete;

    /**
     * Create a 'user' security policy
     * @param PDOUserModel $User an empty instance of the UserModel
     * @param bool $readOtherUsers allow 'GET' and 'GET search' on other user accounts
     * @param bool $deleteOwn allow 'DELETE' on own user account
     */
    public function __construct(PDOUserModel $User, $readOtherUsers=false, $deleteOwn=false) {
        $this->mUser = $User;
        $this->mReadOther = $readOtherUsers;
        $this->mDelete = $deleteOwn;
    }

    /**
     * Assert permission in default API calls such as GET, GET search, PATCH, and DELETE
     * Overwrite to enforce permission across API calls
     * @param PDOModel $User the User Model to assert access upon
     * @param IRequest $Request
     * @param int $intent the read intent. Typically IReadAccess::INTENT_GET or IReadAccess::INTENT_SEARCH
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     * @return void
     */
    function assertReadAccess(PDOModel $User, IRequest $Request, $intent) {
        $EmptyUser = $this->mUser;
        $id = $EmptyUser::getUserSession()->getID(); // Assert logged in
        if(!$this->mReadOther)
            if($User->columnValue($EmptyUser::COLUMN_ID) !== $id)
                throw new InvalidPermissionException("No permission to modify " . $User);
    }

    /**
     * Assert read permissions by Limiting API search queries endpoints such as GET, GET search, PATCH, and DELETE
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @param int $intent the read intent. Typically IReadAccess::INTENT_SEARCH
     * @return void
     */
    function assertQueryReadAccess(PDOWhere $Select, IRequest $Request, $intent) {
        $EmptyUser = $this->mUser;
        $id = $EmptyUser::getUserSession()->getID();    // Assert logged in
        if(!$this->mReadOther)
            $Select->where($EmptyUser::COLUMN_ID, $id);  // TODO: kinda silly no?
    }

    /**
     * Assert permission in default API calls 'POST, PATCH, and DELETE'
     * @param PDOModel $User the User Model to assert access upon
     * Note: during POST, $Model has no values
     * @param IRequest $Request
     * @param int $intent the read intent.
     * Typically IWriteAccess::INTENT_POST, IWriteAccess::INTENT_PATCH or IWriteAccess::INTENT_DELETE.
     * Note: during IWriteAccess::INTENT_POST, the instance $Model contains no data.
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     */
    function assertWriteAccess(PDOModel $User, IRequest $Request, $intent) {
        $EmptyUser = $this->mUser;
        switch($intent) {
            case IWriteAccess::INTENT_POST:
                break;
            case IWriteAccess::INTENT_PATCH:
                $id = $EmptyUser::getUserSession()->getID();    // Assert logged in
                if($User->columnValue($EmptyUser::COLUMN_ID) !== $id)
                    throw new InvalidPermissionException("No permission to modify " . $User);
                break;
            case IWriteAccess::INTENT_DELETE:
                $id = $EmptyUser::getUserSession()->getID();    // Assert logged in
                if(!$this->mDelete
                    || $User->columnValue($EmptyUser::COLUMN_ID) != $id)
                    throw new InvalidPermissionException("No permission to delete " . $User);
                break;
        }
    }
}
