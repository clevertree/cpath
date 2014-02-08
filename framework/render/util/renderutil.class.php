<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Util;

use CPath\Framework\Render\Interfaces\IRender;
use CPath\Framework\Render\Interfaces\IRenderHtml;
use CPath\Framework\Render\Interfaces\IRenderJSON;
use CPath\Framework\Render\Interfaces\IRenderText;
use CPath\Framework\Render\Interfaces\IRenderXML;
use CPath\Framework\Request\Interfaces\IRequest;

class MissingRenderModeException extends \Exception {}

class RenderUtil implements IRender {
    private $mTarget;

    function __construct($Target) {
        $this->mTarget = $Target;
    }

    public function getTarget() {
        return $this->mTarget;
    }

    /**
     * Render this API Call. The output format is based on the requested mimeType from the browser
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @throws MissingRenderModeException
     * @return void
     */
    function render(IRequest $Request) {
        $T = $this->mTarget;
        foreach($Request->getMimeTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    if($T instanceof IRenderJSON) {
                        $T->renderJSON($Request);
                        return;
                    }
                    break;

                case 'application/xml':
                    if($T instanceof IRenderXML) {
                        $T->renderXML($Request);
                        return;
                    }
                    break;

                case 'text/html':
                    if($T instanceof IRenderHtml) {
                        $T->renderHTML($Request);
                        return;
                    }
                    break;

                case 'text/plain':
                    if($T instanceof IRenderText) {
                        $T->renderText($Request);
                        return;
                    }
                    break;
            }
        }

        if($T instanceof IRender) {
            $T->render($Request);
            return;
        }

        if($T instanceof IRenderText) {
            $T->renderText($Request);
            return;
        }

        throw new MissingRenderModeException("Render mode could not be determined for: " . implode(', ', $Request->getMimeTypes()));
    }

}