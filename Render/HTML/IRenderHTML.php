<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

interface IRenderHTML {

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr=null);
}

