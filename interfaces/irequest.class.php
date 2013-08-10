<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IRequest extends IArrayObject {

    /**
     * Get the URL Path starting at the root path of the framework
     * @return String the url path starting with '/'
     */
    function getPath();

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String the method
     */
    function getMethod();

    /**
     * Returns Request headers
     * @param String|Null $key the header key to return or all headers if null
     * @return mixed
     */
    function getHeaders($key=NULL);
//
//    /**
//     * Add an argument to the arg list
//     * @param String $arg the argument value toa dd
//     * @return void
//     */
//    function addArg($arg);

    /**
     * Return the next argument for this request
     * @return String argument
     */
    function getNextArg();

    /**
     * Returns a list of mimetypes accepted by this request
     * @return Array
     */
    function getMimeTypes();
    /**
     * Get the IRoute instance for this request
     * @return IRoute
     */
    function getRoute();

    /**
     * Attempt to find a Route
     * @return IRoute the route instance found. MissingRoute is returned if no route was found
     */
    public function findRoute();

    /**
     * Merges an associative array into the current request
     * @param array $request the array to merge
     * @param boolean $replace if true, the array is replaced instead of merged
     * @return void
     */
    function merge(Array $request, $replace=false);

    /**
     * Remove an element from the request array and return its value
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->pluck(0, 'key') removes $data[0]['key'] and returns it's value;
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException if the data path doesn't exist
     */
    function pluck($_path);

    // Statics

    static function fromRequest();
}