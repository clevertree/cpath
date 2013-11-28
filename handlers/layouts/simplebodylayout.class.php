<?php
namespace CPath\Handlers\Layouts;

use CPath\Handlers\View;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class SimpleBodyLayout extends View {

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyHeaderContent(IRequest $Request);

    /**
     * Render the page center content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyContent(IRequest $Request);

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyFooterContent(IRequest $Request);


    protected function renderBodyHeader(IRequest $Request) {
        echo RI::ni(), "<div class='header'>";
        RI::ai(1);
        $this->renderBodyHeaderContent($Request);
        RI::ai(-1);
        echo RI::ni(), "</div>";
    }

    /**
     * Render the view body
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final function renderBody(IRequest $Request) {
        echo RI::ni(), "<body>";
        RI::ai(1);
        $this->renderBodyHeader($Request);
        $this->renderBodyContent($Request);
        $this->renderBodyFooter($Request);
        RI::ai(-1);
        echo RI::ni(), "</body>";
    }

    protected function renderBodyFooter(IRequest $Request) {
        echo RI::ni(), "<div class='footer'>";
        RI::ai(1);
        $this->renderBodyFooterContent($Request);
        RI::ai(-1);
        echo RI::ni(), "</div>";
    }
}

