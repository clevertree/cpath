<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Request;

use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IShortOptions;
use CPath\Log;
use CPath\LogException;
use CPath\Model\ArrayObject;
use CPath\Model\MissingRoute;
use CPath\Router;

class CLI extends ArrayObject implements ILogListener, IRequest, IShortOptions {

    private
        $mMethod,
        $mPath,
        $mHeaders = array(),
        $mArgs = array(),
        $mPos = 0,
        $mRequest = array(),
        $mShortRequests = array();
    /** @var IRoute */
    private
        $mRoute = NULL;

    protected function __construct(Array $args) {

        if(!$args[0]) {
            $this->mMethod = 'CLI';
        } else {
            if(preg_match('/^('.IRoute::METHODS.')(?: (.*))?$/i', $args[0], $matches)) {
                array_shift($args);
                $this->mMethod = strtoupper($matches[1]);
                if(!empty($matches[2]))
                    array_unshift($args, $matches[2]);
            } else {
                $this->mMethod = 'CLI';
            }
        }

        $args2 = array();
        for($i=0; $i<sizeof($args); $i++) {
            if(is_array($args[$i])) {
                $this->mRequest = $args[$i] + $this->mRequest;
                continue;
            }
            $arg = trim($args[$i]);
            if($arg === '')
                return;
            if($arg[0] == '-') {
                $val = true;
                if(!empty($args[$i+1]) && $args[$i+1][0] !== '-')
                    $val = $args[++$i];

                if($arg[1] == '-')
                    $this->mRequest[substr($arg, 2)] = $val;
                else
                    $this->mShortRequests[substr($arg, 1)] = $val;
            } else {
                $args2[] = $arg;
            }
        }
        $args = $args2;

        if($args) {
            if($args[0])
                foreach(array_reverse(explode('/', array_shift($args))) as $a)
                    if($a) array_unshift($args, $a);
            $parse = parse_url('/'.implode('/', $args));
            if(isset($parse['query'])) {
                parse_str($parse['query'], $query);
                $this->mRequest = $query + $this->mRequest;
            }
            $this->mPath = $parse['path'];
            //$this->mArgs = $args;
        } else {
            $this->mPath = '/';
            //$this->mArgs = array();
        }
    }

    public function setOutputLog($enable=true) {
        $enable ? Log::addCallback($this) : Log::removeCallback($this);
    }

    // Implement IRequest

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String the method
     */
    function getMethod() { return $this->mMethod; }

    /**
     * Get the URL Path
     * @return String the url path starting with '/'
     */
    function getPath() { return $this->mPath; }

    /**
     * Returns Request headers
     * @param String|Null $key the header key to return or all headers if null
     * @return mixed
     */
    function getHeaders($key=NULL) {
        if($key === NULL)
            return $this->mHeaders;
        return isset($this->mHeaders[$key]) ? $this->mHeaders[$key] : NULL;
    }

//    /**
//     * Add an argument to the arg list
//     * @param String $arg the argument value toa dd
//     * @return void
//     */
//    function addArg($arg) {
//        $this->mArgs[] = $arg;
//    }

    /**
     * Return the next argument for this request
     * @return String argument
     */
    function getNextArg() {
        return isset($this->mArgs[$this->mPos])
            ? $this->mArgs[$this->mPos++]
            : NULL;
    }

    /**
     * Get the IRoute instance for this request
     * @return IRoute
     */
    function getRoute() {
        return $this->mRoute;
    }

//    /**
//     * Set the IRoute instance for this request
//     * @param IRoute $Route
//     * @return void
//     */
//    function setRoute(IRoute $Route) {
//        $this->mArgs = $Route->getRequestArgs($this);
//        $this->mRoute = $Route;
//    }

    /**
     * Merges an associative array into the current request
     * @param array $request the array to merge
     * @param boolean $replace if true, the array is replaced instead of merged
     * @return void
     */
    function merge(Array $request, $replace=false) {
        if($replace) $this->mRequest = $request;
        $this->mRequest = $request + $this->mRequest;
    }

    /**
     * Attempt to find a Route
     * @return IRoute the route instance found. MissingRoute is returned if no route was found
     */
    public function findRoute() {
        $routePath = $this->mMethod . ' ' . $this->mPath;
        $Route = Router::findRoute($routePath, $args)
            ?: new MissingRoute($routePath);
        $this->mRoute = $Route;
        $this->mArgs = $args;
        return $Route;
    }

    // Implement ILogListner

    function onLog(ILogEntry $log)
    {
        echo $log->getMessage(),"\n";
        if($log instanceof LogException)
            echo $log->getException();
    }

    // Extend ArrayObject

    /**
     * Return a reference to this object's associative array
     * @return array the associative array
     */
    protected function &getArray() {
        return $this->mRequest;
    }

    /**
     * Returns a list of mimetypes accepted by this request
     * @return Array
     */
    function getMimeTypes() {
        return array('text/plain');
    }

    // Implement IShortOptions

    /**
     * Generate an associative array of short options from a set of fields
     * @param array $fields the fields to process
     * @return array a list of short-field key pairs
     */
    function processShortOptions(Array $fields) {
        $opts = array();

        foreach($fields as $short => $field)
            if(!is_int($short))
                $opts[$short] = $field;

        foreach($fields as $field) {
            $short = '';
            foreach(explode('_', $field) as $f2)
                $short .= $f2[0];

            $short = strtolower($short);
            if(!isset($opts[$short]))
                $opts[$short] = $field;
        }

        $i=97;
        foreach(array_diff($fields, $opts) as $field) {
            while(isset($opts[chr($i)]))
                $i++;
            if($i>122) break;
            $opts[chr($i)] = $field;
        }

        foreach($this->mShortRequests as $key=>$val) {
            if(isset($opts[$key])) {
                $this->mRequest[$opts[$key]] = $val;
                unset($this->mShortRequests[$key]);
            }
        }

        return $opts;
    }

    /**
     * Prevent notices and return null if the parameter is missing
     * @param mixed $offset
     * @return mixed|NULL .
     */
    public function offsetGet($offset) {
        return isset($this->mRequest[$offset]) ? $this->mRequest[$offset] : NULL;
    }

    // Statics

    static function fromArgs($_args) {
        if(!is_array($_args))
            $_args = func_get_args();
        if(sizeof($_args) == 1)
            $_args = explode(' ', $_args[0]);
        return new CLI($_args);
    }

    static function fromRequest($force=false) {
        static $CLI = NULL;
        if($CLI && !$force) return $CLI;
        $args = $_SERVER['argv'];
        array_shift($args);
        $CLI = new CLI($args);
        $CLI->setOutputLog(true);
        $CLI->mHeaders = function_exists('getallheaders')
            ? getallheaders()
            : array();
        return $CLI;
    }
}