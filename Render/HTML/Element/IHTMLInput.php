<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 3:16 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Common\IInputField;

interface IHTMLInput extends IInputField, IRenderHTML
{
	public function getFieldID();
	public function setFieldID($value);
	public function setInputValue($value);
	public function setFieldName($name);
}

