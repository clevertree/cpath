<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 10:15 AM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Data\Map\CallbackSequenceMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Data\Map\SequenceMapCallback;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\Form;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Validation\IValidation;

class HTMLSelectField extends HTMLInputField implements ISequenceMap
{
	const NODE_TYPE = 'select';

	private $mValues = array();
	private $mSelected = array();

	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $classList a list of element classes
	 * @param String|null|Array|IAttributes|IValidation $_options [varargs] select options as string, array, or IValidation || IAttributes instance
	 * @internal param null|String $classList a list of class elements
	 */
	public function __construct($name = null, $classList=null, $_options = null) {
		parent::__construct($name);
		is_scalar($name)        ?: $this->addVarArg($name);
		is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=2; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

	public function setMultiple() {
		$this->setAttribute('multiple', 'multiple');
		return $this;
	}

	public function getInputValue() {
		return isset($this->mSelected[0])
			? $this->mSelected[0]
			: null;
	}

	public function setInputValue($value) {
		$this->select($value);
	}

	public function getFieldName()                      { return $this->getAttribute('name'); }
	public function setFieldName($name)                 { $this->setAttribute('name', $name); }

	public function getType()                           { return $this->getAttribute('type'); }
	public function setType($value)                     { $this->setAttribute('type', $value); }


	protected function addVarArg($arg, $allowHTMLAttributeString=false) {
		if(is_array($arg)) {
			foreach($arg as $k=>$a)
				$this->addOption($a, is_int($k) ? null : $k);
		} else if(is_string($arg)) {
			$this->addOption($arg);
		} else {
			parent::addVarArg($arg, $allowHTMLAttributeString);
		}
	}

	public function setOptions(Array $newOptions) {
		$this->mValues = array();
		$selected = $this->mSelected;
		$this->mSelected = array();

		foreach($newOptions as $k=>$a)
			$this->addOption($a, is_int($k) ? null : $k);

		foreach((array)$selected as $s)
			$this->select($s);
	}

	public function addOption($value, $description=null, $selected=false) {
		if($description) {
            if(!in_array($value, $this->mValues))
			    $this->mValues[$description] = $value;
		} else {
			if(!in_array($value, $this->mValues))
				$this->mValues[] = $value;
		}
		if($selected)
			$this->select($value);
	}

	public function select($value, $_value=null) {
		foreach(func_get_args() as $value)
			if(in_array($value, $this->mValues))
				$this->mSelected[] = $value;
	}

	public function deselect($value, $_value=null) {
		$c = sizeof($this->mSelected);
		$this->mSelected = array_diff($this->mSelected, func_get_args());
		return sizeof($this->mSelected) !== $c;
	}

	public function getSelectedValues() {
		return $this->mSelected;
	}

	public function isSelected($value) {
		return in_array($value, $this->mSelected);
	}

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @internal param \CPath\Request\IRequest $Request
	 */
	function mapSequence(ISequenceMapper $Map) {
		//$Map = new HTMLOptionMapper($Map, $this->mSelected);

		foreach($this->mValues as $description => $value) {
			if ($value instanceof ISequenceMap) {
				$value->mapSequence($Map);

			} else {
				$done = $Map->mapNext($value, is_string($description) ? $description : null, $this->isSelected($value));
				if($done === true)
					break;
			}
		}
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		RI::ai(1);

		$THIS = $this;
		$this->mapSequence(new CallbackSequenceMapper(
				function ($value, $description=null, $isSelected=false) use ($Request, $THIS) {
					$Input = $value instanceof IRenderHTML
						? $value
						: new HTMLSelectOptionElement($value, $description, $isSelected);
					$Input->renderHTML($Request, null, $THIS);
				}
			)
		);

		RI::ai(-1);
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return true;
	}
}

