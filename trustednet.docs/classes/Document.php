<?php


/**
 * Document class
 */
class Document implements IEntity, ISave
{

    protected $id = null;
    protected $created;
    protected $name = '';
    protected $path = '';
    protected $type = DOC_TYPE_FILE;
    protected $signers = '';
    protected $properties = null;
    protected $parent = null;
    protected $parentId = null;
    protected $child = null;
    protected $childId = null;
    protected $status = null;

    function __construct()
    {
        $this->id = null;
    }

    /**
     * Returns new document object from array
     * @param type $array
     * @return \Document
     */
    static function fromArray($array)
    {
        $doc = null;
        if ($array) {
            $doc = new Document();
            $doc->setId($array["ID"]);
            $doc->setCreated($array["TIMESTAMP_X"]);
            $doc->setName($array["NAME"]);
            $doc->setPath($array["PATH"]);
            $doc->setSigners($array["SIGNERS"]);
            $doc->setType($array["TYPE"]);
            $doc->setParentId($array["PARENT_ID"]);
            $doc->setChildId($array["CHILD_ID"]);
        }
        return $doc;
    }

    /**
     * Returns document status
     * @return \DocumentStatus
     */
    function getStatus()
    {
        if (!$this->status) {
            $this->status = TDataBaseDocument::getStatus($this);
        }
        return $this->status;
    }

    /**
     * Sets document status
     * @param \DocumentStatus $status
     */
    function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return type
     */
    function getChildId()
    {
        return $this->childId;
    }

    /**
     * @param type $childId
     */
    function setChildId($childId)
    {
        $this->childId = $childId;
        $this->child = null;
    }

    /**
     * Returns last document in the chain of signed documents
     * @return \Document
     */
    function getLastDocument()
    {
        $res = $this;
        if ($res->hasChild()) {
            $child = $this->getChild();
            $res = $child->getLastDocument();
        }
        return $res;
    }

    /**
     * Checks if the document has child
     * @return boolean
     */
    function hasChild()
    {
        $res = false;
        $child = $this->getChild();
        if ($child) {
            $res = true;
        }
        return $res;
    }

    /**
     * Returns child document
     * @return \Document
     */
    function getChild()
    {
        if (!$this->child && $this->childId) {
            $this->child = TDataBaseDocument::getDocumentById($this->childId);
        }
        return $this->child;
    }

    /**
     * Sets document child
     * @param \Document $doc
     */
    function setChild($doc)
    {
        $this->child = $doc;
        $this->childId = $doc->id;
    }

    /**
     * Returns parent document id
     * @return number
     */
    function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Sets parent document id
     * @param number $parentId
     */
    function setParentId($parentId)
    {
        $this->parentId = $parentId;
        $this->parent = null;
    }

    /**
     * Returns parent document
     * @return \Document
     */
    function getParent()
    {
        if (!$this->parent && $this->parentId) {
            $this->parent = TDataBaseDocument::getDocumentById($this->parentId);
        }
        return $this->parent;
    }

    /**
     * Sets parent document
     * @param \Document $parent
     */
    function setParent($parent)
    {
        $this->parent = $parent;
        $this->parentId = $parent->id;
    }

    /**
     * Returns document creation time.
     * TIMESTAMP_X field in DB
     * @return type Time
     */
    function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets document creation time.
     * TIMESTAMP_X field in DB
     * @param type $time
     */
    function setCreated($time)
    {
        $this->created = $time;
    }

