<?php
namespace CPath\Handlers\Themes;

use CPath\Base;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\IFragmentTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Helpers\Describable;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;


class CPathDefaultTheme implements ITableTheme, IFragmentTheme {

    public function __construct() {
    }

    /**
     * Set up a view according to this theme
     * @param IView $View
     * @return mixed
     */
    function setupView(IView $View)
    {
        $basePath = Base::getClassPublicPath(__CLASS__, false);
        $View->addHeadStyleSheet($basePath . 'assets/cpathdefaulttheme.css');
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param IDescribable|String|Null $Description optional fragment header text or description
     * @return void
     */
    function renderFragmentStart(IRequest $Request, $Description=null)
    {
        echo RI::ni(), "<div class='fragment'>";
        echo RI::ai(1);
        if($Description) {
            echo RI::ni(), "<div class='fragment-title'>", Describable::get($Description)->getTitle(), "</div>";
        }
        echo RI::ni(), "<div>";
    }

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderFragmentEnd(IRequest $Request)
    {
        echo RI::ai(-1);
        echo RI::ni(1), "</div>";
        echo RI::ni(), "</div>";
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $headerText text that should appear in the footer
     * @return void
     */
    function renderTableStart(IRequest $Request, $headerText = NULL)
    {
        // TODO: Implement renderTableStart() method.
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableColumnStart(IRequest $Request)
    {
        // TODO: Implement renderTableColumnStart() method.
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableRowStart(IRequest $Request)
    {
        // TODO: Implement renderTableRowStart() method.
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableRowEnd(IRequest $Request)
    {
        // TODO: Implement renderTableRowEnd() method.
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableColumnEnd(IRequest $Request)
    {
        // TODO: Implement renderTableColumnEnd() method.
    }

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $footerText text that should appear in the footer
     * @return void
     */
    function renderTableEnd(IRequest $Request, $footerText = NULL)
    {
        // TODO: Implement renderTableEnd() method.
    }
}

