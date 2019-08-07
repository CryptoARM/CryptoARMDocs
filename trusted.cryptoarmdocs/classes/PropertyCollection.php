<?php
namespace Trusted\CryptoARM\Docs;

/**
 * Represents multiple properties of the document.
 *
 * @see Collection
 */
class PropertyCollection extends Collection {
    /**
     * Returns property from collection by index.
     * @param integer $i [0..n]
     * @return Property
     */
    function items($i) {
        return parent::items($i);
    }

    /**
     * Returns property by type.
     * @param string $type
     * @return Property|null
     */
    function getPropByType($type) {
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

    /**
     * Returns property collection by type.
     * @param string $type
     * @return PropertyCollection|null
     */
    function getPropsByType($type) {
        $list = $this->getList();
        $res = new PropertyCollection();
        foreach ($list as $item) {
            if ($item->getType() == $type) {
                $res->add($item);
            }
        }
        return $res;
    }

    /**
     * Returns property collection by type.
     * @param string $type
     * @return Property|null
     */
    function getPropByTypeAndValue($type, $value) {
        $list = $this->getList();
        $res = null;
        foreach ($list as $item) {
            if ($item->getType() == $type && $item->getValue() == $value) {
                $res = $item;
                break;
            }
        }
        return $res;
    }
}
