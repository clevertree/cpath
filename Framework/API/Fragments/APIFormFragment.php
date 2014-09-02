<?php
namespace CPath\Framework\API\Fragments;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Field\Util\FieldUtil;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Theme\Interfaces\ITableTheme;
use CPath\Framework\Render\Theme\Util\TableThemeUtil;
use CPath\Framework\Render\Util\HTMLRenderUtil;

class APIFormFragment extends AbstractFormFragment{

    private $mAPI;

    /**
     * @param IAPI $API
     * @param \CPath\Framework\Render\Theme\Interfaces\ITableTheme $Theme
     */
    public function __construct(IAPI $API, ITableTheme $Theme = null) {
        $this->mAPI = $API;
        parent::__construct($Theme);
    }

    protected function getAPI() { return $this->mAPI; }

    /**
     * Render this API Form
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderForm(IRequest $Request, IAttributes $Attr=NULL) {
        $Attr = Attr::get($Attr);

        $API = $this->mAPI;
        $Fields = $API->getFields($Request);

        $method = $Request->getMethod();
        $path = $Request->getPath();
        if($method == 'ANY') // TODO: Is this a hack?
            $method = 'GET';
        $num = 1;
        $absPath = rtrim(Config::getDomainPath(), '/') . $path;

        $Util = new HTMLRenderUtil($Request);

        $Attr->addClass('api-form-fragment');
        $Attr->add('enctype', 'multipart/form-data');
        $Attr->add('method', $method);
        $Attr->add('action', $absPath);

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
            $RenderField->renderHTML($Request);
        }

        $Table->renderFooterStart();
        $Attr = new Attr('table-field-footer-buttons', 'text-align: left');
        $Table->renderDataStart($Attr, 5, 0);
        $this->renderFormButtons($Request);
        $Table->renderEnd();

        $Util->formClose();
    }
}
