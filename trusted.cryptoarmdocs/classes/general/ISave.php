<?php
namespace Trusted\CryptoARM\Docs;

/**
 * For objects that are stored in DB
 */
interface ISave {
    /**
     * Saves object in DB
     */
    function save();
}
