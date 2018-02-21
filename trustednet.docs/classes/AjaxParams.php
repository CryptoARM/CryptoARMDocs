<?php

class AjaxParams implements IEntity
{

    protected $logo = null;
    protected $extra = "";
    protected $css = null;

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

    protected static function fromArrayItem($array, $name)
    {
        $res = null;
        if (isset($array[$name])) {
            $res = $array[$name];
        }
        return $res;
    }

    function getExtra()
    {
        return $this->extra;
    }

    function setExtra($extra)
    {
        $this->extra = $extra;
    }

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

