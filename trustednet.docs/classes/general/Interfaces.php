<?php

/**
 * General object representation
 */
interface IEntity
{

    /**
     * Creates object from array
     * @param array $array
     */
    static function fromArray($array);

    /**
     * Creates array from object
     */
    function toArray();
}

/**
 * For objects that are stored in DB
 */
interface ISave
{

    /**
     * Saves object in DB
     */
    function save();
}

