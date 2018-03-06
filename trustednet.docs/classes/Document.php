<?php


/**
 * Class: Document
 * Documents are stored in DB,
 * where each row represents single document.
 *
 * Documents can have a single child document,
 * and a single parent document.
 *
 * Chain of documents therefore is a doubly-linked list.
 *
 * @see IEntity
 * @see ISave
 */
class Document implements IEntity, ISave
{

    protected $id = null;
    protected $created;
    protected $name = '';
    protected $path = '';
    protected $type = DOC_TYPE_FILE;
    protected $status = DOC_STATUS_NONE;
    protected $signers = '';
    protected $properties = null;
    protected $parentId = null;
    protected $childId = null;

    function __construct()
    {
        $this->id = null;
    }

    /**
     * Returns new document object from array
     * @param array $array
     * @return object Document
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
            $doc->setStatus($array["STATUS"]);
            $doc->setParentId($array["PARENT_ID"]);
            $doc->setChildId($array["CHILD_ID"]);
        }
        return $doc;
    }

    /**
     * Returns document status
     * @return integer
     */
    function getStatus()
    {
        return (int)$this->status;
    }

    /**
     * Sets document status
     * @param integer $status
     * @return void
     */
    function setStatus($status)
    {
        $this->status = (int)$status;
    }

    /**
     * Returns id of the child of the document
     * @return integer|null
     */
    function getChildId()
    {
        if (is_null($this->childId)) {
            return null;
        } else {
            return (int)$this->childId;
        }
    }

    /**
     * Sets id of the child of the document
     * @param integer|null $childId
     * @return void
     */
    function setChildId($childId)
    {
        if (is_null($childId)) {
            $this->childId = null;
        } else {
            $this->childId = (int)$childId;
        }
    }

    /**
     * Returns last document in the chain of signed documents
     * @return object Document
     */
    function getLastDocument()
    {
        if ($this->hasChild()) {
            $child = $this->getChild();
            return $child->getLastDocument();
        } else {
            return $this;
        }
        // TODO: delete after testing
        //$res = $this;
        //if ($res->hasChild()) {
        //    $child = $this->getChild();
        //    $res = $child->getLastDocument();
        //}
        //return $res;
    }

    /**
     * Checks if the document has child
     * @return boolean
     */
    function hasChild()
    {
        if ($this->getChildId()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns child document
     * @return object Document|null
     */
    function getChild()
    {
        if ($this->childId) {
            return TDataBaseDocument::getDocumentById($this->childId);
        } else {
            return null;
        }
    }

    /**
     * Sets child id by passed document
     * @param object Document $doc
     * @return void
     */
    function setChild($doc)
    {
        $this->childId = $doc->id;
    }

    /**
     * Returns parent document id
     * @return integer|null
     */
    function getParentId()
    {
        if (is_null($this->parentId)) {
            return null;
        } else {
            return (int)$this->parentId;
        }
    }

    /**
     * Sets parent document id
     * @param integer|null $parentId
     * @return void
     */
    function setParentId($parentId)
    {
        if (is_null($parentId)) {
            $this->parentId = null;
        } else {
            $this->parentId = (int)$parentId;
        }
    }

    /**
     * Returns parent document
     * @return object Document|null
     */
    function getParent()
    {
        if ($this->parentId) {
            return TDataBaseDocument::getDocumentById($this->parentId);
        } else {
            return null;
        }
    }

    /**
     * Sets parent id by passed document
     * @param object Document $parent
     * @return void
     */
    function setParent($parent)
    {
        $this->parentId = $parent->id;
    }

    /**
     * Returns document creation time.
     * TIMESTAMP_X field in DB
     * @return string "YYYY-MM-DD hh:mm:ss"
     */
    function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets document creation time.
     * TIMESTAMP_X field in DB
     * @param string $time
     * @return void
     */
    function setCreated($time)
    {
        $this->created = $time;
    }

    /**
     * Returns document sign info as array
     * @return array
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

    /**
     * Returns document info in JSON format
     * Used to send document to signing app
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * Prepares document info for converting to JSON
     * @return array
     */
    public function jsonSerialize()
    {
        $a = array(
            "name" => $this->getName(),
            "url" => $this->getUrl(),
            "id" => $this->getId(),
        );
        return $a;
    }

    // TODO: remove?
    public function getHtmlPath()
    {
        return str_replace($_SERVER['DOCUMENT_ROOT'], "", $this->path);
    }

    /**
     * Returns url for downloading document file through controller
     * @return string
     */
    public function getUrl()
    {
        return TN_DOCS_AJAX_CONTROLLER . "?command=content&id=" . $this->getId();
    }

    /**
     * Returns document id
     * ID field in DB
     * @return integer
     */
    function getId()
    {
        return (int)$this->id;
    }

    /**
     * Sets id
     * ID field in DB
     * @param integer $id
     * @return void
     */
    function setId($id)
    {
        $this->id = (int)$id;
    }

    /**
     * Removes document and all its parents
     * @return void
     */
    public function remove()
    {
        TDataBaseDocument::removeDocumentRecursively($this);
    }

    /**
     * Saves changed document in DB or creates new record if id is null
     * @return void
     */
    public function save()
    {
        TDataBaseDocument::saveDocument($this);
        $list = $this->getProperties()->getList();
        foreach ($list as &$prop) {
            if (!$prop->getDocumentId()) {
                $prop->setDocumentId($this->id);
            }
            $prop->save();
        }
    }

    /**
     * Return collection of properties of document
     * @return object PropertyCollection
     */
    function getProperties()
    {
        $props = &$this->properties;
        if (!$props) {
            if ($this->getId()) {
                $props = TDataBaseDocument::getPropertiesByDocumentId(DB_TABLE_PROPERTY, $this->getId());
            } else {
                $props = new PropertyCollection();
            }
        }
        return $props;
    }

    /**
     * Creates a copy of the document object
     * New document has id = null
     * @return object Document
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
     * @return void
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
     * @return void
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
     * @return void
     */
    function setSigners($signers)
    {
        $this->signers = $signers;
    }

    /**
     * Returns document type
     * TYPE field in DB
     * @return integer
     */
    function getType()
    {
        return (int)$this->type;
    }

    /**
     * Sets document type
     * TYPE field in DB
     * @param integer $type
     * @return void
     */
    function setType($type)
    {
        $this->type = (int)$type;
    }

    //TODO: implement
    public function toArray()
    {

    }

    /**
     * Returns true if associated file exists on disk
     * @return boolean
     */
    function checkFile()
    {
        $file = $_SERVER["DOCUMENT_ROOT"] . urldecode($this->getPath());
        return file_exists($file);
    }
}

