<?php

interface IEntity
{

    /**
     * Creates object from array
     * @param type $array
     */
    static function fromArray($array);

    /**
     * Creates array from object
     */
    function toArray();
}

interface ISave
{

    /**
     * Saves object in DB
     */
    function save();
}

