<?php
namespace CPath\Handlers\Fragments;

use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Helpers\Describable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelect;

class ModelTableListFragment implements IHandler{

    private $mQuery, $mTheme;

    /**
     * @param PDOSelect $Query
     * @param ITableTheme $Theme
     */
    public function __construct(PDOSelect $Query, ITableTheme $Theme = null) {
        $this->mQuery = $Query;
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request)
    {
        foreach($this->mQuery as $data) {
            $MF = new ModelTableFragment($data, $this->mTheme);
            $MF->render($Request);
        }
    }
}