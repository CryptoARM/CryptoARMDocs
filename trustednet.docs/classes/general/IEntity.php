<?php
namespace TrustedNet\Docs;

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

