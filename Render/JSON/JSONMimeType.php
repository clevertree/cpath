<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Render\JSON;

use CPath\Response\IResponse;
use CPath\Request\IRequest;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\MimeType;

class JSONMimeType extends MimeType
{
    public function __construct($typeName='application/json', IRequestedMimeType $nextMimeType=null) {
        parent::__construct($typeName, $nextMimeType);
    }

    /**
     * Send response headers for this mime type
     * @param int $code HTTP response code
     * @param String $message response message
     * @internal param \CPath\Request\IRequest $Request
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders($code = 200, $message = 'OK') {
        if(!parent::sendHeaders($code, $message))
            return false;

        header('Access-Control-Allow-Origin: *');

        return true;
    }

}