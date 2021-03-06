<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/13/2015
 * Time: 11:44 AM
 */
namespace CPath\Render\HTML\Element\Table;

use CPath\Data\Map\CallbackKeyMapper;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Data\Schema\PDO\PDOSelectBuilder;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\Attributes;
use CPath\Render\HTML\Attribute\ClassAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute\StyleAttributes;
use CPath\Render\HTML\Element\Form\HTMLInputField;
use CPath\Render\HTML\Element\Form\HTMLSelectField;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLConfig;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\Text\IRenderText;
use CPath\Request\IRequest;
use CPath\Request\Validation\IRequestValidation;
use CPath\Response\IResponse;

class HTMLPDOQueryTable implements IRenderHTML, IRequestValidation, ISequenceMap, IResponse, IHTMLSupportHeaders
{
	protected $Query;
	private $columns;
	private $searchColumn = array();
	private $sortColumn = array();
	private $rowCount;

	public function __construct(PDOSelectBuilder $Query) {
		$this->Query = $Query;
	}

    public function getQuery() {
        return $this->Query;
    }

	public function addColumn($columnName, $fieldName=null) {
		$this->columns[$fieldName ?: $columnName] = $columnName;
		return $this;
	}

	public function addSearchColumn($columnName, $fieldName=null) {
		$this->searchColumn[$fieldName ?: $columnName] = $columnName;
		return $this;
	}

	public function addSortColumn($columnName, $fieldName=null) {
		$this->sortColumn[$fieldName ?: $columnName] = $columnName;
		return $this;
	}

    public function setPagination() {

        $page = 0;
        $total = null;
        $row_count = 5;
        if(isset($Request[self::PARAM_PAGE]))
            $page = $Request[self::PARAM_PAGE];
        $offset = $page * $row_count;

        $Pagination = new HTMLPagination($row_count, $page, $total);
    }

