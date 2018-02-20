<?php

/**
 * Collection class
 */
class Collection
{

    protected $items_ = array();

    /**
     * Returns array of elements
     * @return mixed
     */
    function getList()
    {
        return $this->items_;
    }

    /**
     * Add element to collection
     * @param type $item
     */
    public function add($item)
    {
        if (isset($item)) {
            $this->items_[] = $item;
        }
    }

    /**
     * Returns element fom collection by index [0..n]
     * @param type $i
     * @return type
     */
    public function items($i)
    {
        return $this->items_[$i];
    }

    /**
     * Returns number of elements in collection
     * @return number
     */
    public function count()
    {
        return count($this->items_);
    }

}

