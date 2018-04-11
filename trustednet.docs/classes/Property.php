<?php
namespace TrustedNet\Docs;

/**
 * Represents a property attached to the document.
 *
 * Property has custom user-set type and value.
 *
 * @see IEntity
 * @see ISave
 */
class Property implements IEntity, ISave
{

    /**
     * Property ID. ID field in DB. Not the document ID!
     * @var integer
     */
    protected $id;

    /**
     * ID of the associated document. DOCUMENT_ID field in DB.
     * @var integer
     */
    protected $documentId;

    /**
     * User-set property type. TYPE field in DB.
     * @var string max 50 characters
     */
    protected $type;

    /**
     * User-set property value. VALUE field in DB.
     * @var string max 255 characters
     */
    protected $value;

    /**
     * @param integer $id Document ID
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
     * Returns property id.
     * @return integer
     */
    function getId()
    {
        return (int)$this->id;
    }

    /**
     * Sets property id.
     * @param integer $id
     * @return void
     */
    function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns ID of the document associated with this property.
     * @return integer
     */
    function getDocumentId()
    {
        return (int)$this->documentId;
    }

    /**
     * Sets id of the document associated with this property.
     * @param integer $docId
     * @return void
     */
    function setDocumentId($docId)
    {
        $this->documentId = $docId;
    }

    /**
     * Returns property type.
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Sets property type.
     * @param string $type
     * @return void
     */
    function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns property value.
     * @return string
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Sets property value.
     * @param string $value
     * @return void
     */
    function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Creates property object from array.
     * @see toArray
     * @param array $array
     * @return Property
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
     * Creates array from property object.
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
     * Adds/saves property in DB.
     * @return void
     */
    public function save()
    {
        Database::saveProperty($this, DB_TABLE_PROPERTY);
    }

}

