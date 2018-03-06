<?php

/**
 * Class: PropertyCollection
 * Represents multiple properties of the document
 *
 * @see Collection
 */
class PropertyCollection extends Collection
{

    /**
     * Returns property from collection by index [0..n]
     * @param integer $i
     * @return object Property
     */
    function items($i)
    {
        return parent::items($i);
    }

    /**
     * Returns property by type
     * @param string $type
     * @return object Property|null
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

