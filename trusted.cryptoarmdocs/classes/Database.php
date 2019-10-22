<?php
namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;

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

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/DocumentCollection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/Document.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/PropertyCollection.php';
if (isModuleInstalled('trusted.cryptoarmdocsbp')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocsbp/classes/WorkflowDocument.php';
}
// Loader::includeModule("trusted.cryptoarmdocsbp");

/**
 * DB interaction class.
 */
class Database
{
    /**
     * Return collection of all last documents.
     * Last documents in the chain have empty CHILD_ID field.
     * @global object $DB Bitrix global CDatabase object
     * @return DocumentCollection
     */
    static function getDocuments()
    {
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
     * @global object $DB Bitrix global CDatabase object
     * @param Document $doc Document to be saved
     * @return void
     */
    static function saveDocument($doc)
    {
        if ($doc->getId() == null) {
            Database::insertDocument($doc);
        } else {
            global $DB;
            $parentId = $doc->getParentId();
            $childId = $doc->getChildId();
            $blockBy = $doc->getBlockBy();
            $blockToken = $doc->getBlockToken();
            $blockTime = $doc->getBlockTime();
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
            $sql = 'UPDATE ' . DB_TABLE_DOCUMENTS . ' SET '
                . 'NAME = "' . $DB->ForSql($doc->getName()) . '", '
                . 'PATH = "' . $doc->getPath() . '", '
                . 'TYPE = ' . $doc->getType() . ', '
                . 'STATUS = ' . $doc->getStatus() . ', '
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
            Database::saveDocumentParent($doc, $doc->getId());
        }
    }

    /**
     * Adds new document in DB.
     * @global object $DB Bitrix global CDatabase object
     * @param Document $doc Document to be added
     * @return void
     */
    static function insertDocument($doc)
    {
        global $DB;
        $parentId = $doc->getParentId();
        $childId = $doc->getChildId();
        if (is_null($parentId)) {
            $parentId = 'NULL';
        }
        if (is_null($childId)) {
            $childId = 'NULL';
        }
        $sql = 'INSERT INTO ' . DB_TABLE_DOCUMENTS . '  '
            . '(NAME, PATH, TYPE, PARENT_ID, CHILD_ID, HASH, SIGNATURES, SIGNERS)'
            . 'VALUES ('
            . '"' . $DB->ForSql($doc->getName()) . '", '
            . '"' . $doc->getPath() . '", '
            . $doc->getType() . ', '
            . $parentId . ', '
            . $childId . ', '
            . '"' . $DB->ForSql($doc->getHash()) . '", '
            . "'" . $DB->ForSql($doc->getSignatures()) . "', "
            . '"' . $DB->ForSql($doc->getSigners()) . '"'
            . ')';
        $DB->Query($sql);
        $doc->setId($DB->LastID());
        Database::saveDocumentParent($doc, $doc->getId());
    }

    /**
     * Updates document parent with child id.
     * @param object $doc Parent document
     * @param integer $id Document id. Default NULL
     * @return void
     */
    protected static function saveDocumentParent($doc, $id = null)
    {
        if ($doc->getParent()) {
            $parent = $doc->getParent();
            $parent->setChildId($id);
            $parent->save();
        }
    }

    static function saveDocumentHash($doc)
    {
        global $DB;
        $docId = $doc->getId();
        $hash = $doc->getHash();
        $sql = 'UPDATE ' . DB_TABLE_DOCUMENTS . ' SET '
            . 'HASH = "' . $hash . '" '
            . 'WHERE ID = ' . $docId . ';';
        $DB->Query($sql);
    }

    /**
     * Removes document from DB.
     * @global object $DB Bitrix global CDatabase object
     * @param Document $doc Document to be removed
     * @return void
     */
    static function removeDocument(&$doc)
    {
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
     * Removes document and all of its parents from DB.
     * Also cleans up any workflows, associated with the document.
     * @global object $DB Bitrix global CDatabase object
     * @param Document $doc Document to be removed
     * @return void
     */
    static function removeDocumentRecursively(&$doc)
    {
        global $DB;
        $parent = null;
        if ($doc->getParent()) {
            $parent = $doc->getParent();
        }
        if (Loader::includeModule('bizproc')) {
            if (isModuleInstalled("trusted.cryptoarmdocsbp")) {
                \CBPDocument::OnDocumentDelete(
                    WorkflowDocument::getComplexDocumentId($doc->getId()),
                    $errors = array()
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
     * @global object $DB Bitrix global CDatabase object
     * @param integer $id Document ID
     * @return Document
     */
    static function getDocumentById($id)
    {
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
     * @global object $DB Bitrix global CDatabase object
     * @param string $name Name of the document.
     * @return DocumentCollection
     */
    static function getDocumentsByName($name)
    {
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
     * @global object $DB Bitrix global CDatabase object
     * @param string $blockToken string BLOCK_TOKEN
     * @return DocumentCollection
     */
    static function getDocumentsByBlockToken($token)
    {
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
     * @global object $DB Bitrix global CDatabase object
     * @param Property $property Property to be saved
     * @param string $tableName DB table name
     * @return void
     */
    static function saveProperty($property, $tableName = DB_TABLE_PROPERTY)
    {
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
     * @global object $DB Bitrix global CDatabase object
     * @param Property $property Property to be added
     * @param string $tableName DB table name
     * @return void
     */
    static function insertProperty($property, $tableName = DB_TABLE_PROPERTY)
    {
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
    static function removeProperty($property)
    {
        global $DB;
        $sql = 'DELETE FROM ' . DB_TABLE_PROPERTY . '  '
            . 'WHERE ID = ' . $property->getId();
        $DB->Query($sql);
    }

    /**
     * Gets property collection from DB by specified type and value fields.
     * @global object $DB Bitrix global CDatabase object
     * @param string $type TYPE field
     * @param string $value VALUE field
     * @param string $tableName DB table name
     * @return PropertyCollection
     */
    static function getPropertiesByTypeAndValue($type, $value, $tableName = DB_TABLE_PROPERTY)
    {
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
     * @global object $DB Bitrix global CDatabase object
     * @param string $fldName Field in DB table
     * @param string $value VALUE field
     * @param string $tableName DB table name
     * @return Property
     */
    static function getPropertyBy($fldName, $value, $tableName = DB_TABLE_PROPERTY)
    {
        $props = Database::getPropertiesBy($fldName, $value, $tableName);
        $res = null;
        if ($props->count()) {
            $res = $props->items(0);
        }
        return $res;
    }

    /**
     * Gets property collection from DB by specified field.
     * @global object $DB Bitrix global CDatabase object
     * @param string $fldName Field in DB
     * @param string $value VALUE field
     * @param string $tableName DB table name
     * @return PropertyCollection
     */
    static function getPropertiesBy($fldName, $value, $tableName = DB_TABLE_PROPERTY)
    {
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
     * @param string $tableName DB table name
     * @return PropertyCollection
     */
    static function getPropertiesByDocumentId($documentId, $tableName = DB_TABLE_PROPERTY)
    {
        return Database::getPropertiesBy('DOCUMENT_ID', $documentId, $tableName);
    }

    /**
     * Gets property values of specified type of the specified document
     * @param integer $documentId
     * @param string $type Property type
     * @return array
     */
    static function getPropertyValuesByDocumentIdAndType($documentId, $type) {
        $props = Database::getPropertiesByDocumentId($documentId);
        $res = array();
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
    static function getDocumentsByPropertyType($type)
    {
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
        while($row = $rows->Fetch()) {
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
    static function getDocumentsByPropertyTypeAndValue($type, $value)
    {
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
        while($row = $rows->Fetch()) {
            $docs->add(Document::fromArray($row));
        }
        return $docs;
    }

    /**
     * Returns object with document ids, filtered by specified filter.
     * @global object $DB Bitrix global CDatabase object
     * @param array $arOrder Sort direction
     * @param array $filter Array with filter keys and values
     * @return CDBResult
     */
    static function getDocumentIdsByFilter($arOrder = array(), $filter)
    {
        // TODO: change $arOrder to separate $by and $order
        $arFields = array(
            'DOC' => array(
                'FIELD_NAME' => 'TD.ID',
            ),
            'FILE_NAME' => array(
                'FIELD_NAME' => 'TD.NAME',
            ),
            'SIGNATURES' => array(
                'FIELD_NAME' => 'TD.SIGNATURES',
            ),
            'TYPE' => array(
                'FIELD_NAME' => 'TD.TYPE',
            ),
            'STATUS' => array(
                'FIELD_NAME' => 'TD.STATUS',
            ),
        );

        $find_docId = (string)$filter['DOC'];
        $find_fileName = (string)$filter['FILE_NAME'];
        $find_signatures = (string)$filter['SIGNATURES'];
        $find_type = (string)$filter['TYPE'];
        $find_status = (string)$filter['STATUS'];
        $find_shareUser = (string)$filter['SHARE_USER'];
        $find_owner = (string)$filter['OWNER'];

        global $DB;
        $sql = "
            SELECT
                TD.ID
            FROM
                " . DB_TABLE_DOCUMENTS . " as TD ";
        if ($find_shareUser !== "" ||  $find_owner !== "")
            $sql .= "RIGHT JOIN tr_ca_docs_property as TDP ON TDP.DOCUMENT_ID = TD.ID ";
        if ($find_shareUser !== "" &&  $find_owner !== "")
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
        if ($find_shareUser !== "" &&  $find_owner !== ""){
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
     * @global object $DB Bitrix global CDatabase object
     * @param string $by Sort column
     * @param string $order Sort order
     * @param array $filter Array with filter keys and values
     * @return CDBResult
     */
    static function getUsersWithDocsByFilter($by, $order, $filter)
    {
        $find_user_id = (string)$filter["USER_ID"];
        $find_user_name = (string)$filter["USER_NAME"];
        $find_user_email = (string)$filter["USER_EMAIL"];
        $find_doc_name = (string)$filter["DOC_NAME"];
        $find_doc_type = (string)$filter["DOC_TYPE"];
        $find_doc_status = (string)$filter["DOC_STATUS"];

        $sqlWhere = array();
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
        $fields = array(
            "USER_ID" => "BU.ID",
            "USER_NAME" => "NAME",
        );
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

    /**
     * Returns all documents attached or shared with the user.
     * @param integer $userId
     * @param true $shared Include shared documents
     * @return DocumentCollection
     */
    static function getDocumentsByUser($userId, $shared = false)
    {
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
    static function getDocumentIdsByUser($userId)
    {
        $docs = Database::getDocumentsByUser($userId);
        $res = array();
        foreach ($docs->getList() as $doc) {
            $res[] = $doc->getId();
        }
        return $res;
    }

    /**
     * Returns array of order IDs with documents attached to them.
     * Requires 'sale' module.
     * @global object $DB Bitrix global CDatabase object
     * @return array
     */
    static function getOrders()
    {
        global $DB;
        $sql = "    SELECT VALUE
            FROM " . DB_TABLE_PROPERTY . " TDP, b_sale_order BO
            WHERE TDP.TYPE = 'ORDER' AND TDP.VALUE = BO.ID
            GROUP BY TYPE, VALUE";
        $rows = $DB->Query($sql);
        $res = array();
        while ($row = $rows->Fetch()) {
            $res[] = $row["VALUE"];
        }
        return $res;
    }

    /**
     * Returns order IDs with filter applied.
     * Requires 'sale' module.
     * @global object $DB Bitrix global CDatabase object
     * @param array $arOrder Sort direction
     * @param array $filter Filter array with keys and values
     * @return CDBResult
     */
    static function getOrdersByFilter($arOrder = array(), $filter)
    {
        // TODO: change $arOrder to separate $by and $order
        $arFields = array(
            'ORDER' => array(
                'FIELD_NAME' => 'OrderList.VALUE',
            ),
            'ORDER_STATUS' => array(
                'FIELD_NAME' => 'BO.STATUS_ID',
            ),
            'CLIENT_NAME' => array(
                'FIELD_NAME' => 'BU.NAME',
            ),
            'DOCS' => array(
                'FIELD_NAME' => 'OrderList.VALUE',
            ),
            'ORDER_EMAIL_STATUS' => array(
                'FIELD_NAME' => 'OrderList.EMAIL',
            ),
            'DOC_STATE' => array(
                'FIELD_NAME' => 'TDP.VALUE',
            ),
        );

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
    static function getIdsByOrder($order)
    {
        $docs = Database::getDocumentsByOrder($order);
        $list = $docs->getList();
        $ids = array();
        foreach ($list as &$doc) {
            $ids[] = $doc->getId();
        }
        return $ids;
    }

    /**
     * Returns DocumentCollection by order ID.
     * @global object $DB Bitrix global CDatabase object
     * @param string $order Order ID
     * @return DocumentCollection
     */
    static function getDocumentsByOrder($order)
    {
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

    static function getOrderByDocumentId($id)
    {
        global $DB;
        $sql = 'SELECT VALUE FROM ' . DB_TABLE_PROPERTY . ' '
            . 'WHERE '
            . 'DOCUMENT_ID = "' . $id . '" AND '
            . 'TYPE = "ORDER"';
        $rows = $DB->Query($sql);
        $orderId = $rows->Fetch();
        return $orderId;
    }

}

