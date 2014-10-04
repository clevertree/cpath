<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:32 PM
 */
namespace CPath\Render\Text;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Framework\Render\Util\RenderIndents as RI;

class TextSequenceMapRenderer implements ISequenceMapper
{

    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        if(is_array($value))
            $value = new ArraySequence($value);

        if ($value instanceof IKeyMap) {
            $Map = new TextKeyMapRenderer();
            $value->mapKeys($Map);

        } elseif ($value instanceof ISequenceMap) {
            $Renderer = new TextSequenceMapRenderer();
            $value->mapSequence($Renderer);

        } else {
            echo RI::ni(), $value;
        }
    }
}