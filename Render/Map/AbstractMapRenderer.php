<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 9:30 PM
 */
namespace CPath\Render\Map;

use CPath\Build\IBuildable;
use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\IRenderAll;
use CPath\Request\IRequest;
use CPath\Route\IRoutable;

abstract class AbstractMapRenderer implements IRenderAll, IKeyMapper, ISequenceMapper, IRoutable
{
	private $mIsArray = null;
	private $mMap;
	private $mCount = 0;
	private $mRequest = null;
	private $mFinished = false;

	/**
	 * @param IKeyMap|ISequenceMap $Map
	 */
	public function __construct($Map) {
		$this->mMap = $Map;
	}

	public function __destruct() {
		$this->flush();
	}

	function flush() {
		if($this->mFinished)
			return;
		$this->mFinished = true;

		if ($this->mIsArray === false) {
			$this->renderEnd($this->mIsArray);

		} else if ($this->mIsArray === true) {
			$this->renderEnd($this->mIsArray);
		}
	}

	function __clone() {
		$this->mIsArray   = null;
		$this->mCount     = 0;
		$this->mFinished  = false;
	}

	abstract protected function renderStart($isArray);

	abstract protected function renderEnd($isArray);

	protected function getRequest() {
		return $this->mRequest;
	}

	protected function getMap() {
		return $this->mMap;
	}

	protected function renderKeyValue($key, $value) {
		if (is_array($value))
			$value = new ArraySequence($value);

		if ($value instanceof IKeyMap && $this->mMap !== $value) {
			/** @var AbstractMapRenderer $Mapper */
//			if ($Mapper && $Mapper->mMap === $value) {
//				echo "Recursion detected at " . get_class($value) . "::mapSequence()";
//				return false;
//			}
			$Mapper = clone $this;
			$Mapper->mMap = $value;
			$value->mapKeys($Mapper);
			unset($Mapper);

		} elseif ($value instanceof ISequenceMap) {
			/** @var AbstractMapRenderer $Mapper */
			$Mapper = clone $this;
			$Mapper->mMap = $value;
			$value->mapSequence($Mapper);
			unset($Mapper);

		} elseif (is_string($value)) {
			echo $value ? nl2br(htmlspecialchars($value)) : '&nbsp;';

		} else {
			echo var_export($value, true);
			//echo RI::ni(), $value ? htmlspecialchars(new Description($value)) : '&nbsp;';

		}

		return true;
	}

	protected function renderValue($value) {
		if (is_array($value))
			$value = new ArraySequence($value);

		if ($value instanceof IKeyMap) {
			/** @var AbstractMapRenderer $Mapper */
			$Mapper = clone $this;
//			if ($Mapper && $Mapper->mMap === $value) {
//				echo "Recursion detected at " . get_class($value) . "::mapSequence()";
//				return false;
//			}
			$Mapper->mMap = $value;
			$value->mapKeys($Mapper);
			unset($Mapper);

		} elseif (is_string($value)) {
			echo $value ? nl2br(htmlspecialchars($value)) : '&nbsp;';

		} else {
			echo var_export($value, true);
			//echo RI::ni(), $value ? htmlspecialchars(new Description($value)) : '&nbsp;';

		}

		return true;
	}

	/**
	 * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String $key
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @return bool true to stop or any other value to continue
	 */
	function map($key, $value) {
		$this->mCount++;

		if ($this->mIsArray === null) {
			$this->mIsArray = false;
			$this->renderStart($this->mIsArray);
		}

		try {
			return $this->renderKeyValue($key, $value);

		} catch (\Exception $ex) {
			return $this->renderKeyValue($key, $ex->getMessage());
		}
	}

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool false to continue, true to stop
	 */
	function mapNext($value, $_arg = null) {
		$this->mCount++;

		if ($this->mIsArray === null) {
			$this->mIsArray = true;
			$this->renderStart($this->mIsArray);
		}

		try {
			return $this->renderValue($value);

		} catch (\Exception $ex) {
			return $this->renderValue($ex->getMessage());
		}
	}


	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest inst for this render
	 * @param bool $sendHeaders if true, sends the response headers
	 * @return bool returns false if no rendering occurred
	 */
	function render(IRequest $Request, $sendHeaders = true) {
		$this->mRequest = $Request;
		$Mappable       = $this->mMap;
		if ($Mappable instanceof IKeyMap) {
			$Mappable->mapKeys($this);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Mappable->mapSequence($this);
		}
		$this->mRequest = null;
		$this->flush();
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$this->render($Request);
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
		$this->render($Request);
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
		$this->render($Request);
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
		$this->render($Request);
	}
}