<?php
namespace TrustedNet\Docs;

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

