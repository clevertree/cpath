<?php
namespace CPath\Handlers\API\Fragments;

use CPath\Config;
use CPath\Handlers\Util\HTMLRenderUtil;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Describable\Describable;
use CPath\Interfaces\IRequest;

class SimpleFormFragment extends AbstractFormFragment{

    /**
     * Render this API Form
     * @param IRequest $Request the IRequest instance for this render
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderForm(IRequest $Request, $class=null, $attr=null) {

        $API = $this->getAPI();
        $Fields = $API->getFields();
        $Route = $API->loadRoute();

        $domainPath = Config::getDomainPath();
        $route = $Route->getPrefix();
        list($method, $path) = explode(' ', $route, 2);
        $path = rtrim($domainPath, '/') . $path;

        $Util = new HTMLRenderUtil($Request);

        $Util->formOpen(
            $Util->getClass('apiview-form', $class),
            $Util->getAttr($attr, array('enctype'=>'multipart/form-data', 'method' => $method, 'action' => $path))
        );

        $Table = new TableThemeUtil($Request, $this->getTheme());
        $Table->renderStart(null, 'apiview-table'); // Describable::get($API)->getDescription()
        $Table->renderHeaderStart();
        $Table->renderTD('Description', 'table-field-description');
        $Table->renderTD('Value',       'table-field-input');

        if(!$Fields) {
            $Table->renderRowStart();
            $Table->renderTD('&nbsp;',      'table-field-description');
            $Table->renderTD('&nbsp;',      'table-field-input');
        } else foreach($Fields as $name=>$Field) {
            $desc = Describable::get($Field)->getDescription();;

            $Table->renderRowStart();
            $Table->renderTD($desc,     'table-field-description');
            $Table->renderDataStart(    'table-field-input');
            if(isset($_GET[$name]))
                $Field->setValue($_GET[$name]);
            $Field->render($Request);
        }

        $Table->renderFooterStart();
        $Table->renderDataStart('table-field-footer-buttons', 5, 0, "style='text-align: left'");
        $this->renderFormButtons($Request);
        $Table->renderEnd();

        $Util->formClose();
    }
}
