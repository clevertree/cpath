<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Interfaces\IResponseHelper;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IHandler;
use CPath\Model\MultiException;
use CPath\Model\Response;
use CPath\Model\ResponseException;
use CPath\Builders\BuildRoutes;
use CPath\Handlers\Api\View\ApiInfo;

/**
 * Class SimpleApi
 * @package CPath
 *
 * Provides a portable Handler template for API calls
 */
class SimpleApi extends Api {

    const BUILD_IGNORE = true;     // API Calls are built to provide routes

    const ROUTE_METHODS = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;        // No custom route path. Path is based on namespace + class name

    private $mCallback;

    /**
     * @param Callable $callback
     * @param ApiField[] $fields
     */
    public function __construct($callback, Array $fields) {
        $this->mCallback = $callback;
        $this->addFields($fields);
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    public function execute(Array $request){
        $call = $this->mCallback;
        if($call instanceof \Closure)
            return $call($this, $request);
        return call_user_func($call, $request);
    }

}