	/**
	 * Validate the request
	 * @param IRequest $Request
	 * @throw Exception if validation failed
	 * @return array|void optionally returns an associative array of modified field names and values
	 */
	function validateRequest(IRequest $Request) {
		foreach($this->searchColumn as $fieldName => $columnName) {
			if(!empty($Request['search-' . $fieldName])) {
				$value = $Request['search-' . $fieldName];
				if(strpos($value, '%') === false)
					$value .= '%';
				$this->Query->where($columnName, $value, ' LIKE ?');
			}
			if(!empty($Request['sort-' . $fieldName])) {
				$order = $Request['sort-' . $fieldName];
				$this->Query->orderBy($columnName, $order);
			}
		}
		foreach($this->sortColumn as $fieldName => $columnName) {
			if(!empty($Request['sort-' . $fieldName])) {
				$order = $Request['sort-' . $fieldName];
				$this->Query->orderBy($columnName, $order);
			}
		}
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {

		$row = $this->Query->fetch();

		$columns = $this->columns;
		if (!$columns) {
			$this->Query->rewind();
			if ($row instanceof IKeyMap) {
				$row->mapKeys(
					new CallbackKeyMapper(
						function ($key, $value) use (&$columns) {
							$columns[$key] = $key;
						}
					)
				);
			} else {
				foreach ((array)$row as $key => $value)
					$columns[$key] = $key;
			}
			$this->columns = $columns;
		}

		echo RI::ni(), "<table";
        if($Attr)
            $Attr->renderHTMLAttributes($Request);
        echo ">";
		echo RI::ai(1);

		echo RI::ni(), "<thead>";
		echo RI::ai(1);

		echo RI::ni(), "<tr>";
		echo RI::ai(1);

		foreach ((array)$columns as $fieldName => $columnName) {
			echo RI::ni(), "<th>";
			$title = ucwords(preg_replace('/[_-]/', ' ', $fieldName));

			$ASC = 'DESC';
			if(isset($Request['sort-' . $fieldName])
				&& $Request['sort-' . $fieldName] === $ASC)
				$ASC = 'ASC';

			if(isset($this->searchColumn[$fieldName])) {
				echo "<a href='?sort-", $fieldName, "={$ASC}'>{$title}</a>";

			} else if(isset($this->sortColumn[$fieldName])) {
				echo "<a href='?sort-", $fieldName, "={$ASC}'>{$title}</a>";

			} else {
				echo $title;

			}
			echo RI::ni(), "</th>";
		}

		echo RI::ai(-1);
		echo RI::ni(), "</tr>";

		echo RI::ai(-1);
		echo RI::ni(), "</thead>";

		echo RI::ni(), "<tbody>";
		echo RI::ai(1);

		while ($row) {
			echo RI::ai(1);

			echo RI::ni(), "<tr>";
			echo RI::ai(1);

			if ($row instanceof IKeyMap) {
				$array = array();
				$row->mapKeys(
					new CallbackKeyMapper(
						function($key, $value, $_arg=null) use ($Request, &$array) {
                            if($_arg) {
                                $array[$key] = func_get_args();
                            } else {
                                if(isset($array[$key]))
                                    $array[$key] .= ', ' . $value;
                                else
                                    $array[$key] = $value;
                            }
						}
					)
				);
				$row = $array;
			}

			if( is_array($row)) {
				foreach((array)$columns as $fieldName => $columnName) {
					echo RI::ni(), "<td>";

					if(isset($row[$columnName])) {
						$value = $row[$columnName];
						$arg=null;
						if(is_array($value))
							list(, $value, $arg) = $value;
						if($Request && $value instanceof IRenderHTML) {
							$value->renderHTML($Request, null, $this);

						} else if($Request && $value instanceof IRenderText) {
							$value->renderText($Request);

						} else {
							HTMLConfig::renderNamedValue($fieldName, $value, $arg);
						}
					}

					echo "</td>";
				}
			} else {
				echo RI::ni(), "<td>", HTMLConfig::renderValue($row), "</td>";
			}
			echo RI::ai(-1);
			echo RI::ni(), "</tr>";

			echo RI::ai(-1);
			$this->rowCount++;

			$row = $this->Query->fetch();
		}

		echo RI::ai(-1);
		echo RI::ni(), "</tbody>";

		if($this->searchColumn || $this->sortColumn) {
			echo RI::ni(), "<tfoot>";
			echo RI::ai(1);

			echo RI::ni(), "<tr>";
			echo RI::ai(1);

			foreach ((array)$columns as $fieldName => $columnName) {
				echo RI::ni(), "<td>";

				if(isset($this->searchColumn[$fieldName])) {
					$searchColumnName = $this->searchColumn[$fieldName];
					$Input = new HTMLInputField('search-' . $fieldName,
						new Attributes('placeholder', 'Search ' . $searchColumnName),
//						new Attributes('size', 16),
                        new StyleAttributes('width', '100%'),
						new ClassAttributes('input search transparent')
					);
					$Input->setInputValueFromRequest($Request);
					$Input->renderHTML($Request);

				} else if (isset($this->sortColumn[$fieldName])) {
					$sortColumnName = $this->sortColumn[$fieldName];
					$Select = new HTMLSelectField('sort-' . $fieldName, 'input search transparent', array(
						'Sort by ' . $sortColumnName . '...' => "",
						'Ascending' => "ASC",
						'Descending' => "DESC",
					));
					$Select->setInputValueFromRequest($Request);
					$Select->renderHTML($Request);
				}

				echo "</td>";
			}

			echo RI::ai(-1);
			echo RI::ni(), "</tr>";

			echo RI::ai(-1);
			echo RI::ni(), "</tfoot>";
		}

		echo RI::ai(-1);
		echo RI::ni(), "</table>";


		//echo $this->Query->prepare($Request)->queryString, "<br/>";
	}

    /**
     * Map sequential data to the map
     * @param ISequenceMapper $Map
     */
    function mapSequence(ISequenceMapper $Map) {
        $this->Query->rewind();
        $stmt = $this->Query->prepare();
        while($row = $stmt->fetch()) {
            $Map->mapNext($row);
        }
    }

    /**
     * Get the request status code
     * @return int
     */
    function getCode() {
        return 200;
    }

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage() {
        return __CLASS__;
    }

    /**
     * Write all support headers used by this renderer
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer inst to use
     * @return void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeStyleSheet(__DIR__ . '/assets/table.css');
    }
}