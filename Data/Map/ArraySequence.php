<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 6:15 PM
 */
namespace CPath\Data\Map;

class ArraySequence implements ISequenceMap
{
    private $mArr;

    public function __construct(Array $array=array()) {
        $this->mArr = $array;
    }

	public function addKeyPair($key, $value) {
		$this->mArr[$key] = $value;
	}

	public function add($value) {
		$this->mArr[] = $value;
	}

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @internal param \CPath\Data\Map\IRequest $Request
	 * @internal param \CPath\Data\Map\IRequest $Request
	 * @return mixed
	 */
    function mapSequence(ISequenceMapper $Map) {
        foreach ($this->mArr as $value)
            $Map->mapNext($value);
    }
}

