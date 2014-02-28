<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 6/25/13
 * Time: 12:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\Data\Collection;

use CPath\Framework\Data\Collection\Predicate\IPredicate;

interface ICollection extends \IteratorAggregate, \Countable {

    /**
     * Filter the item collection by an IPredicate
     * @param IPredicate $Where
     * @return ICollection
     */
    function where(IPredicate $Where);
}