    /**
     * Returns document sign info as array
     * @return Array
     */
    function getSignersToArray()
    {
        $signers = $this->signers;
        $signers = explode(",{", $signers);
        foreach ($signers as $key => $signer) {
            $arr = array("{", "}", "[", "]");
            $arrTo = array("", "", "", "");
            $signer = str_replace($arr, $arrTo, $signer);
            $signer = explode(",", $signer);
            foreach ($signer as $keyN => $value) {
                $value = str_replace('"', '', $value);
                $value = explode(":", $value);
                $prop = $value[0];
                $value = $value[1];
                $signer[$prop] = $value;
                unset($signer[$keyN]);
            }
            $signers[$key] = $signer;
            if ($signer["subjectName"]) {
                $subject = explode("/", ($signer["subjectName"]));
                foreach ($subject as $keyS => $value) {
                    $value = explode("=", $value);
                    $prop = $value[0];
                    $value = $value[1];
                    $subject[$prop] = $value;
                    unset($subject[$keyS]);
                }
                $signer["subjectName"] = $subject;
            }
            if ($signer["issuerName"]) {
                $subject = explode("/", ($signer["issuerName"]));
                foreach ($subject as $keyS => $value) {
                    $value = explode("=", $value);
                    $prop = $value[0];
                    $value = $value[1];
                    $subject[$prop] = $value;
                    unset($subject[$keyS]);
                }
                $signer["issuerName"] = $subject;
            }
            $signers[$key] = $signer;
        }
        return $signers;
    }

    function __destruct()
    {

    }

    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

    public function jsonSerialize()
    {
        $a = array(
            "name" => $this->name,
            "url" => $this->getUrl(),
            "id" => $this->getId(),
        );
        return $a;
    }

    public function getHtmlPath()
    {
        return str_replace($_SERVER['DOCUMENT_ROOT'], "", $this->path);
    }

    public function getUrl()
    {
        return TN_DOCS_AJAX_CONTROLLER . "?command=content&id=" . $this->getId();
    }

    /**
     * Returns id
     * ID field in DB
     * @return type
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Sets id
     * ID field in DB
     */
    function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Removes document and all its parents
     * @return boolean
     */
    public function remove()
    {
        TDataBaseDocument::removeDocumentRecursively($this);
    }

    /**
     * Saves changed document or creates new record if id is null
     * @return boolean
     */
    public function save()
    {
        TDataBaseDocument::saveDocument($this);
        $list = $this->getProperties()->getList();
        foreach ($list as &$prop) {
            if (!$prop->getParentId()) {
                $prop->setParentId($this->id);
            }
            $prop->save();
        }
    }

    /**
     * Return collection of properties of document
     * @param number $i
     * @return PropertyCollection | Property
     */
    function getProperties($i = null)
    {
        $props = &$this->properties;
        if (!$props) {
            if ($this->getId()) {
                $props = TDataBaseDocument::getPropertiesByParentId(DB_TABLE_PROPERTY, $this->getId());
            } else {
                $props = new PropertyCollection();
            }
        }
        $res = $props;
        if (!is_null($i)) {
            $res = $props->items($i);
        }
        return $res;
    }

    /**
     *
     * @return \Document
     */
    public function copy()
    {
        $new = new Document();
        $new->setName($this->getName());
        $new->setPath($this->getPath());
        $new->setSigners($this->getSigners());
        $new->setType($this->getType());
        $list = $this->getProperties()->getList();
        foreach ($list as &$prop) {
            $newProp = new Property(null, $prop->getType(), $prop->getValue());
            $new->getProperties()->add($newProp);
        }
        return $new;
    }

    /**
     * Returns document name
     * NAME field in DB
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * Sets document name
     * NAME field in DB
     * @param string $name
     */
    function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns path to the document
     * PATH field in DB
     * @return string
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * Sets path to the document
     * PATH field in DB
     * @param string $path
     */
    function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Returns signers of the document
     * SIGNERS field in DB
     * @return string JSON
     */
    function getSigners()
    {
        return $this->signers;
    }

    /**
     * Sets signers of the document
     * SIGNERS field in DB
     * @param string $signers JSON
     */
    function setSigners($signers)
    {
        $this->signers = $signers;
    }

    /**
     * Returns document type
     * TYPE field
     * @return number
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Sets document type
     * TYPE field
     * @param number $type
     */
    function setType($type)
    {
        $this->type = $type;
    }

    public function toArray()
    {

    }

    /**
     * Returns true if associated file exists on disk
     */
    function checkFile()
    {
        $file = $_SERVER["DOCUMENT_ROOT"] . urldecode($this->getPath());
        return file_exists($file);
    }
}

