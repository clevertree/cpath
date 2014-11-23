<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 11:14 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;

class PasswordField extends Parameter
{
	const PASS_BLANK = '*****';

	private $mRequired;

	public function __construct($paramName, $description=null, $required=false) {
		parent::__construct($paramName, $description);
		$this->mRequired = $required;
	}

	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @throw Exception if validation failed
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value, $fieldName = null) {
		//$value = $Request[$this->getFieldName()];
		$value = parent::validate($Request, $value, $fieldName ?: $this->getFieldName());
		if (!$Request instanceof IFormRequest) {
			if(!$this->mRequired)
				return null;
			throw new RequestException("Password field value must come from a form request: " . $this->getFieldName());
		}
		if($value === self::PASS_BLANK)
			$value = null;
		$value = $this->filter($Request, $value);
		if($this->mRequired && !$value) {
			throw new RequestException("Password was not entered");
		}
		//$this->Input->setValue($value);
		return $value;
	}

}

