<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 3:00 PM
 */
namespace CPath\Request;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Log\StaticLogger;
use CPath\Request\Parameter\RequestParameterForm;
use CPath\Response\Exceptions\HTTPRequestException;
use CPath\Response\IResponse;

class RequestException extends HTTPRequestException implements IRenderHTML, IResponse, IHTMLSupportHeaders
{
    /**
     * @param string $message
     * @param null $statusCode
     * @param null $_arg [varargs] used to format the exception
     */
    function __construct($message, $statusCode=null, $_arg=null) {
        if($_arg !== null)
            $message = vsprintf($message, array_slice(func_get_args(), 2));
        parent::__construct($message, $statusCode);
    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Form = new RequestParameterForm();
        $Form->writeHeaders($Request, $Head);
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Form = new RequestParameterForm();
        $Form->renderHTML($Request, $Attr);
    }

}