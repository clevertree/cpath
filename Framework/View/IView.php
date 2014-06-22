<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\View;


use CPath\Framework\Render\HTML\IRenderHTML;

interface IView extends IRenderHTML {

    /**
     * Return the view theme or null if none exists
     * @return mixed
     */
    //function getTheme();


    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View);
}

