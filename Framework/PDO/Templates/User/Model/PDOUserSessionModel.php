<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Model;

use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;

abstract class PDOUserSessionModel extends PDOPrimaryKeyModel implements ISession {

    /**
     * @return \CPath\Framework\PDO\Templates\User\Table\PDOUserSessionTable
     */
    function table() {
        return parent::table();
    }

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID() {
        $T = $this->table();
        return $this->{$T::COLUMN_USER_ID};
    }

    /**
     * End a user session
     */
    function endSession()
    {
        throw new \InvalidArgumentException();
    }
}
