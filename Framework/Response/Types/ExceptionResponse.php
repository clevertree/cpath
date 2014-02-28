<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Types;

use CPath\Config;


class ExceptionResponse extends DataResponse {
    /** @var \Exception */
    private $mEx;

    public function __construct(\Exception $ex) {
        $this->mEx = $ex;
        parent::__construct($ex->getMessage(), false);
    }

    function getException() {
        return $this->mEx;
    }

//    function toJSON(Array &$JSON) {
//        parent::toJSON($JSON);
//        if($this->mEx instanceof IJSON)
//            Util::toJSON($this->mEx, $JSON);
//        if(Config::$Debug) {
//            $ex = $this->mEx->getPrevious() ?: $this->mEx;
//            $trace = $ex->getTraceAsString();
//            $JSON['_debug_trace'] = $trace; //current(explode("\n", $trace));
//        }
//    }
//
//    function toXML(\SimpleXMLElement $xml)
//    {
//        parent::toXML($xml);
//        if($this->mEx instanceof IXML)
//            Util::toXML($this->mEx, $xml);
//        if(Config::$Debug) {
//            $ex = $this->mEx->getPrevious() ?: $this->mEx;
//            $trace = $ex->getTraceAsString();
//            $xml->addChild('_debug_trace', current(explode("\n", $trace)));
//        }
//    }
//
//    function renderText() {
//        parent::renderText();
//        if($this->mEx instanceof IText)
//            $this->mEx->renderText();
//        if(Config::$Debug) {
//            $ex = $this->mEx->getPrevious() ?: $this->mEx;
//            $trace = $ex->getTraceAsString();
//            echo "Trace: ", current(explode("\n", $trace));
//        }
//    }
//
//    function renderHtml() {
//        parent::renderHtml();
//        if($this->mEx instanceof IHTML)
//            $this->mEx->renderHtml();
//        if(Config::$Debug) {
//            throw $this->mEx->getPrevious() ?: $this->mEx;
//        }
//    }
}