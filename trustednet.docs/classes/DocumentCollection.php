<?php

class DocumentCollection extends Collection implements IEntity
{

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
     * @param number $i Number [0..n]
     * @return DocumentItem
     */
    function items($i)
    {
        return parent::items($i);
    }

    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

    public function jsonSerialize()
    {
        $a = array();
        foreach ($this->items_ as &$item) {
            $a[] = $item->jsonSerialize();
        }
        return $a;
    }

    public function toArray()
    {

    }
}

