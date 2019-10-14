<?php
namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Localization\Loc;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = array(
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
);
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $coreId . "/";
    if(file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/general/IEntity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/general/ISave.php';

/**
 * Represents a single document
 *
 * Documents are stored in DB, where each row represents single document.
 * Documents can have a single child document, and a single parent document.
 * Chain of documents therefore is a doubly-linked list.
 *
 * @see IEntity
 * @see ISave
 */
class Document implements IEntity, ISave
{

    /**
     * Document id. ID field in DB.
     * @var integer
     */
    protected $id = null;

    /**
     * Document name. NAME field in DB.
     * @var string
     */
    protected $name = "";

    /**
     * Path to document file relative to the site root. PATH field in DB.
     * @var string
     */
    protected $path = "";

    /**
     * Document type. TYPE field in DB.
     * @see config.php
     * @var integer
     */
    protected $type = DOC_TYPE_FILE;

    /**
     * Document status. STATUS field in DB.
     * @see config.php
     * @var integer
     */
    protected $status = DOC_STATUS_NONE;

    /**
     * ID of the parent of the document. PARENT_ID field in DB.
     * @var integer
     */
    protected $parentId = null;

    /**
     * ID of the child of the document. CHILD_ID FIELD in DB.
     * @var integer
     */
    protected $childId = null;

    /**
     * Document file hash. HASH field in DB.
     * @var string
     */
    protected $hash = null;

    /**
     * Information about document signatures. SIGNATURES field in DB.
     * @var string JSON
     */
    protected $signatures = "";

    /**
     * Comma-separated list of bitrix users who signed the doc
     * @var string
     */
    protected $signers = "";

    /**
     * Id of the bitrix user that blocked the doc
     * @var integer
     */
    protected $blockBy = null;

    /**
     * Token for accessing the blocked document
     * @var string
     */
    protected $blockToken = null;

    /**
     * Timestamp of the block operation
     * @var string
     */
    protected $blockTime = "";

    /**
     * Document creation time. TIMESTAMP_X field in DB.
     * @var string
     */
    protected $created = "";

    function __construct()
    {
        $this->id = null;
    }

    function __destruct()
    {

    }

    /**
     * Returns new document object from array.
     * @param array $array
     * @return Document
     */
    static function fromArray($array)
    {
        $doc = null;
        if ($array) {
            $doc = new Document();
            $doc->setId($array["ID"]);
            $doc->setName($array["NAME"]);
            $doc->setPath($array["PATH"]);
            $doc->setType($array["TYPE"]);
            $doc->setStatus($array["STATUS"]);
            $doc->setParentId($array["PARENT_ID"]);
            $doc->setChildId($array["CHILD_ID"]);
            $doc->setHash($array["HASH"]);
            $doc->setSignatures($array["SIGNATURES"]);
            $doc->setSigners($array["SIGNERS"]);
            $doc->setBlockBy($array["BLOCK_BY"]);
            $doc->setBlockToken($array["BLOCK_TOKEN"]);
            $doc->setBlockTime($array["BLOCK_TIME"]);
            $doc->setCreated($array["TIMESTAMP_X"]);
        }
        return $doc;
    }

    /**
     * Converts document object to associative array.
     * @return array
     */
    public function toArray()
    {
        $a = array(
            "id"       => $this->getId(),
            "name"     => $this->getName(),
            "path"     => $this->getPath(),
            "type"     => $this->getType(),
            "status"   => $this->getStatus(),
            "signatures"  => $this->getSignatures(),
            "parentId" => $this->getParentId(),
            "childId"  => $this->getChildId(),
            "hash"  => $this->getHash(),
        );
        return $a;
    }

    /**
     * Returns document id.
     * @return integer|null
     */
    function getId()
    {
        return (int)$this->id;
    }

    /**
     * Sets document id.
     * @param integer|null $id
     * @return void
     */
    function setId($id)
    {
        $this->id = (int)$id;
    }

    /**
     * Returns document name.
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * Sets document name.
     * @param string $name
     * @return void
     */
    function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns path to the document.
     * @return string
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * Sets path to the document.
     * @param string $path
     * @return void
     */
    function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Returns document type.
     * @return integer
     */
    function getType()
    {
        return (int)$this->type;
    }

    /**
     * Sets document type.
     * @param integer $type
     * @return void
     */
    function setType($type)
    {
        $this->type = (int)$type;
    }

    /**
     * Returns document status.
     * @return integer
     */
    function getStatus()
    {
        return (int)$this->status;
    }

    /**
     * Sets document status.
     * @param integer $status
     * @return void
     */
    function setStatus($status)
    {
        $this->status = (int)$status;
    }

    /**
     * Returns parent document id.
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
     * Sets parent document id.
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
     * Returns id of the child of the document.
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
     * Sets id of the child of the document.
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
     * Returns document file hash.
     * @return string
     */
    function getHash()
    {
        // Older versions of module didn't have hash field
        // so we have to calculate and save it on first access
        if (is_null($this->hash)) {
            $hash = hash_file('md5', $this->getFullPath());
            $this->hash = $hash;
            Database::saveDocumentHash($this);
        }
        return $this->hash;
    }

    /**
     * Sets document file hash
     * @param string $hash
     * @return void
     */
    function setHash($hash)
    {
        if (is_null($this->hash)) {
            $this->hash = $hash;
        }
    }

    /**
     * Returns signatures of the document.
     * @return string JSON
     */
    function getSignatures()
    {
        return $this->signatures;
    }

    /**
     * Sets signatures of the document.
     * @param string $signatures JSON
     * @return void
     */
    function setSignatures($signatures)
    {
        $this->signatures = $signatures;
    }

    /**
     * Returns signers of the document.
     * @return void
     */
    function getSigners()
    {
        return $this->signers;
    }

    /**
     * Sets signers of the document.
     * @param string $signers
     * @return void
     */
    function setSigners($signers)
    {
        $this->signers = $signers;
    }

    /**
     * Returns id of the user that blocked the doc.
     * @return integer
     */
    function getBlockBy()
    {
        if (is_null($this->blockBy)) {
            return null;
        } else {
            return (int)$this->blockBy;
        }
    }

    /**
     * Sets id of the user that blocked the doc.
     * @param integer $blockBy
     * @return void
     */
    function setBlockBy($blockBy)
    {
        if (is_null($blockBy)) {
            $this->blockBy = null;
        } else {
            $this->blockBy = (int)$blockBy;
        }
    }

    /**
     * Returns token of the blocked doc.
     * @return string
     */
    function getBlockToken()
    {
        return $this->blockToken;
    }

    /**
     * Sets block token of the doc.
     * @param string $blockToken
     * @return void
     */
    function setBlockToken($blockToken)
    {
        $this->blockToken = $blockToken;
    }

    /**
     * Returns time when the doc was blocked.
     * @return string
     */
    function getBlockTime()
    {
        return $this->blockTime;
    }

    /**
     * Sets time when the doc was blocked.
     * @param string $blockTime
     * @return void
     */
    function setBlockTime($blockTime)
    {
        $this->blockTime = $blockTime;
    }

    /**
     * Returns document creation time.
     * @return string "YYYY-MM-DD hh:mm:ss"
     */
    function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets document creation time.
     * @param string $time "YYYY-MM-DD hh:mm:ss"
     * @return void
     */
    function setCreated($time)
    {
        $this->created = $time;
    }

    /**
     * Returns last document in the chain of signed documents.
     * @return Document
     */
    function getLastDocument()
    {
        if ($this->hasChild()) {
            $child = $this->getChild();
            return $child->getLastDocument();
        } else {
            return $this;
        }
    }

    /**
     * Checks if the document has child.
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
     * Returns child document if it exists.
     * @return Document|null
     */
    function getChild()
    {
        if ($this->childId) {
            return Database::getDocumentById($this->childId);
        } else {
            return null;
        }
    }

    /**
     * Sets child id by passed document.
     * @param Document $doc
     * @return void
     */
    function setChild($doc)
    {
        $this->childId = $doc->id;
    }

    /**
     * Checks if the document has parent.
     * @return boolean
     */
    function hasParent()
    {
        if ($this->getParentId()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns parent document if it exists.
     * @return Document|null
     */
    function getParent()
    {
        if ($this->parentId) {
            return Database::getDocumentById($this->parentId);
        } else {
            return null;
        }
    }

    /**
     * Find original document.
     * @return Document
     */
    function getFirstParent() {
        if ($parent = $this->getParent()) {
            return $parent->getFirstParent();
        } else {
            return $this;
        }
    }

    /**
     * Sets parent id by passed document.
     * @param Document $parent
     * @return void
     */
    function setParent($parent)
    {
        $this->parentId = $parent->id;
    }

    /**
     * Returns document sign info as array.
     * @return array
     */
    function getSignaturesToArray()
    {
        $signatures = json_decode($this->signatures, true);
        if (!is_array($signatures)) {
            return array();
        }
        foreach ($signatures as $index => $signature) {
            $subjectName = explode('/', $signature['subjectName']);
            $newSubjectName = array();
            foreach ($subjectName as $value) {
                $value = explode('=', $value);
                if ($value[0] !== '') {
                    $newSubjectName[$value[0]] = $value[1];
                }
            }
            $signatures[$index]['subjectName'] = $newSubjectName;

            $issuerName = explode('/', $signature['issuerName']);
            $newIssuerName = array();
            foreach ($issuerName as $value) {
                $value = explode('=', $value);
                if ($value[0] !== '') {
                    $newIssuerName[$value[0]] = $value[1];
                }
            }
            $signatures[$index]['issuerName'] = $newIssuerName;
        }
        return $signatures;
    }

    /**
     * Returns document sign info as html table
     * @return string
     */
    function getSignaturesToTable($fields = array('time', 'name', 'org'))
    {
        $signatures = $this->getSignaturesToArray();
        if (!$signatures || !$fields) {
            return '';
        }

        $signaturesString = "<table class='trca-adm-list-table-cell-certificate'>";

        $signaturesString .= '<tr>';
        foreach ($fields as $field) {
            switch ($field) {
                case 'time':
                    $signaturesString .= '<th>' . Loc::getMessage('TR_CA_DOCS_SIGN_TIME') . '</th>';
                    break;
                case 'name':
                    $signaturesString .= '<th>' . Loc::getMessage('TR_CA_DOCS_SIGN_NAME') . '</th>';
                    break;
                case 'org':
                    $signaturesString .= '<th>' . Loc::getMessage('TR_CA_DOCS_SIGN_ORG') . '</th>';
                    break;
                case 'algorithm':
                    $signaturesString .= '<th>' . Loc::getMessage('TR_CA_DOCS_SIGN_ALGORITHM') . '</th>';
                    break;
            }
        }
        $signaturesString .= '</tr>';

        foreach ($signatures as $signature) {

            $signaturesString .= '<tr>';
            foreach ($fields as $field) {
                switch ($field) {
                    case 'time':
                        $signingTime = date("d-m-o H:i", round($signature['signingTime'] / 1000));
                        $signaturesString .= '<td>' . $signingTime . '</td>';
                        break;

                    case 'name':
                        $signaturesString .= '<td>' . $signature['subjectFriendlyName'] . '</td>';
                        break;

                    case 'org':
                        $subjectOrganization = '';
                        // Check for organization name code
                        if ($signature['issuerName']['2.5.4.10']) {
                            $subjectOrganization = $signature['issuerName']['2.5.4.10'];
                        }
                        $signaturesString .= '<td>' . $subjectOrganization . '</td>';
                        break;

                    case 'algorithm':
                        $signaturesString .= '<td>' . $signature['digestAlgorithm'] . '</td>';
                        break;
                }
            }
            $signaturesString .= '</tr>';
        }

        $signaturesString .= '</table>';
        return $signaturesString;
    }

    /**
     * Returns document signers as array
     * @return array
     */
    function getSignersToArray()
    {
        return preg_split("/,/", $this->getSigners(), null, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Return collection of properties of document.
     * @return PropertyCollection
     */
    function getProperties()
    {
        $props = &$this->properties;
        if (!$props) {
            if ($this->getId()) {
                $props = Database::getPropertiesByDocumentId($this->getId(), DB_TABLE_PROPERTY);
            } else {
                $props = new PropertyCollection();
            }
        }
        return $props;
    }

    /**
     * Sets document properties
     * @param PropertyCollection $properties
     * @return void
     */
    function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * Saves changed document in DB or creates new record if id is null.
     * @return void
     */
    public function save()
    {
        Database::saveDocument($this);
        $list = $this->getProperties()->getList();
        foreach ($list as &$prop) {
            if (!$prop->getDocumentId()) {
                $prop->setDocumentId($this->id);
            }
            $prop->save();
        }
    }

    /**
     * Creates a copy of the document object with id = null.
     * @return Document
     */
    public function copy()
    {
        $new = new Document();
        $new->setName($this->getName());
        $new->setPath($this->getPath());
        $new->setType($this->getType());
        $new->setSignatures($this->getSignatures());
        $new->setSigners($this->getSigners());
        $list = $this->getProperties()->getList();
        foreach ($list as &$prop) {
            $newProp = new Property($prop->getType(), $prop->getValue());
            $new->getProperties()->add($newProp);
        }
        return $new;
    }

    /**
     * Returns document info in JSON format.
     *
     * Used to send document to signing app.
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * Prepares document info for converting to JSON.
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

    public function getFullPath()
    {
        return $_SERVER['DOCUMENT_ROOT'] . urldecode($this->getPath());
    }

    public function getHtmlPath()
    {
        // TODO: remove getHtmlPath?
        return str_replace($_SERVER['DOCUMENT_ROOT'], "", $this->path);
    }

    /**
     * Returns url for downloading document file through controller.
     * @return string
     */
    public function getUrl()
    {
        return TR_CA_DOCS_AJAX_CONTROLLER . "?command=content&id=" . $this->getId();
    }

    /**
     * Add new bitrix user id to the comma-separated list of signers
     * @param integer $id
     * @return void
     */
    public function addSigner($id)
    {
        $signers = $this->getSignersToArray();
        $signers[] = (int)$id;
        $this->setSigners(implode(",", $signers));
    }

    /**
     * Blocks document as the current user
     * @param string $token
     * @return void
     */
    public function block($token)
    {
        $userId = Utils::currUserId();
        if ($userId && $token) {
            $this->setStatus(DOC_STATUS_BLOCKED);
            $this->setBlockBy($userId);
            $this->setBlockToken($token);
            $this->setBlockTime(date('Y-m-d H:i:s', time()));
        }
    }

    /**
     * Unblocks the document.
     * @return void
     */
    public function unblock()
    {
            $this->setStatus(DOC_STATUS_NONE);
            $this->setBlockBy(null);
            $this->setBlockToken(null);
            $this->setBlockTime(null);
    }

    /**
     * Removes document and all its parents.
     * @return void
     */
    public function remove()
    {
        // Remove record in database
        Database::removeDocumentRecursively($this);
        // Remove document file
        $file = $_SERVER["DOCUMENT_ROOT"] . $this->getPath();
        $file = rawurldecode($file);
        if (file_exists($file)) {
            File::deleteFile($file);
        }
        // Remove unsigned file if it exists
        if ($this->getType() == DOC_TYPE_SIGNED_FILE) {
            $unsignedFile = preg_replace("/\.sig$/", "", $file);
            if (file_exists($unsignedFile)) {
                File::deleteFile($unsignedFile);
            }
        }
        // Remove unique document directory if it's empty
        $dir = dirname($file);
        if (is_readable($dir)) {
            if (preg_match("/^([\dabcdef]){13}$/", basename($dir))) {
                if (count(array_diff(scandir($dir), array(".", ".."))) == 0) {
                    Directory::deleteDirectory($dir);
                }
            }
        }
    }

    /**
     * Shares document this the specified user at the specified level
     * @param int $userId Bitrix user id
     * @param string $level See "Document access level" in config
     * @return void
     */
    public function share($userId, $level) {
        // Stop if user doesn't exist
        if (!\CUser::GetByID($userId)) {
            return;
        }

        $props = &$this->getProperties();
        $shareReadProp = $props->getPropByTypeAndValue(DOC_SHARE_READ, $userId);
        $shareSignProp = $props->getPropByTypeAndValue(DOC_SHARE_SIGN, $userId);

        switch ($level) {

            case DOC_SHARE_READ:
                // Document already shared with this user on level READ
                if ($shareReadProp) {
                    return;
                }

                $props->add(new Property(DOC_SHARE_READ, $userId));
                break;

            case DOC_SHARE_SIGN:
                // SIGN level should include READ
                if (!$shareReadProp) {
                    $props->add(new Property(DOC_SHARE_READ, $userId));
                }

                // Document already shared with this user on level SIGN
                if ($shareSignProp) {
                    return;
                }

                $props->add(new Property(DOC_SHARE_SIGN, $userId));
                break;
        }
    }

    /**
     * Unshares document from the specified user on all levels
     * @param mixed $userId
     */
    public function unshare($userId) {
        $shareReadProp = $this->getProperties()->getPropByTypeAndValue(DOC_SHARE_READ, $userId);
        if ($shareReadProp) {
            $shareReadProp->remove();
        }
        $shareSignProps = $this->getProperties()->getPropByTypeAndValue(DOC_SHARE_SIGN, $userId);
        if ($shareSignProps) {
            $shareSignProps->remove();
        }
        // Updated properties will be fetched on the next getProperties call
        $this->properties = null;
    }

    /**
     * Checks if user has access to the document at the specified level.
     * When called without level - checks for admin rights and ownership.
     * @param int $userId
     * @param string $level See Document access levels in config
     * @return bool
     */
    public function accessCheck($userId, $level = null) {
        // Admins have access to all docs
        if (Utils::isAdmin($userId)) {
            return true;
        }

        // Document owners always have access
        if ($this->getOwner() == $userId) {
            return true;
        }

        if (!$level) {
            return false;
        }

        $props = $this->getProperties();
        return $props->getPropByTypeAndValue($level, $userId) ? true : false;
    }

    public function getOwner() {
        $props = $this->getProperties();
        $userProp = $props->getPropByType('USER');
        return $userProp ? $userProp->getValue() : false;
    }

    /**
     * Returns true if associated file exists on disk.
     * @return boolean
     */
    function checkFile()
    {
        $file = $_SERVER["DOCUMENT_ROOT"] . urldecode($this->getPath());
        return file_exists($file);
    }

}

