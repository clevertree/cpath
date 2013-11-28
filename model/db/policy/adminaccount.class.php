<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Interfaces\IRequest;
use CPath\Model\DB\Interfaces\ISecurityPolicy;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;

/**
 * Class Policy_AdminAccount implements an Admin-Only security policy
 * @package CPath\Model\DB
 */
class Policy_AdminAccount extends Policy_UserAccountViewer {

    private $mUser;

    /**
     * Create an 'admin only' security policy
     * @param PDOUserModel $User an instance of the current user session
     */
    public function __construct(PDOUserModel $User) {
        $this->mUser = $User;
    }

    /**
     * Assert permission in default API calls such as GET, GET search, PATCH, and DELETE
     * Overwrite to enforce permission across API calls
     * @param PDOModel $Model the User Model to assert access upon
     * @param IRequest $Request
     * @param int $intent the read intent. Typically IReadAccess::INTENT_GET or IReadAccess::INTENT_SEARCH
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     * @return void
     */
    function assertReadAccess(PDOModel $Model, IRequest $Request, $intent) {
        if(!$this->getUserAccount()->isAdmin())
            throw new InvalidPermissionException("No permission to modify " . $Model);
    }

    /**
     * Assert read permissions by Limiting API search queries endpoints such as GET, GET search, PATCH, and DELETE
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @param int $intent the read intent. Typically IReadAccess::INTENT_SEARCH
     * @return void
     * @throws InvalidPermissionException if the user is not an Admin account
     */
    function assertQueryReadAccess(PDOWhere $Select, IRequest $Request, $intent) {
        if(!$this->getUserAccount()->isAdmin())
            throw new InvalidPermissionException("No permission to query");
    }

    /**
     * Assert permission in default API calls 'POST, PATCH, and DELETE'
     * @param PDOModel $User the User Model to assert access upon
     * Note: during POST, $Model has no values
     * @param IRequest $Request
     * @param int $intent the read intent.
     * Typically IWriteAccess::INTENT_POST, IWriteAccess::INTENT_PATCH or IWriteAccess::INTENT_DELETE.
     * Note: during IWriteAccess::INTENT_POST, the instance $Model contains no data.
     * @throws InvalidPermissionException if the user account is not an Admin account
     */
    function assertWriteAccess(PDOModel $User, IRequest $Request, $intent) {
        if(!$this->getUserAccount()->isAdmin())
            throw new InvalidPermissionException("No permission to query");
    }

    /**
     * Assign Access ID and assert permission in default POST API calls.
     * Typically this involves updating the $Request column (ex. user_id, owner_id) with the correct access identifier before the POST occurs.
     * Additionally, an InvalidPermissionException should be thrown if there is no permission to POST
     * @param IRequest $Request
     * @param int $intent the read intent. Typically IAssignAccess::INTENT_POST
     * @throws InvalidPermissionException if the user does not have permission to create this Model
     */
    function assignAccessID(IRequest $Request, $intent) {
        if(!$this->getUserAccount()->isAdmin())
            throw new InvalidPermissionException("No permission to query");
    }

    /**
     * Return the user model instance
     * @return PDOUserModel
     */
    function getUserAccount() { return $this->mUser; }
}