<?php

/**
 * Represent multiple instances of object of the same class
 */
class Collection
{

    /**
     * Holds collection items
     *
     * @var array
     */
    protected $items_ = array();

    /**
     * Returns array with collection items
     * @return array
     */
    function getList()
    {
        return $this->items_;
    }

    /**
     * Add item to collection
     * @param object $item
     */
    public function add($item)
    {
        if (isset($item)) {
            $this->items_[] = $item;
        }
    }

    /**
     * Returns item from collection by index
     * @param integer $i [0..n]
     * @return object
     */
    public function items($i)
    {
        return $this->items_[$i];
    }

    /**
     * Returns number of items in collection
     * @return integer
     */
    public function count()
    {
        return count($this->items_);
    }

}

