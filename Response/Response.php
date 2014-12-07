<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;

use CPath\Request\IRequest;

class Response implements IResponse, IResponseHeaders {
    private $mCode, $mMessage;
	private $mHeaders = array();

    /**
     * Create a new response
     * @param String $message the response message
     * @param int|bool $status the response status code or true/false for success/error
     * @internal param mixed $data additional response data
     */
    function __construct($message=NULL, $status=true) {
        $this->setStatusCode($status);
        $this->setMessage($message);
    }

	/**
	 * Add response headers to this response object
	 * @param String $name i.e. 'Location' or 'Location: /path'
	 * @param String|null $value i.e. '/path'
	 * @return $this
	 */
	function addHeader($name, $value=null) {
		$this->mHeaders[$name] = $value;
		return $this;
	}

	/**
	 * Send response headers for this response
	 * @param IRequest $Request
	 * @param string $mimeType
	 * @return bool returns true if the headers were sent, false otherwise
	 */
	function sendHeaders(IRequest $Request, $mimeType = null) {
		if(headers_sent())
			return false;

		header("HTTP/1.1 " . $this->getCode() . " " . preg_replace('/[^\w -]/', '', $this->getMessage()));
		header("MainContent-Type: " . $mimeType);

		foreach($this->mHeaders as $name => $value)
			switch($name) {
				default:
					header($value === null ? $name : $name . ': ' . $value);
					break;
			}

		return true;
	}

    function getCode() {
        return $this->mCode;
    }

    /**
     * @param int|bool $status
     * @return $this
     */
    function setStatusCode($status) {
        if(is_int($status))
            $this->mCode = $status;
        else
            $this->mCode = $status ? IResponse::HTTP_SUCCESS : IResponse::HTTP_ERROR;
        return $this;
    }

    /**
     * Get the Response Message
     * @return String
     */
    function getMessage() {
        return $this->mMessage;
    }

    /**
     * Set the message and return the Response
     * @param $msg
     * @return $this
     */
    function setMessage($msg) {
        $this->mMessage = $msg;
        return $this;
    }

    /**
     * Update and return the Response
     * @param $msg
     * @param $status
     * @return $this
     */
    function update($msg=null, $status=null) {
        if($msg !== null)
            $this->setMessage($msg);
        if($status !== null)
            $this->setStatusCode($status);
        return $this;
    }

}