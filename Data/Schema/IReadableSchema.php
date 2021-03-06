<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 9:48 PM
 */
namespace CPath\Data\Schema;

define('IReadableSchema', __NAMESPACE__ . '\\IReadableSchema');
interface IReadableSchema {
	const interfaceName = IReadableSchema;
    /**
     * Write schema to a writable source
     * @param IWritableSchema $DB
     */
    public function writeSchema(IWritableSchema $DB);
}

