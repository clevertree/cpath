<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Interfaces\IViewConfig;

interface ITheme extends IViewConfig, ITableTheme, IFragmentTheme, IPageTheme, ISearchTheme, IBrowseTheme{
}