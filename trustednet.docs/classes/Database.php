<?php
namespace TrustedNet\Docs;

require_once __DIR__ . "/../config.php";

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
            if (is_null($parentId)) {
                $parentId = 'NULL';
            }
            if (is_null($childId)) {
                $childId = 'NULL';
            }
            $sql = 'UPDATE ' . DB_TABLE_DOCUMENTS . ' SET '
                . 'NAME = "' . $DB->ForSql($doc->getName()) . '", '
                . 'PATH = "' . $doc->getPath() . '", '
                . 'TYPE = ' . $doc->getType() . ', '
                . 'STATUS = ' . $doc->getStatus() . ', '
                . "SIGNERS = '" . $DB->ForSql($doc->getSigners()) . "', "
                . 'PARENT_ID = ' . $parentId . ', '
                . 'CHILD_ID = ' . $childId . ' '
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
            . '(NAME, PATH, SIGNERS, TYPE, PARENT_ID, CHILD_ID)'
            . 'VALUES ('
            . '"' . $DB->ForSql($doc->getName()) . '", '
            . '"' . $doc->getPath() . '", '
            . "'" . $DB->ForSql($doc->getSigners()) . "', "
            . $doc->getType() . ', '
            . $parentId . ', '
            . $childId
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
        // TODO: fuzzy finding
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_DOCUMENTS
            . ' WHERE NAME = "' . $DB->ForSql($name) . '"'
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
     * @param integer $documentId Documend ID
     * @param string $tableName DB table name
     * @return PropertyCollection
     */
    static function getPropertiesByDocumentId($documentId, $tableName = DB_TABLE_PROPERTY)
    {
        return Database::getPropertiesBy('DOCUMENT_ID', $documentId, $tableName);
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
            'SIGN' => array(
                'FIELD_NAME' => 'TD.SIGNERS',
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
        $find_signInfo = (string)$filter['SIGN'];
        $find_type = (string)$filter['TYPE'];
        $find_status = (string)$filter['STATUS'];

        global $DB;
        $sql = "
            SELECT
                TD.ID
            FROM
                " . DB_TABLE_DOCUMENTS . " TD
            WHERE
                isnull(TD.CHILD_ID)";
        if ($find_docId !== "")
            $sql .= " AND TD.ID = '" . $find_docId . "'";
        if ($find_fileName !== "")
            $sql .= " AND TD.NAME LIKE '%" . $find_fileName . "%'";
        if ($find_signInfo !== "")
            $sql .= " AND TD.SIGNERS LIKE '%" . $DB->ForSql($find_signInfo) . "%'";
        if ($find_type !== "")
            $sql .= " AND TD.TYPE = " . $find_type;
        if ($find_status !== "")
            $sql .= " AND TD.STATUS = " . $find_status;

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
        $find_doc_id = (string)$filter["DOC_ID"];
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
        if ($find_doc_id !== "") {
            $sqlWhere[] = "TD.ID = '" . $find_doc_id . "'";
        }
        if ($find_doc_name !== "") {
            $sqlWhere[] = "TD.NAME LIKE '%" . $find_doc_name . "%'";
        }
        if ($find_doc_type !== "") {
            $sqlWhere[] = "TD.TYPE LIKE '%" . $find_doc_type . "%'";
        }
        if ($find_doc_status !== "") {
            $sqlWhere[] = "TD.STATUS LIKE '%" . $find_doc_status . "%'";
        }

        global $DB;
        $sql = "
            SELECT
                BU.ID, BU.LOGIN, CONCAT(BU.NAME, ' ', BU.LAST_NAME) as NAME, BU.EMAIL
            FROM
                b_user as BU,
                trn_docs as TD,
                trn_docs_property as TDP
            WHERE
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
     * Returns all documents attached to the user.
     * @param integer $userId
     * @return DocumentCollection
     */
    static function getDocumentsByUser($userId)
    {
        global $DB;
        $sql = "
            SELECT
                TD.ID
            FROM
                trn_docs as TD,
                trn_docs_property as TDP
            WHERE
                TD.ID = TDP.DOCUMENT_ID AND
                isnull(TD.CHILD_ID) AND
                TDP.TYPE = 'USER' AND
                TDP.VALUE = '" . (int)$userId . "';";
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
            'DOC_STATE' => array(
                'FIELD_NAME' => 'TDP.VALUE',
            ),
        );

        $find_order = (string)$filter['ORDER'];
        $find_order_status = (string)$filter['ORDER_STATUS'];
        $find_clientEmail = (string)$filter['CLIENT_EMAIL'];
        $find_clientName = (string)$filter['CLIENT_NAME'];
        $find_clientLastName = (string)$filter['CLIENT_LASTNAME'];
        $find_docState = (string)$filter['DOC_STATE'];

        global $DB;
        $sql = "
    SELECT
        OrderList.VALUE as `ORDER`
    FROM
        " . DB_TABLE_DOCUMENTS . " TD,
        " . DB_TABLE_PROPERTY . " TDP,
        (SELECT * FROM " . DB_TABLE_PROPERTY . " WHERE TYPE = 'ORDER') as OrderList,
        b_sale_order BO,
        b_user BU
    WHERE
        BO.USER_ID = BU.ID
        AND BO.ID = OrderList.VALUE
        AND TD.ID = TDP.DOCUMENT_ID
        AND TD.ID = OrderList.DOCUMENT_ID
        AND TDP.TYPE = 'ORDER'
        AND isnull(TD.CHILD_ID)";
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

