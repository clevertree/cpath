<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


use CPath\Builders\RouteBuilder;

interface IRoutable {

    /**
     * Returns an array of all routes for this class
     * @param IRouteBuilder $Builder the IRouteBuilder instance
     * @return IRoute[]
     * @throws \CPath\BuildException when a route is not in a valid format
     */
    function getAllRoutes(IRouteBuilder $Builder);
}