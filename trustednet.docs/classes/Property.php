<?php

/**
 * Class: Property
 * Represents a property attached to the document
 *
 * @see IEntity
 * @see ISave
 */
class Property implements IEntity, ISave
{

    protected $id;
    protected $documentId;
    protected $type;
    protected $value;

    /**
     * @param integer $id Document id
     * @param string $type Property type
     * @param string $value Property value
     * @return void
     */
    function __construct($id = null, $type = null, $value = '')
    {
        $this->documentId = $id;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Creates property from array
     * @see toArray
     * @param array $array
     * @return object Property
     */
    public static function fromArray($array)
    {
        $res = new Property();
        $res->id = $array["ID"];
        $res->documentId = $array["DOCUMENT_ID"];
        $res->type = $array["TYPE"];
        $res->value = $array["VALUE"];
        return $res;
    }

    /**
     * Returns property id
     * ID field in DB
     * Do not confuse with document id!
     * Property id is just an autoincremented db key
     * @return integer
     */
    function getId()
    {
        return (int)$this->id;
    }

    /**
     * Sets property id
     * ID field in DB
     * @param number $id
     * @return void
     */
    function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns id of the document associated with this property
     * DOCUMENT_ID field in DB
     * @return int
     */
    function getDocumentId()
    {
        return (int)$this->documentId;
    }

    /**
     * Sets id of the document associated with this property
     * @param integer $docId
     * @return void
     */
    function setDocumentId($docId)
    {
        $this->documentId = $docId;
    }

    /**
     * Returns property type
     * TYPE field in DB
     * User-defined value
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Sets property type
     * TYPE field in DB
     * User-defined value
     * @param string $type
     * @return void
     */
    function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns property value
     * VALUE field in DB
     * User-defined value
     * @return string
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Sets property value
     * VALUE field in DB
     * User-defined value
     * @param string $value
     * @return void
     */
    function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Creates array from property
     * @see fromArray
     * @return array
     */
    public function toArray()
    {
        $res = array(
            "ID" => $this->id,
            "DOCUMENT_ID" => $this->documentId,
            "TYPE" => $this->type,
            "VALUE" => $this->value,
        );
        return $res;
    }

    /**
     * Adds/saves property in DB
     * @return void
     */
    public function save()
    {
        TDataBaseDocument::saveProperty($this, DB_TABLE_PROPERTY);
    }

}

