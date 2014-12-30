<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Data\Map;

use CPath\Render\HTML\Attribute;


class CallbackMapper implements IKeyMapper, ISequenceMapper
{
    private $mCallback;

    function __construct(\Closure $callback) {
        $this->mCallback = $callback;
    }


    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @return bool true to stop or any other value to continue
     */
    function map($key, $value) {
        $call = $this->mCallback;
        return $call($key, $value);
    }

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool true to stop or any other value to continue
	 */
	function mapNext($value, $_arg = null) {
		return call_user_func_array($call, func_get_args());
	}
}
