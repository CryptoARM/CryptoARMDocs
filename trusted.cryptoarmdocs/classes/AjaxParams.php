<?php
namespace Trusted\CryptoARM\Docs;

/**
 * Describes AJAX request parameters
 *
 * @see IEntity
 */
class AjaxParams implements IEntity
{

    /**
     * JSON string containing additional information
     *
     * @var string JSON
     */
    protected $extra = "";

    /**
     * Creates object from array
     *
     * @see toArray
     * @param array $array
     * @return object AjaxParams
     */
    public static function fromArray($array)
    {
        $res = new AjaxParams();
        foreach ($array as $key => $value) {
            foreach ($res as $okey => &$ovalue) {
                if ($okey == $key) {
                    $ovalue = $value;
                }
            }
        }
        return $res;
    }

    /**
     * Gets an item by its name
     *
     * @param array $array
     * @param string $name
     * @return mixed
     */
    protected static function fromArrayItem($array, $name)
    {
        $res = null;
        if (isset($array[$name])) {
            $res = $array[$name];
        }
        return $res;
    }

    /**
     * Returns extra string
     * @return string JSON
     */
    function getExtra()
    {
        return $this->extra;
    }

    /**
     * Sets extra string
     * @param string $extra JSON
     * @return void
     */
    function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * Creates array from object
     * @see fromArray
     * @return array
     */
    public function toArray()
    {
        $res = array();
        foreach ($this as $key => $value) {
            if ($value) {
                $res[$key] = $value;
            }
        }
        return $res;
    }
}

