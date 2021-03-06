<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Column\Types;


use CPath\Data\Describable\IDescribable;
use CPath\Framework\API\Exceptions\ValidationException;
use CPath\Framework\API\Field\EnumField;
use CPath\Framework\API\Field\Field;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Field\PasswordField;
use CPath\Framework\Interfaces\Constructable\IConstructable;
use CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn;
use CPath\Request\IRequest;
use CPath\Validate;

class PDOColumn implements IPDOColumn, IDescribable, IConstructable {
    const BUILD_IGNORE = true;

    protected
        $mName,
        $mComment,
        $mFlags,
        $mFilter,
        $mDefault,
        $mEnum;

    /**
     * Create a new Column
     * @param String $name the name
     * @param int $flags the flags
     * @param int $filter the default validation/filter
     * @param String $comment the comment
     * @param String $default the default value
     * @param Array $enum the enum values
     */
    function __construct($name, $flags, $filter=0, $comment=NULL, $default=NULL, $enum=NULL) {
        $this->mName = $name;
        $this->mFlags = $flags;
        $this->mFilter = $filter;
        $this->mComment = $comment;
        $this->mDefault = $default;
        $this->mEnum = $enum;
    }

    /**
     * Returns the column name
     * @return String the column name
     */
    function getName() {
        return $this->mName;
    }

    /**
     * Returns true one or more flags are set, otherwise false
     * Note: multiple flags follows 'OR' logic. Only one flag has to match to return true
     * @param int $flag the flag or flags to check
     * @return bool true one or more flags are set, otherwise false
     */
    function hasFlag($flag) {
        return $this->mFlags & $flag ? true : false;
    }

    /**
     * Get the comment for this column
     * @return String comment
     */
    function getMComment() {
        return $this->mComment
            ?: $this->mComment = ucwords(str_replace('_', ' ', $this->mName));
    }

    function hasDefaultValue() {
        return $this->mDefault ? true : false;
    }

    /**
     * Generate default value for this
     */
    function getDefaultValue() {
        if(!$this->mDefault)
            return NULL;
//        switch(strtolower($this->mDefault)) {
//            case 'time()': return time();
//            case 'uniqid()': return uniqid();
//        }
//        throw new \Exception("Invalid Default value: " . $this->mDefault);
        $eval = trim($this->mDefault);
        return eval('return ' . $eval . ';');
    }

    /**
     * Set the comment for this column
     * @param String $comment
     * @return void
     */
    function setComment($comment) {
        $this->mComment = $comment;
    }

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param \CPath\Request\IRequest $Request the request inst
     * @param String $fieldName the field name
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     */
    function validate(IRequest $Request, $fieldName) {
        $value = $Request[$fieldName];
        return Validate::inputField($this->mName, $value, $this->mFilter);
    }

    /**
     * Return an array of enum values for this Column
     * @return array enum values
     */
    function getEnumValues() {
        return $this->mEnum;
    }

    /**
     * Generate an IField for this column
     * @param boolean|NULL $comment
     * @param mixed $defaultValidation
     * @param int $flags optional IField:: flags
     * @internal param bool|NULL $required if null, the column flag FLAG_REQUIRED determines the value
     * @internal param bool|NULL $param
     * @return IField
     */
    function generateAPIField($comment=NULL, $defaultValidation=NULL, $flags=0) {
        if($this->mFlags & PDOColumn::FLAG_REQUIRED)
            $flags |= IField::IS_REQUIRED;

        if($this->mFilter)
            $defaultValidation = $this->mFilter;

        if($this->mEnum)
            $Field = new EnumField($this->getName(), $comment ?: $this->getMComment(), $defaultValidation, $flags, $this->mEnum);
        elseif($this->hasFlag(self::FLAG_PASSWORD))
            $Field = new PasswordField($this->getName(), $comment ?: $this->getMComment(), $defaultValidation, $flags);
        else
            $Field = new Field($this->getName(), $comment ?: $this->getMComment(), $defaultValidation, $flags);

        if($this->hasDefaultValue())
            $Field->setDefaultValue($this->getDefaultValue());
        return $Field;
    }


    /**
     * Get a simple public-visible title of this object as it would be displayed in a header (i.e. "Mr. Root")
     * @return String title for this Object
     */
    function getTitle() {
        $words = explode('_', $this->mName);
        foreach($words as &$word)
            if(strlen($word) <= 2)
                $word = strtoupper($word);
        return ucwords(implode(' ', $words));
    }

    /**
     * Get a simple public-visible description of this object as it would appear in a paragraph (i.e. "User account 'root' with ID 1234")
     * @return String simple description for this Object
     */
    function getDescription() {
        return $this->getMComment();
    }

    /**
     * Get a simple world-visible description of this object as it would be used when cast to a String (i.e. "root", 1234)
     * Note: This method typically contains "return $this->getTitle();"
     * @return String simple description for this Object
     */
    function __toString() {
        return $this->mName;
    }

    /**
     * Exports constructor parameters for code generation
     * @return Array constructor params for var_export
     */
    function exportConstructorArgs() {
        return array(
            $this->mName,
            $this->mFlags,
            $this->mFilter,
            $this->mComment,
            $this->mDefault,
            $this->mEnum
        );
    }

    // Static

    static function cls() {
        return get_called_class();
    }
}

