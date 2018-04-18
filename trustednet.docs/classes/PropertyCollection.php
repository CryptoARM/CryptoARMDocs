<?php
namespace TrustedNet\Docs;

/**
 * Represents multiple properties of the document.
 *
 * @see Collection
 */
class PropertyCollection extends Collection
{
    // TODO: simplify adding properties to the collection
    // add "add" method

    /**
     * Returns property from collection by index.
     * @param integer $i [0..n]
     * @return Property
     */
    function items($i)
    {
        return parent::items($i);
    }

    /**
     * Returns property by type.
     * @param string $type
     * @return Property|null
     */
    function getPropByType($type)
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

