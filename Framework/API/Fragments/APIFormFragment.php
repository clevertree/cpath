<?php
namespace CPath\Framework\API\Fragments;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\Api\Field\Interfaces\IField;
use CPath\Framework\Api\Interfaces\FieldUtil;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Handlers\Util\HTMLRenderUtil;
use CPath\Route\IRoutable;
use CPath\Route\RouteUtil;

class APIFormFragment extends AbstractFormFragment{

    private $mAPI;

    /**
     * @param IAPI $API
     * @param ITableTheme $Theme
     */
    public function __construct(IAPI $API, ITableTheme $Theme = null) {
        $this->mAPI = $API;
        parent::__construct($Theme);
    }

    /**
     * Render this API Form
     * @param IRequest $Request the IRequest instance for this render
     * @param \CPath\Framework\Render\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderForm(IRequest $Request, IAttributes $Attr=NULL) {
        $Attr = Attr::get($Attr);

        $API = $this->mAPI;
        $Fields = $API->getFields();
        if($API instanceof IRoutable)
            $Route = $API->loadRoute();
        else
            $Route = $Request->getRoute();

        $RouteUtil = new RouteUtil($Route);

        $method = $RouteUtil->getMethod();
        if($method == 'ANY') // TODO: Is this a hack?
            $method = 'GET';
        $num = 1;
        $path = $RouteUtil->buildPublicURL(true);

        $Util = new HTMLRenderUtil($Request);

        $Attr->addClass('api-form-fragment');
        $Attr->add('enctype', 'multipart/form-data');
        $Attr->add('method', $method);
        $Attr->add('action', $path);

        $Util->formOpen($Attr);

        $Table = new TableThemeUtil($Request, $this->getTheme());
        $Table->renderStart(Describable::get($API)->getDescription(), Attr::get('apiview-table'));
        $Table->renderHeaderStart();
        $Table->renderTD('#',           'table-field-num');
        $Table->renderTD('Req\'d',      'table-field-required');
        $Table->renderTD('Name',        'table-field-name');
        $Table->renderTD('Description', 'table-field-description');
        $Table->renderTD('Test',        'table-field-input');
        if(!$Fields) {
            $Table->renderRowStart();
            $Table->renderTD('&nbsp;',      'table-field-num');
            $Table->renderTD('&nbsp;',      'table-field-required');
            $Table->renderTD('&nbsp;',      'table-field-name');
            $Table->renderTD('&nbsp;',      'table-field-description');
            $Table->renderTD('&nbsp;',      'table-field-input');
        } else foreach($Fields as $name=>$Field) {
            $req = $Field->getFieldFlags() & IField::IS_REQUIRED ? 'yes' : '&nbsp;';
            $desc = Describable::get($Field)->getDescription();;

            $Table->renderRowStart();
            $Table->renderTD($num++,    'table-field-num');
            $Table->renderTD($req,      'table-field-required');
            $Table->renderTD($name,     'table-field-name');
            $Table->renderTD($desc,     'table-field-description');
            $Table->renderDataStart(    'table-field-input');
            if(isset($_GET[$name]))
                $Field->setValue($_GET[$name]);

            $RenderField = new FieldUtil($Field);
            $RenderField->renderHtml($Request);
        }

        $Table->renderFooterStart();
        $Attr = new Attr('table-field-footer-buttons', 'text-align: left');
        $Table->renderDataStart($Attr, 5, 0);
        $this->renderFormButtons($Request);
        $Table->renderEnd();

        $Util->formClose();
    }
}