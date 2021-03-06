<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Header\IHTMLSupportHeaders;

class HTMLConfig  {
	/** @var IHTMLValueRenderer[] */
	private static $mValueRenderers = array() ;

	static $DefaultClass = null;
	static $DefaultInputClass = 'input';

	static function addValueRenderer(IHTMLValueRenderer $Renderer) {
		self::$mValueRenderers[] = $Renderer;
	}

    /**
     * Return value renderer support headers
     * @return IHTMLSupportHeaders[]
     */
    static function getSupportHeaders() {
        $headers = array();
        foreach(self::$mValueRenderers as $Headers) {
            if($Headers instanceof IHTMLSupportHeaders) {
                $headers[] = $Headers;
            }
        }
        return $headers;
    }

	/**
	 * Render an html value
	 * @param String $value
	 * @return void|string
	 */
	static function renderValue($value) {
		foreach(self::$mValueRenderers as $Renderer)
			if($Renderer->renderValue($value))
				return;
		echo $value ? (($value)) : '&nbsp;';
	}

	/**
	 * Render a named html value
	 * @param String $name
	 * @param String $value
	 * @param null $arg1
	 * @return void|string
	 */
	static function renderNamedValue($name, $value, $arg1=null) {
		foreach(self::$mValueRenderers as $Renderer)
			if($Renderer->renderNamedValue($name, $value, $arg1))
				return;
		echo $value !== null ? (($value)) : '&nbsp;';
	}
}
