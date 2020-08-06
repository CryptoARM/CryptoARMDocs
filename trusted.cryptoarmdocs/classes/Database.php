<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Loader;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = [
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
];
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
    if (file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/DocumentCollection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/Document.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/RequireSign.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/PropertyCollection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/config.php';
if (isModuleInstalled('trusted.cryptoarmdocsbp')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsbp/classes/WorkflowDocument.php';
}
// Loader::includeModule("trusted.cryptoarmdocsbp");

/**
 * DB interaction class.
 */
class Database {
    /**
     * Return collection of all last documents.
     * Last documents in the chain have empty CHILD_ID field.
     * @return DocumentCollection
     * @global object $DB Bitrix global CDatabase object
     */
    static function getDocuments() {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_DOCUMENTS . ' WHERE CHILD_ID is null';
        $rows = $DB->Query($sql);
        $res = new DocumentCollection();
        while ($array = $rows->Fetch()) {
            $res->add(Document::fromArray($array));
        }
        return $res;
    }

    /**
     * Saves document in DB.
     * If the document doesn't have an id creates new record for it.
     * @param Document $doc Document to be saved
     * @return void
     * @global object  $DB  Bitrix global CDatabase object
     */
    static function saveDocument($doc) {
        if ($doc->getId() == null) {
            Database::insertDocument($doc);
        } else {
            global $DB;
            $parentId = $doc->getParentId();
            $childId = $doc->getChildId();
            $blockBy = $doc->getBlockBy();
            $blockToken = $doc->getBlockToken();
            $blockTime = $doc->getBlockTime();
            $originalId = $doc->getOriginalId();
            $typeSign = $doc->getSignType();
            if (is_null($parentId)) {
                $parentId = 'NULL';
            }
            if (is_null($childId)) {
                $childId = 'NULL';
            }
            if (is_null($blockBy)) {
                $blockBy = 'NULL';
            }
            if (is_null($blockToken)) {
                $blockToken = 'NULL';
            } else {
                $blockToken = "'$blockToken'";
            }
            if (is_null($blockTime)) {
                $blockTime = '1000-01-01 00:00:00';
            }
            if (is_null($typeSign)) {
                $typeSign = DOC_SIGN_TYPE_COMBINED;
            }
            $sql = 'UPDATE ' . DB_TABLE_DOCUMENTS . ' SET '
                . 'NAME = "' . $DB->ForSql($doc->getName()) . '", '
                . 'PATH = "' . $doc->getPath() . '", '
                . 'TYPE = ' . $doc->getType() . ', '
                . 'STATUS = ' . $doc->getStatus() . ', '
                . 'SIGN_TYPE = ' . $typeSign . ', '
                . 'PARENT_ID = ' . $parentId . ', '
                . 'CHILD_ID = ' . $childId . ', '
                . 'HASH = "' . $doc->getHash() . '", '
                . "SIGNATURES = '" . $DB->ForSql($doc->getSignatures()) . "', "
                . "SIGNERS = '" . $DB->ForSql($doc->getSigners()) . "', "
                . 'BLOCK_BY = ' . $blockBy . ', '
                . 'BLOCK_TOKEN = ' . $blockToken . ', '
                . "BLOCK_TIME = '" . $blockTime . "' "
                . 'WHERE ID = ' . $doc->getId();
            $DB->Query($sql);
            Database::saveOriginalId($doc, $originalId);
            Database::saveDocumentParent($doc, $doc->getId());
        }
    }

    /**
     * Adds new sign transaction
     * @param array   $docsId Array of documents
     * @param int     $userId User id
     * @return string transaction UUID
     * @global object $DB     Bitrix global CDatabase object
     */
    static function insertTransaction($docsId = null, $userId = null, $typeTransaction = null) {
        if (is_null($docsId)) {
            return false;
        }
        if (is_null($userId)) {
            global $USER;
            $userId = $USER->GetID();
        }
        if (is_null($typeTransaction)) {
            return false;
        }

        $UUID = Utils::generateUUID();
        $insertDocsId = serialize($docsId);
        $userId = (int)$userId;

        global $DB;
        $sql = 'INSERT INTO ' . DB_TABLE_TRANSACTION . ' '
            . '(UUID, DOCUMENTS_ID, USER_ID, TRANSACTION_TYPE) '
            . 'VALUES ('
            . '"' . $UUID . '", '
            . '"' . $DB->ForSql($insertDocsId) . '", '
            . $userId . ', '
            . $typeTransaction
            . ')';
        $DB->Query($sql);

        return $UUID;
    }

    /**
     * Get sign transaction by UUID from DB
     * @param string  $UUID transaction UUID
     * @return array
     * @global object $DB   Bitrix global CDatabase object
     */
    static function getTransaction($UUID) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_TRANSACTION
            . ' WHERE UUID = "' . $UUID . '"';
        $rows = $DB->Query($sql);
        $array = $rows->Fetch();
        if ($array) {
            $array["ID"] = (int)$array["ID"];
            $array["DOCUMENTS_ID"] = unserialize($array["DOCUMENTS_ID"]);
            $array["USER_ID"] = (int)$array["USER_ID"];
            $array["TRANSACTION_STATUS"] = (int)$array["TRANSACTION_STATUS"];
            $array["TRANSACTION_TYPE"] = (int)$array["TRANSACTION_TYPE"];
            return $array;
        }
        return null;
    }

    /**
     * Complete sign transaction by UUID in DB
     * @param string $UUID transaction UUID
     * @return void
     * @global object $DB Bitrix global CDatabase object
     */
    static function stopTransaction($UUID) {
        global $DB;
        $sql = 'UPDATE ' . DB_TABLE_TRANSACTION . ' SET '
            . 'TRANSACTION_STATUS = ' . DOC_TRANSACTION_COMPLETED . ' '
            . 'WHERE UUID = "' . $UUID . '"';
        $DB->Query($sql);
    }

    /**
     * Adds new document in DB.
     * @param Document $doc Document to be added
     * @return void
     * @global object  $DB  Bitrix global CDatabase object
     */
    static function insertDocument($doc) {
        global $DB;
        $parentId = $doc->getParentId();
        $childId = $doc->getChildId();
        $originalId = $doc->getOriginalId();
        $typeSign = $doc->getSignType();
        if (is_null($parentId)) {
            $parentId = 'NULL';
        }
        if (is_null($childId)) {
            $childId = 'NULL';
        }
        if (is_null($originalId)) {
            $originalId = 'NULL';
        }
        if (is_null($typeSign)) {
            $typeSign = DOC_SIGN_TYPE_COMBINED;
        }
        $sql = 'INSERT INTO ' . DB_TABLE_DOCUMENTS . '  '
            . '(NAME, PATH, TYPE, SIGN_TYPE, PARENT_ID, CHILD_ID, ORIGINAL_ID, HASH, SIGNATURES, SIGNERS)'
            . 'VALUES ('
            . '"' . $DB->ForSql($doc->getName()) . '", '
            . '"' . $doc->getPath() . '", '
            . $doc->getType() . ', '
            . $typeSign . ', '
            . $parentId . ', '
            . $childId . ', '
            . $originalId . ', '
            . '"' . $DB->ForSql($doc->getHash()) . '", '
            . "'" . $DB->ForSql($doc->getSignatures()) . "', "
            . '"' . $DB->ForSql($doc->getSigners()) . '"'
            . ')';
        $DB->Query($sql);
        $doc->setId($DB->LastID());
        Database::saveDocumentParent($doc, $doc->getId());
        if (is_null($originalId)) {
            Database::saveOriginalId($doc, $doc->getId());
        }
    }

    static function saveRequire($require) {
        $requireId = $require->getRequireId();
        $docId = $require->getDocId();
        $userId = $require->getUserId();
        $emailStatus = $require->getEmailStatus();
        $signStatus = (int)$require->getSignStatus();
        $signUUID = $require->getSignUUID();

        if (is_null($signUUID)) {
            $signUUID = Utils::generateUUID();
            $require->setSignUUID($signUUID);
        }

        $requireResponse = self::getRequire($docId, $userId);

        if (!$requireResponse || ($requireResponse && $requireResponse->getSignStatus() && !(bool)$signStatus)) {
            Database::insertRequire($require);
        } else {
            if ($requireId) {
                global $DB;

                if (is_null($emailStatus)) {
                    $emailStatus = "NOT_SENT";
                }

                if (is_null($signStatus)) {
                    $signStatus = DOC_TYPE_FILE;
                }

                $sql = 'UPDATE ' . DB_TABLE_REQUIRE . ' SET '
                    . 'EMAIL_STATUS = "' . $emailStatus . '", '
                    . 'SIGNED = "' . $signStatus . '", '
                    . 'TRANSACTION_UUID = "' . $signUUID . '" '
                    . 'WHERE ID = ' . $requireId;
                $DB->Query($sql);
            }
        }
    }

    static function insertRequire($require) {
        $docId = $require->getDocId();
        $userId = $require->getUserId();
        $emailStatus = $require->getEmailStatus();
        $signStatus = (int)$require->getSignStatus();
        $signUUID = $require->getSignUUID();

        global $DB;

        $sql = 'INSERT INTO ' . DB_TABLE_REQUIRE . '  '
            . '(DOCUMENT_ID, USER_ID, EMAIL_STATUS, SIGNED, TRANSACTION_UUID)'
            . 'VALUES ('
            . $docId . ', '
            . $userId . ', '
            . '"' . $emailStatus . '", '
            . $signStatus . ', '
            . '"' . $signUUID . '"'
            . ')';
        $DB->Query($sql);
    }

    static function getRequire($docId, $userId) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_REQUIRE . ' WHERE ID = ('
            . 'SELECT MAX(ID) FROM ' . DB_TABLE_REQUIRE . ' WHERE DOCUMENT_ID = '
            . $docId . ' AND USER_ID = ' . $userId . ')';
        $rows = $DB->Query($sql);
        $array = $rows->Fetch();
        $res = RequireSign::fromArray($array);
        return $res;
    }


    /**
     * Updates document parent with child id.
     * @param object  $doc Parent document
     * @param integer $id  Document id. Default NULL
     * @return void
     */
    protected static function saveDocumentParent($doc, $id = null) {
        if ($doc->getParent()) {
            $parent = $doc->getParent();
            $parent->setChildId($id);
            $parent->save();
        }
    }

    static function saveDocumentHash($doc) {
        global $DB;
        $docId = $doc->getId();
        $hash = $doc->getHash();
        $sql = 'UPDATE ' . DB_TABLE_DOCUMENTS . ' SET '
            . 'HASH = "' . $hash . '" '
            . 'WHERE ID = ' . $docId . ';';
        $DB->Query($sql);
    }

    static function saveOriginalId($doc, $originalId = null) {
        global $DB;
        $docId = $doc->getId();
        if (is_null($originalId)) {
            $originalDoc = $doc->getFirstParent();
            $originalId = $originalDoc->getId();
        }
        $sql = 'UPDATE ' . DB_TABLE_DOCUMENTS . ' SET '
            . 'ORIGINAL_ID = "' . $originalId . '" '
            . 'WHERE ID = ' . $docId . ';';
        $DB->Query($sql);
        return $originalId;
    }

    /**
     * Removes document from DB.
     * @param Document $doc Document to be removed
     * @return void
     * @global object  $DB  Bitrix global CDatabase object
     */
    static function removeDocument(&$doc) {
        global $DB;
        $sql = 'DELETE FROM ' . DB_TABLE_DOCUMENTS . '  '
            . 'WHERE ID = ' . $doc->getId();
        $DB->Query($sql);
        $sql = 'DELETE FROM ' . DB_TABLE_PROPERTY . ' '
            . 'WHERE DOCUMENT_ID = ' . $doc->getId();
        $DB->Query($sql);
        // Removes childId from parent document
        Database::saveDocumentParent($doc);
    }
    
    /**
     * Returns ids of all documents using in Workflows
     * @return array
     * @global object  $DB  Bitrix global CDatabase object
     */
    static function getDocumentIdsInWorkflows() {
        global $DB;
        $sql = 'SELECT DISTINCT DOCUMENT_ID FROM b_bp_workflow_instance GROUP BY DOCUMENT_ID';
        $row = $DB->Query($sql);
        while ($array = $row->Fetch()) {
            $doc = $array["DOCUMENT_ID"];
            $docIds[] =  $doc;
        }
        return $docIds;
    }

    /**
     * Removes document and all of its parents from DB.
     * Also cleans up any workflows, associated with the document.
     * @param Document $doc Document to be removed
     * @return void
     * @global object  $DB  Bitrix global CDatabase object
     */
    static function removeDocumentRecursively(&$doc) {
        global $DB;
        $parent = null;
        if ($doc->getParent()) {
            $parent = $doc->getParent();
        }
        if (Loader::includeModule('bizproc')) {
            if (isModuleInstalled("trusted.cryptoarmdocsbp")) {
                \CBPDocument::OnDocumentDelete(
                    WorkflowDocument::getComplexDocumentId($doc->getId()),
                    $errors = []
                );
            }
        }
        Database::removeDocument($doc);
        if ($parent) {
            Database::removeDocumentRecursively($parent);
        }
    }

    /**
     * Get document from DB by ID.
     * @param integer $id Document ID
     * @return Document
     * @global object $DB Bitrix global CDatabase object
     */
    static function getDocumentById($id) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_DOCUMENTS . ' WHERE ID = ' . $id;
        $rows = $DB->Query($sql);
        $array = $rows->Fetch();
        $res = Document::fromArray($array);
        return $res;
    }

    /**
     * Returns collection of last documents by name.
     * Multiple documents can have the same name.
     * @param string  $name Name of the document.
     * @return DocumentCollection
     * @global object $DB   Bitrix global CDatabase object
     */
    static function getDocumentsByName($name) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_DOCUMENTS
            . ' WHERE NAME LIKE CONCAT("%", TRIM("' . $DB->ForSql($name) . '"), "%")'
            . ' AND CHILD_ID is null';
        $rows = $DB->Query($sql);
        $docs = new DocumentCollection();
        while ($array = $rows->Fetch()) {
            $doc = Document::fromArray($array);
            $docs->add($doc);
        }
        return $docs;
    }

    /**
     * Get documents from DB by BLOCK_TOKEN.
     * @param string  $blockToken string BLOCK_TOKEN
     * @return DocumentCollection
     * @global object $DB         Bitrix global CDatabase object
     */
    static function getDocumentsByBlockToken($token) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_DOCUMENTS . ' WHERE BLOCK_TOKEN = ' . '"' . $DB->ForSql($token) . '"';
        $rows = $DB->Query($sql);
        $docs = new DocumentCollection();
        while ($array = $rows->Fetch()) {
            $doc = Document::fromArray($array);
            $docs->add($doc);
        }
        return $docs;
    }

    /**
     * Saves property in DB.
     * If property ID is null creates new record.
     * @param Property $property  Property to be saved
     * @param string   $tableName DB table name
     * @return void
     * @global object  $DB        Bitrix global CDatabase object
     */
    static function saveProperty($property, $tableName = DB_TABLE_PROPERTY) {
        if ($property->getId() == null) {
            Database::insertProperty($property, $tableName);
        } else {
            global $DB;
            $sql = 'UPDATE ' . $tableName .
                ' SET DOCUMENT_ID = ' . $property->getDocumentId() . ',
                      TYPE="' . $DB->ForSql($property->getType()) . '",
                      VALUE="' . $DB->ForSql($property->getValue()) . '"
                WHERE ID = ' . $property->getId();
            $DB->Query($sql);
        }
    }

    /**
     * Adds new property to DB.
     * @param Property $property  Property to be added
     * @param string   $tableName DB table name
     * @return void
     * @global object  $DB        Bitrix global CDatabase object
     */
    static function insertProperty($property, $tableName = DB_TABLE_PROPERTY) {
        global $DB;
        $sql = 'INSERT INTO ' . $tableName .
            ' (DOCUMENT_ID, TYPE, VALUE)
                VALUES (' .
            $property->getDocumentId() . ', "' .
            $DB->ForSql($property->getType()) . '", "' .
            $DB->ForSql($property->getValue()) . '")';
        $DB->Query($sql);
        $property->setId($DB->LastID());
    }

    /**
     * Removes document property from the DB
     * @param Property $property Property to be removed
     */
    static function removeProperty($property) {
        global $DB;
        $sql = 'DELETE FROM ' . DB_TABLE_PROPERTY . '  '
            . 'WHERE ID = ' . $property->getId();
        $DB->Query($sql);
    }

    /**
     * Gets property collection from DB by specified type and value fields.
     * @param string  $type      TYPE field
     * @param string  $value     VALUE field
     * @param string  $tableName DB table name
     * @return PropertyCollection
     * @global object $DB        Bitrix global CDatabase object
     */
    static function getPropertiesByTypeAndValue($type, $value, $tableName = DB_TABLE_PROPERTY) {
        global $DB;
        $sql = 'SELECT * FROM ' . $tableName .
            ' WHERE TYPE = "' . $DB->ForSql($type) .
            '" AND VALUE = "' . $DB->ForSql($value) . '"';
        $rows = $DB->Query($sql);
        $res = new PropertyCollection();
        while ($array = $rows->Fetch()) {
            $res->add(Property::fromArray($array));
        }
        return $res;
    }

    /**
     * Get single property from DB by specified field.
     * @param string  $fldName   Field in DB table
     * @param string  $value     VALUE field
     * @param string  $tableName DB table name
     * @return Property
     * @global object $DB        Bitrix global CDatabase object
     */
    static function getPropertyBy($fldName, $value, $tableName = DB_TABLE_PROPERTY) {
        $props = Database::getPropertiesBy($fldName, $value, $tableName);
        $res = null;
        if ($props->count()) {
            $res = $props->items(0);
        }
        return $res;
    }

    /**
     * Gets property collection from DB by specified field.
     * @param string  $fldName   Field in DB
     * @param string  $value     VALUE field
     * @param string  $tableName DB table name
     * @return PropertyCollection
     * @global object $DB        Bitrix global CDatabase object
     */
    static function getPropertiesBy($fldName, $value, $tableName = DB_TABLE_PROPERTY) {
        global $DB;
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE  ' . $fldName . ' = "' . $DB->ForSql($value) . '"';
        $rows = $DB->Query($sql);
        $res = new PropertyCollection();
        while ($array = $rows->Fetch()) {
            $res->add(Property::fromArray($array));
        }
        return $res;
    }

    /**
     * Gets property collection from DB by document ID.
     * @param integer $documentId Document ID
     * @param string  $tableName  DB table name
     * @return PropertyCollection
     */
    static function getPropertiesByDocumentId($documentId, $tableName = DB_TABLE_PROPERTY) {
        return Database::getPropertiesBy('DOCUMENT_ID', $documentId, $tableName);
    }

    static function getRequiresByDocumentId($documentId) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_REQUIRE . ' WHERE DOCUMENT_ID = ' . $documentId;
        $rows = $DB->Query($sql);
        $res = new RequireCollection();
        while ($array = $rows->Fetch()) {
            $res->add(RequireSign::fromArray($array));
        }
        return $res;
    }

    /**
     * Gets property values of specified type of the specified document
     * @param integer $documentId
     * @param string  $type Property type
     * @return array
     */
    static function getPropertyValuesByDocumentIdAndType($documentId, $type) {
        $props = Database::getPropertiesByDocumentId($documentId);
        $res = [];
        foreach ($props->getList() as $prop) {
            if ($prop->getType() == $type) {
                $res[] = $prop->getValue();
            }
        }
        return $res;
    }

    /**
     * Returns all documents which have specified property attached to them.
     * @param string $type Property type
     * @return DocumentCollection
     */
    static function getDocumentsByPropertyType($type) {
        global $DB;
        $sql = "
            SELECT
                TD.*
            FROM
                " . DB_TABLE_DOCUMENTS . " as TD,
                " . DB_TABLE_PROPERTY . " as TDP
            WHERE
                isnull(TD.CHILD_ID) AND
                TD.ID = TDP.DOCUMENT_ID AND
                TDP.TYPE = '" . $type . "'
        ";
        $rows = $DB->Query($sql);
        $docs = new DocumentCollection;
        while ($row = $rows->Fetch()) {
            $docs->add(Document::fromArray($row));
        }
        return $docs;
    }

    /**
     * Returns all documents which have specified property with specified value
     * attached to them.
     * @param string $type Property type
     * @return DocumentCollection
     */
    static function getDocumentsByPropertyTypeAndValue($type, $value) {
        global $DB;
        $sql = "
            SELECT
                TD.*
            FROM
                " . DB_TABLE_DOCUMENTS . " as TD,
                " . DB_TABLE_PROPERTY . " as TDP
            WHERE
                isnull(TD.CHILD_ID) AND
                TD.ID = TDP.DOCUMENT_ID AND
                TDP.TYPE = '" . $type . "' AND
                TDP.VALUE = '" . $value . "'
        ";
        $rows = $DB->Query($sql);
        $docs = new DocumentCollection;
        while ($row = $rows->Fetch()) {
            $docs->add(Document::fromArray($row));
        }
        return $docs;
    }

    /**
     * Returns object with document ids, filtered by specified filter.
     * @param array   $arOrder Sort direction
     * @param array   $filter  Array with filter keys and values
     * @return CDBResult
     * @global object $DB      Bitrix global CDatabase object
     */
    static function getDocumentIdsByFilter($arOrder = [], $filter) {
        // TODO: change $arOrder to separate $by and $order
        $arFields = [
            'DOC' => [
                'FIELD_NAME' => 'TD.ID',
            ],
            'FILE_NAME' => [
                'FIELD_NAME' => 'TD.NAME',
            ],
            'SIGNATURES' => [
                'FIELD_NAME' => 'TD.SIGNATURES',
            ],
            'TYPE' => [
                'FIELD_NAME' => 'TD.TYPE',
            ],
            'STATUS' => [
                'FIELD_NAME' => 'TD.STATUS',
            ],
            'USER' => [
                'FIELD_NAME' => 'BU.EMAIL',
            ]
        ];

        $find_docId = (string)$filter['DOC'];
        $find_fileName = (string)$filter['FILE_NAME'];
        $find_signatures = (string)$filter['SIGNATURES'];
        $find_type = (string)$filter['TYPE'];
        $find_status = (string)$filter['STATUS'];
        $find_shareUser = (string)$filter['SHARE_USER'];
        $find_owner = (string)$filter['OWNER'];
        $find_user = (string)$filter['USER'];
        foreach ($arOrder as $k => $v) {
            if ($k == 'USER')
                $fus = true; 
        };

        global $DB;
        
        $sql = "
            SELECT
                TD.ID
            FROM
                " . DB_TABLE_DOCUMENTS . " as TD ";
        if ($find_user != "" || $fus) {
            $sql .= "LEFT JOIN (SELECT TDPD.VALUE, TDPD.DOCUMENT_ID FROM tr_ca_docs_property as TDPD WHERE TDPD.TYPE = 'USER') as TDP ON TDP.DOCUMENT_ID = TD.ID 
                    LEFT JOIN b_user as BU ON BU.ID = TDP.VALUE";
        };
        if ($find_shareUser !== "" || $find_owner !== "")
            $sql .= "RIGHT JOIN tr_ca_docs_property as TDP ON TDP.DOCUMENT_ID = TD.ID ";
        if ($find_shareUser !== "" && $find_owner !== "")
            $sql .= "RIGHT JOIN (SELECT TDP.DOCUMENT_ID
                     FROM
                        tr_ca_docs_property as TDP
                     WHERE
                        (TDP.TYPE = 'USER' OR TDP.TYPE = 'SHARE_READ') AND
                        TDP.VALUE = '" . $find_shareUser . "'
                        ) as TMP ON TMP.DOCUMENT_ID = TDP.DOCUMENT_ID ";
        $sql .= "
            WHERE
                isnull(TD.CHILD_ID)";
        if ($find_user !=="") {
            $sql .= " AND BU.EMAIL LIKE '%" . $find_user . "%'";
        }
        if ($find_docId !== "")
            $sql .= " AND TD.ID = '" . $find_docId . "'";
        if ($find_fileName !== "")
            $sql .= " AND TD.NAME LIKE '%" . $find_fileName . "%'";
        if ($find_signatures !== "")
            $sql .= " AND TD.SIGNATURES LIKE '%" . $DB->ForSql($find_signatures) . "%'";
        if ($find_type !== "")
            $sql .= " AND TD.TYPE = " . $find_type;
        if ($find_status !== "")
            $sql .= " AND TD.STATUS = " . $find_status;
        if ($find_shareUser !== "" && $find_owner !== "") {
            $sql .= " AND TDP.DOCUMENT_ID = TD.ID
                      AND TDP.TYPE = 'USER'
                      AND TDP.VALUE = '" . $find_owner . "'";
        } else {
            if ($find_shareUser !== "")
                $sql .= " AND (TDP.TYPE = 'USER' OR TDP.TYPE = 'SHARE_READ') AND
                        TDP.VALUE = '" . $find_shareUser . "'";
            if ($find_owner !== "")
                $sql .= " AND TDP.TYPE = 'USER' AND
                        TDP.VALUE = '" . $find_owner . "'";
        }
        $sql .= " GROUP BY TD.ID";

        $sOrder = '';
        if (is_array($arOrder)) {
            foreach ($arOrder as $k => $v) {
                if (array_key_exists($k, $arFields)) {
                    $v = strtoupper($v);
                    if ($v != 'DESC') {
                        $v = 'ASC';
                    }
                    if (strlen($sOrder) > 0) {
                        $sOrder .= ', ';
                    }
                    $k = strtoupper($k);
                    $sOrder .= $arFields[$k]['FIELD_NAME'] . ' ' . $v;
                }
            }

            if (strlen($sOrder) > 0) {
                $sql .= ' ORDER BY ' . $sOrder . ';';
            }
        }
        $rows = $DB->Query($sql);
        return $rows;
    }

    /**
     * Returns object with info about users with attached documents,
     * filtered by specified filter.
     * @param string  $by     Sort column
     * @param string  $order  Sort order
     * @param array   $filter Array with filter keys and values
     * @return CDBResult
     * @global object $DB     Bitrix global CDatabase object
     */
    static function getUsersWithDocsByFilter($by, $order, $filter) {
        $find_user_id = (string)$filter["USER_ID"];
        $find_user_name = (string)$filter["USER_NAME"];
        $find_user_email = (string)$filter["USER_EMAIL"];
        $find_doc_name = (string)$filter["DOC_NAME"];
        $find_doc_type = (string)$filter["DOC_TYPE"];
        $find_doc_status = (string)$filter["DOC_STATUS"];

        $sqlWhere = [];
        if ($find_user_id !== "") {
            $sqlWhere[] = "BU.ID = '" . $find_user_id . "'";
        }
        if ($find_user_name !== "") {
            $sqlWhere[] = "CONCAT(BU.NAME, ' ', BU.LAST_NAME) LIKE '%" . $find_user_name . "%'";
        }
        if ($find_user_email !== "") {
            $sqlWhere[] = "BU.EMAIL LIKE '%" . $find_user_email . "%'";
        }
        if ($find_doc_name !== "") {
            $sqlWhere[] = "TD.NAME LIKE '%" . $find_doc_name . "%'";
        }
        if ($find_doc_type !== "") {
            $sqlWhere[] = "TD.TYPE = " . $find_doc_type;
        }
        if ($find_doc_status !== "") {
            $sqlWhere[] = "TD.STATUS = " . $find_doc_status;
        }

        global $DB;
        $sql = "
            SELECT
                BU.ID, BU.LOGIN, CONCAT(BU.NAME, ' ', BU.LAST_NAME) as NAME, BU.EMAIL
            FROM
                b_user as BU,
                tr_ca_docs as TD,
                tr_ca_docs_property as TDP
            WHERE
                isnull(TD.CHILD_ID) AND
                TD.ID = TDP.DOCUMENT_ID AND
                TDP.VALUE = BU.ID AND
                TDP.TYPE = 'USER'";

        // Filtering
        if (count($sqlWhere)) {
            $sql .= " AND " . implode(" AND ", $sqlWhere);
        }

        // Squash rows by user
        $sql .= " GROUP BY BU.ID";

        // Ordering
        $fields = [
            "USER_ID" => "BU.ID",
            "USER_NAME" => "NAME",
        ];
        $by = strtoupper($by);
        $order = strtoupper($order);
        if (array_key_exists($by, $fields)) {
            if ($order != "DESC") {
                $order = "ASC";
            }
            $sql .= " ORDER BY " . $fields[$by] . " " . $order . ";";
        }

        $rows = $DB->Query($sql);
        return $rows;
    }

    static function getUserByDoc($docId) {
        global $DB;
        $docId = (int)$docId;

        $sql = "
            SELECT
                USERS.EMAIL as EMAIL
            FROM
                tr_ca_docs_property as DOCS
                INNER JOIN
                b_user as USERS
                    ON DOCS.VALUE = USERS.ID
            WHERE DOCS.DOCUMENT_ID = '$docId' AND
            DOCS.TYPE = 'USER'";
        $rows = $DB->Query($sql);
        while ($row = $rows->Fetch()){
            $res.=$row["EMAIL"];
        }
        return $res;
    }

    /**
     * Returns all documents attached or shared with the user.
     * @param integer $userId
     * @param true    $shared Include shared documents
     * @return DocumentCollection
     */
    static function getDocumentsByUser($userId, $shared = false) {
        global $DB;
        $userId = (int)$userId;

        $sql = "
            SELECT
                TD.ID
            FROM
                tr_ca_docs as TD,
                tr_ca_docs_property as TDP
            WHERE
                TD.ID = TDP.DOCUMENT_ID AND
                isnull(TD.CHILD_ID) AND ";
        if ($shared) {
            $sql .= "
                ((TDP.TYPE = 'USER' AND TDP.VALUE = '$userId') OR
                 (TDP.TYPE = 'SHARE_READ' AND TDP.VALUE = '$userId') OR
                 (TDP.TYPE = 'SHARE_SIGN' AND TDP.VALUE = '$userId'))";
        } else {
            $sql .= "
                TDP.TYPE = 'USER' AND TDP.VALUE = '$userId'";
        }
        $sql .= " GROUP BY TD.ID;";
        $rows = $DB->Query($sql);
        $docs = new DocumentCollection;
        while ($row = $rows->Fetch()) {
            $docs->add(Database::getDocumentById($row["ID"]));
        }
        return $docs;
    }

    /**
     * Returns array of ids of documents attached to the user.
     * @param integer $userId
     * @return array
     */
    static function getDocumentIdsByUser($userId) {
        $docs = Database::getDocumentsByUser($userId);
        $res = [];
        foreach ($docs->getList() as $doc) {
            $res[] = $doc->getId();
        }
        return $res;
    }

    /**
     * Returns array of order IDs with documents attached to them.
     * Requires 'sale' module.
     * @return array
     * @global object $DB Bitrix global CDatabase object
     */
    static function getOrders() {
        global $DB;
        $sql = "    SELECT VALUE
            FROM " . DB_TABLE_PROPERTY . " TDP, b_sale_order BO
            WHERE TDP.TYPE = 'ORDER' AND TDP.VALUE = BO.ID
            GROUP BY TYPE, VALUE";
        $rows = $DB->Query($sql);
        $res = [];
        while ($row = $rows->Fetch()) {
            $res[] = $row["VALUE"];
        }
        return $res;
    }

    /**
     * Returns order IDs with filter applied.
     * Requires 'sale' module.
     * @param array   $arOrder Sort direction
     * @param array   $filter  Filter array with keys and values
     * @return CDBResult
     * @global object $DB      Bitrix global CDatabase object
     */
    static function getOrdersByFilter($arOrder = [], $filter) {
        // TODO: change $arOrder to separate $by and $order
        $arFields = [
            'ORDER' => [
                'FIELD_NAME' => 'OrderList.VALUE',
            ],
            'ORDER_STATUS' => [
                'FIELD_NAME' => 'BO.STATUS_ID',
            ],
            'CLIENT_NAME' => [
                'FIELD_NAME' => 'BU.NAME',
            ],
            'DOCS' => [
                'FIELD_NAME' => 'OrderList.VALUE',
            ],
            'ORDER_EMAIL_STATUS' => [
                'FIELD_NAME' => 'OrderList.EMAIL',
            ],
            'DOC_STATE' => [
                'FIELD_NAME' => 'TDP.VALUE',
            ],
        ];

        $find_order = (string)$filter['ORDER'];
        $find_order_status = (string)$filter['ORDER_STATUS'];
        $find_clientEmail = (string)$filter['CLIENT_EMAIL'];
        $find_clientName = (string)$filter['CLIENT_NAME'];
        $find_clientLastName = (string)$filter['CLIENT_LASTNAME'];
        $find_orderEmailStatus = (string)$filter['ORDER_EMAIL_STATUS'];
        $find_docState = (string)$filter['DOC_STATE'];

        global $DB;
        $sql = "
            SELECT
                OrderList.VALUE as `ORDER`, OrderList.EMAIL as `EMAIL`
            FROM
                " . DB_TABLE_DOCUMENTS . " TD,
                " . DB_TABLE_PROPERTY . " TDP,
                (SELECT
                    OrderID.VALUE, OrderID.DOCUMENT_ID, Property.VALUE AS EMAIL
                FROM
                    " . DB_TABLE_PROPERTY . "  AS Property
                RIGHT JOIN
                    (SELECT
                        MAX(DOCUMENT_ID) AS DOCUMENT_ID, TYPE, CAST(VALUE AS UNSIGNED) as VALUE
                    FROM
                        " . DB_TABLE_PROPERTY . "
                    WHERE
                        TYPE = 'ORDER'
                    GROUP BY VALUE) AS OrderID ON OrderID.DOCUMENT_ID = Property.DOCUMENT_ID
                        AND Property.TYPE = 'EMAIL') as OrderList,
                b_sale_order BO,
                b_user BU
            WHERE
                BO.USER_ID = BU.ID
                AND BO.ID = OrderList.VALUE
                AND TD.ID = TDP.DOCUMENT_ID
                AND TD.ID = OrderList.DOCUMENT_ID";

        if ($find_order !== "")
            $sql .= " AND OrderList.VALUE = '" . $find_order . "'";
        if ($find_order_status !== "")
            $sql .= " AND BO.STATUS_ID = '" . $find_order_status . "'";
        if ($find_clientName !== "")
            $sql .= " AND BU.NAME LIKE '%" . $find_clientName . "%'";
        if ($find_clientLastName !== "")
            $sql .= " AND BU.LAST_NAME LIKE '%" . $find_clientLastName . "%'";
        if ($find_clientEmail !== "")
            $sql .= " AND BU.EMAIL LIKE '%" . $find_clientEmail . "%'";
        if ($find_orderEmailStatus !== "") {
            if ($find_orderEmailStatus == "NOT_SENT") {
                $sql .= " AND isnull(OrderList.EMAIL) ";
            } else {
                $sql .= " AND OrderList.EMAIL = '" . $find_orderEmailStatus . "'";
            }
        }
        if ($find_docState !== "")
            $sql .= " AND TDP.VALUE ='" . $find_docState . "'";

        $sql .= " GROUP BY OrderList.VALUE";

        $sOrder = '';
        if (is_array($arOrder)) {
            foreach ($arOrder as $k => $v) {
                if (array_key_exists($k, $arFields)) {
                    $v = strtoupper($v);
                    if ($v != 'DESC') {
                        $v = 'ASC';
                    }
                    if (strlen($sOrder) > 0) {
                        $sOrder .= ', ';
                    }
                    $k = strtoupper($k);
                    $sOrder .= $arFields[$k]['FIELD_NAME'] . ' ' . $v;
                }
            }

            if (strlen($sOrder) > 0) {
                $sql .= ' ORDER BY ' . $sOrder . ';';
            }
        }
        $rows = $DB->Query($sql);

        return $rows;
    }

    /**
     * Returns array of document IDs by the order ID.
     * Requires 'sale' module.
     * @param string $order Order ID
     * @return array
     */
    static function getIdsByOrder($order) {
        $docs = Database::getDocumentsByOrder($order);
        $list = $docs->getList();
        $ids = [];
        foreach ($list as &$doc) {
            $ids[] = $doc->getId();
        }
        return $ids;
    }

    /**
     * Returns DocumentCollection by order ID.
     * @param string  $order Order ID
     * @return DocumentCollection
     * @global object $DB    Bitrix global CDatabase object
     */
    static function getDocumentsByOrder($order) {
        global $DB;
        $sql = 'SELECT ' . DB_TABLE_DOCUMENTS . '.* FROM ' . DB_TABLE_DOCUMENTS . ', ' . DB_TABLE_PROPERTY . ' '
            . 'WHERE isnull(CHILD_ID) AND '
            . DB_TABLE_DOCUMENTS . '.ID = ' . DB_TABLE_PROPERTY . '.DOCUMENT_ID AND '
            . DB_TABLE_PROPERTY . '.TYPE = "ORDER" AND '
            . DB_TABLE_PROPERTY . '.VALUE = "' . $order . '"';
        $rows = $DB->Query($sql);
        $docs = new DocumentCollection();
        while ($array = $rows->Fetch()) {
            $docs->add(Document::fromArray($array));
        }
        return $docs;
    }

    /**
     * Returns order ID of the document by its ID
     * @param integer $id Document ID
     * @return array
     */

    static function getOrderByDocumentId($id) {
        global $DB;
        $sql = 'SELECT VALUE FROM ' . DB_TABLE_PROPERTY . ' '
            . 'WHERE '
            . 'DOCUMENT_ID = "' . $id . '" AND '
            . 'TYPE = "ORDER"';
        $rows = $DB->Query($sql);
        $orderId = $rows->Fetch();
        return $orderId;
    }

    /**
     * Returns IDs of users with access to the document
     * @param integer $id Document ID
     * @return array
     */

    static function getUserIdsByDocument($id) {
        global $DB;
        $sql = 'SELECT VALUE FROM ' . DB_TABLE_PROPERTY . ' '
            . 'WHERE '
            . 'DOCUMENT_ID = "' . $id . '" AND '
            . 'TYPE = "SHARE_READ"';
        $rows = $DB->Query($sql);
        $userIds = [];
        while ($row = $rows->Fetch()) {
            $userIds[] = (int)$row["VALUE"];
        }
        return $userIds;
    }

    static function removeRequireToSign($docId, $userId) {
        global $DB;
        $sql = 'DELETE FROM ' . DB_TABLE_REQUIRE . ' '
            . 'WHERE '
            . 'DOCUMENT_ID = ' . $docId . ' AND '
            . 'USER_ID = ' . $userId;
        $DB->Query($sql);
    }

}

