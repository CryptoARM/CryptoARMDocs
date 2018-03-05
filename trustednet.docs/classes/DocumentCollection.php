<?php

/**
 * Class: DocumentCollection
 * Represents multiple documents in one object
 *
 * @see IEntity
 * @see Collection
 */
class DocumentCollection extends Collection implements IEntity
{

    /**
     * Generates document collection from array
     *
     * @param array $array
     * @see toArray
     * @return \DocumentCollection
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
     * Returns element from collection by id
     * @param integer $i [0..n]
     * @return \Document
     */
    function items($i)
    {
        return parent::items($i);
    }

    /**
     * Converts collection to JSON format
     *
     * @return string JSON
     */
    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * Prepares data for conversion
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

    // TODO: implement
    public function toArray()
    {

    }
}

