<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;


use CPath\Interfaces\IRequest;

interface ITheme {

    /**
     * Set up a view according to this theme
     * @param IView $View
     * @return mixed
     */
    function setupView(IView $View);
}