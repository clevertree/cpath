<?php
namespace CPath\Handlers\Layouts;

use CPath\Handlers\Interfaces\IRenderContent;
use CPath\Handlers\View;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class NavBarLayout extends View implements IRenderContent {

    private $navBarStarted=false;

//    public function __construct($Target, ITheme $Theme=NULL) {
//        parent::__construct($Target, $Theme);
//    }


    protected function setupHeadFields() {
        parent::setupHeadFields();
        //$basePath = Base::getClassPublicPath(__CLASS__, false);
        //$this->addHeadStyleSheet($basePath . 'assets/navbarlayout.css');
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyHeaderContent(IRequest $Request);


    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderBodyFooterContent(IRequest $Request);

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    abstract protected function renderNavBarContent(IRequest $Request);


    /**
     * Render the navigation bar content
     * @param String $url the url for this navbar entry
     * @param String|NULL $text the html for this navbar entry
     * @param String|NULL $title the title for this navbar entry
     * @return void
     */
    protected function renderNavBarEntry($url, $text=null, $title=null)
    {
        if(!$this->navBarStarted) {
            echo RI::ni(), "<ul class='navbar-menu'>";
            $this->navBarStarted = true;
        }

        echo RI::ni(1), "<li class='navbar-menu-item clearfix'>";
        echo RI::ni(2), "<a href='{$url}' title='", $title ?: $text ,"'>", $text ?: $url, "</a>";
        echo RI::ni(1), "</li>";
    }


    protected function renderBodyHeader(IRequest $Request) {
        $this->getTheme()->renderSectionStart($Request, 'header');
        $this->renderBodyHeaderContent($Request);
        $this->getTheme()->renderSectionEnd($Request);
    }


    /**
     * Render the view body
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final function renderBody(IRequest $Request) {
        $this->getTheme()->renderBodyStart($Request);

        $this->renderBodyHeader($Request);
        $this->renderBodyContent($Request);
        $this->renderBodyFooter($Request);

        $this->getTheme()->renderBodyEnd($Request);
    }

    /**
     * Render the page center content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final protected function renderBodyContent(IRequest $Request) {
        $Theme = $this->getTheme();
        $Theme->renderSectionStart($Request, 'navbar');
        $this->renderNavBarContent($Request);
        if($this->navBarStarted) {
            echo RI::ni(), "</ul>";
            $this->navBarStarted = false;
        }
        $Theme->renderSectionEnd($Request);

        $Theme->renderSectionStart($Request, 'content');
        $this->renderViewContent($Request);
        $Theme->renderSectionEnd($Request);
    }


    protected function renderBodyFooter(IRequest $Request) {
        $this->getTheme()->renderSectionStart($Request, 'footer');
        $this->renderBodyFooterContent($Request);
        $this->getTheme()->renderSectionEnd($Request);
    }
}
