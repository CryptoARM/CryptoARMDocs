<?php

/**
 * Property class
 */
class Property implements IEntity, ISave
{

    protected $parentId;
    protected $id;
    protected $type;
    protected $value;

    /**
     * @param number $id Parent id
     * @param string $type Property type
     * @param string $value Property value
     */
    function __construct($id = null, $type = null, $value = '')
    {
        $this->parentId = $id;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Creates property from array
     * @param mixed $array
     * @return \Property
     */
    public static function fromArray($array)
    {
        $res = new Property();
        $res->id = $array["ID"];
        $res->parentId = $array["DOCUMENT_ID"];
        $res->type = $array["TYPE"];
        $res->value = $array["VALUE"];
        return $res;
    }

    /**
     * @return type
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @param number $id
     */
    function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return number
     */
    function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param number $docId
     */
    function setParentId($docId)
    {
        $this->parentId = $docId;
    }

    /**
     * Returns property type
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Sets property type
     * @param string $type
     */
    function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns property value
     * @return string
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Sets property value
     * @param string $value
     */
    function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Creates array from property
     * @return type
     */
    public function toArray()
    {
        $res = array(
            "ID" => $this->id,
            "DOCUMENT_ID" => $this->parentId,
            "TYPE" => $this->type,
            "VALUE" => $this->value
        );
        return $res;
    }

    /**
     * Adds/saves property in DB
     */
    public function save()
    {
        TDataBaseDocument::saveProperty($this, DB_TABLE_PROPERTY);
    }

}

