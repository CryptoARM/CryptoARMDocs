<?php

/**
 * Property collection class
 */
class PropertyCollection extends Collection
{

    /**
     * Returns property from collection by index [0..n]
     * @param number $i
     * @return \Property
     */
    function items($i)
    {
        return parent::items($i);
    }

    /**
     * Returns property by type
     * @param type $type
     * @return \Property
     */
    function getItemByType($type)
    {
        $list = $this->getList();
        $res = null;
        foreach ($list as $item) {
            if ($item->getType() == $type) {
                $res = $item;
                break;
            }
        }
        return $res;
    }

}

