<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/17/14
 * Time: 7:12 PM
 */
namespace CPath\Route;

use CPath\Request\IRequest;

class RouteCallback implements IRouteMapper
{
    private $mCallback;
    /**
     * @var IRequest
     */
    private $mRequest;

    public function __construct(IRequest $Request, \Closure $Callback) {
        $this->mCallback = $Callback;
        $this->mRequest = $Request;
    }

    /**
     * Map a Route prefix to a target class or inst. Return true if the route prefix was matched
     * @param String $prefix route prefix i.e. GET /my/path
     * @param IRoutable|IRouteMap|String $target Request handler class name or inst
     * @param null $_arg Additional varargs
     * @return bool true if the route mapper should stop mapping, otherwise false to continue
     */
    function route($prefix, $target, $_arg=null) {
        $call = $this->mCallback;

	    if($target instanceof IRouteMap) {
		    return $target->mapRoutes($this->mRequest, $this);
	    }
        return call_user_func_array($call, func_get_args());
    }
}