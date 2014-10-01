<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/31/14
 * Time: 2:25 PM
 */
namespace CPath\Framework\Render\Header;

interface IHeaderWriter
{

    /**
     * Write a header as raw html
     * Note: Uniqueness of html is not checked. String will be written every time
     * @param String $html
     * @return IHeaderWriter return instance of self
     */
    function writeHTML($html);

    /**
     * Write a <script> header only the first time it's encountered
     * @param String $scriptPath the script url
     * @param bool $defer
     * @param null $charset
     * @return IHeaderWriter return instance of self
     */
    function writeScript($scriptPath, $defer = false, $charset = null);

    /**
     * Write a <link type="text/css"> header only the first time it's encountered
     * @param String $styleSheetPath the stylesheet url
     * @return IHeaderWriter return instance of self
     */
    function writeStyleSheet($styleSheetPath);
}
