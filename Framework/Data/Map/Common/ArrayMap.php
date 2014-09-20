<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Map\Common;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IMappableKeys;

class ArrayMap implements IKeyMap, IMappableKeys {
    private $mMap = array(), $mIsAssoc = false;

    public function __construct(Array $map=array()) {
        if($map) {
            $this->mMap = $map;
            $this->mIsAssoc = true;
        }
    }

    /**
     * Returns an associative array of keys and data
     * @return Array associative array
     */
    function getMapData() {
        return $this->mMap;
    }

    /**
     * Map data to a key in the map
     * @param String $key
     * @param mixed $value
     * @param int $flags
     * @return void
     */

    function mapKeyValue($key, $value, $flags = 0) {
        $this->mIsAssoc = true;
        $this->mMap[$key] = $value;
    }

    /**
     * Map data to a data map
     * @param IKeyMap $Map the map instance to add data to
     * @return void
     */
    function mapKeys(IKeyMap $Map) {
        if($this->mIsAssoc) {
            foreach($this->mMap as $key => $data)
                $Map->map($key, $data);

        } else {
            foreach($this->mMap as $data)
                $Map->mapArrayValue($data);
        }
    }

    // Static

    static function get(IMappableKeys $Mappable) {
        $Inst = new ArrayMap();
        $Mappable->mapKeys($Inst);
        return $Inst->mMap;
    }

    /**
     * Map an object to this array
     * @param IMappableKeys $Mappable
     * @return void
     */
    function mapArrayObject(IMappableKeys $Mappable)
    {
        // TODO: Implement mapArrayObject() method.
    }

    /**
     * Add a value to the array
     * @param mixed $value
     * @return void
     */
    function mapArrayValue($value)
    {
        // TODO: Implement mapArrayValue() method.
    }

    /**
     * Map data to subsection
     * @param $subsectionKey
     * @param \CPath\Data\Map\IMappableKeys $Mappable
     * @return void
     */
    function mapSubsection($subsectionKey, IMappableKeys $Mappable)
    {
        // TODO: Implement mapSubsection() method.
    }
}