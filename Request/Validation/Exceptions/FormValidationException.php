<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/11/14
 * Time: 1:19 PM
 */
namespace CPath\Request\Validation\Exceptions;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Validation\FormValidation;
use CPath\Response\IResponse;

class FormValidationException extends \Exception implements IResponse, IRenderHTML, IHTMLSupportHeaders
{
	/** @var FormValidation */
	private $mForm;

	public function __construct(FormValidation $Form, $message = null, $code=IResponse::HTTP_ERROR, \Exception $Previous) {
		parent::__construct($message ? : "Form", $code, $Previous);
		$this->mForm = $Form;
	}


	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$this->mForm->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		$this->mForm->renderHTML($Request, $Attr);
	}
}