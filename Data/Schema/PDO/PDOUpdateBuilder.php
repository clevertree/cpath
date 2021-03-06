<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 7:33 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Request\IRequest;

class PDOUpdateBuilder extends PDOWhereBuilder
{
	private $mUpdateSQL = null;

	public function update($fieldName, $fieldValue=null, $set = '=?') {
		if (is_array($fieldName)) {
			foreach ($fieldName as $k => $v)
				$this->update(is_int($k) ? $v : $k, $v);
			return $this;
		}
//
//        if(strpos($set, '?') !== false)
//            $set = str_replace('?', ':' . $fieldName, $set);

        $this->bindValue($fieldValue); // , ':' . $fieldName);
        if(strpos($set, '%') !== false)
            $fieldName = sprintf($set, $fieldName);
        else
            $fieldName .= $set;

		if ($this->mUpdateSQL) {
			$this->mUpdateSQL .= ', ' . $fieldName;
		} else {
			$this->mUpdateSQL = "\n\tSET " . $fieldName;
		}

		return $this;
	}

	protected function getSQL() {
		if(!$this->mTableSQL)
			throw new \InvalidArgumentException("Table not set");
		if(!$this->mUpdateSQL)
			throw new \InvalidArgumentException("Update not set");
		if(!$this->mWhereSQL)
			throw new \InvalidArgumentException("Select not set");

		if($this->mFormat)
			return sprintf($this->mFormat, $this->mTableSQL, $this->mUpdateSQL, $this->mWhereSQL, $this->mLimitSQL);

		return
			("UPDATE " . $this->mTableSQL)
			. ($this->mUpdateSQL)
			. ($this->mWhereSQL)
			. ($this->mLimitSQL);
	}

	public function execUpdate(IRequest $Request=null, $throwExceptionIfUnmodified=true) {
		$this->execute($Request);
		$c = $this->rowCount();
		if($c === 0 && $throwExceptionIfUnmodified)
			throw new PDOUnmodifiedException($this, "UPDATE Query failed to update any row(s): " . $this->getSQL());
		return $c;
	}
}