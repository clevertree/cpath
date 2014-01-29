<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User;

use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Framework\User\IncorrectUsernameOrPasswordException;
use CPath\Framework\User\IUser;
use CPath\Framework\User\Session\ISessionManager;


/**
 * Class PDOUserModel
 * A PDOModel for User Account Tables
 * Provides additional user-specific functionality and API endpoints
 * @package CPath\Framework\PDO
 */
abstract class PDOUserModel extends PDOPrimaryKeyModel implements IUser {

    /** Confirm Password by default */
    //const PASSWORD_CONFIRM = true;

//    public function __construct() {
//        $this->mFlags = (int)$this->{$T::COLUMN_FLAGS};
//    }

//    public function getUsername() { return $this->{$T::COLUMN_USERNAME}; }
//    public function setUsername($value, $commit=true) { return $this->updateColumn($T::COLUMN_USERNAME, $value, $commit); }
//
//    public function getEmail() { return $this->{$T::COLUMN_EMAIL}; }
//    public function setEmail($value, $commit=true) { return $this->updateColumn($T::COLUMN_EMAIL, $value, $commit, FILTER_VALIDATE_EMAIL); }


    /**
     * @return ISessionManager
     */
    abstract function session();

    /**
     * @return PDOUserTable
     */
    abstract function table();


    function getFlags() {
        $T = $this->table();
        return (int)$this->{$T::COLUMN_FLAGS};
    }

    function addFlags($flags, $commit=true) {
        $T = $this->table();
        if(!is_int($flags))
            throw new \InvalidArgumentException("addFlags 'flags' parameter must be an integer");
        $flags = $this->getFlags() & ~$flags;
        $this->updateColumn($T::COLUMN_FLAGS, $flags, $commit);
    }

    function removeFlags($flags, $commit=true) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("removeFlags 'flags' parameter must be an integer");

        $flags = $this->getFlags() & ~$flags;
        $T = $this->table();
        $this->updateColumn($T::COLUMN_FLAGS, $flags, $commit);
    }

    function checkPassword($password) {
        $T = $this->table();
        $hash = $this->{$T::COLUMN_PASSWORD};
        if($T->hashPassword($password, $hash) == $hash)
            throw new IncorrectUsernameOrPasswordException();
    }

    function changePassword($newPassword, $confirmPassword=NULL) {
        $T = $this->table();
        if($confirmPassword !== NULL)
            $T->confirmPassword($newPassword, $confirmPassword);
        if(!$newPassword)
            throw new \InvalidArgumentException("Empty password provided");
        $this->updateColumn($T::COLUMN_PASSWORD, $newPassword, true); // It should auto hash
    }

    /**
     * Returns true if the user is a guest account
     * @return boolean true if user is a guest account
     */
    function isGuestAccount() {
        return $this->getFlags() & IUser::FLAG_GUEST ? true : false;
    }

    /**
     * Returns true if the user is an admin
     * @return boolean true if user is an admin
     */
    function isAdmin() {
        return $this->getFlags() & IUser::FLAG_ADMIN ? true : false;
    }

    /**
     * Returns true if the user is viewing debug mode
     * @return boolean true if user is viewing debug mode
     */
    function isDebug() {
        return $this->getFlags() & IUser::FLAG_DEBUG ? true : false;
    }


    /**
     * UPDATE a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    function updateColumn($column, $value, $commit=true) {
        $T = $this->table();
        if($column == $T::COLUMN_PASSWORD)
            $value = $T->hashPassword($value);
        return parent::updateColumn($column, $value, $commit);
    }

}