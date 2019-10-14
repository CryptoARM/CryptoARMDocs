<?php
namespace Trusted\CryptoARM\Docs;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = array(
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
);
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $coreId . "/";
    if(file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/general/Collection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/general/IEntity.php';

/**
 * Represents multiple documents in one object.
 *
 * @see IEntity
 * @see Collection
 */
class DocumentCollection extends Collection implements IEntity
{

    // TODO: add getIds method

    /**
     * Generates document collection from array.
     *
     * @param array $array
     * @see toArray
     * @return DocumentCollection
     */
    static function fromArray($array)
    {
        $docs = new DocumentCollection();
        foreach ($array as &$item) {
            $docs->add(Document::fromArray($item));
        }
        return $docs;
    }

    /**
     * Returns item from collection by id.
     *
     * @param integer $i [0..n]
     * @return object Document
     */
    function items($i)
    {
        return parent::items($i);
    }

    /**
     * Converts collection to JSON format.
     *
     * @return string JSON
     */
    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * Prepares collection for conversion to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $a = array();
        foreach ($this->items_ as &$item) {
            $a[] = $item->jsonSerialize();
        }
        return $a;
    }

    /**
     * Converts document collection object to array.
     * @return array
     */
    public function toArray()
    {
        $a = array();
        foreach ($this->items_ as &$item) {
            $a[] = $item->toArray();
        }
        return $a;
    }

    public function toIdArray()
    {
        $a = array();
        foreach ($this->items_ as &$item) {
            $a[] = $item->getId();
        }
        return $a;
    }

    public function toIdAndFilenameArray()
    {
        $a = array();
        foreach ($this->items_ as &$item) {
            $a[] = array(
                'filename' => $item->getName(),
                'id' => $item->getId(),
            );
        }
        return $a;
    }

}

