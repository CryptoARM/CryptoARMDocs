<?php

require_once __DIR__ . "/../config.php";

/**
 * Class: TDataBaseDocument
 * DB interaction class
 */
class TDataBaseDocument
{

    /**
     * Return collection of all last documents.
     * Last documents in the chain have empty CHILD_ID field.
     * @global object $DB
     * @return object DocumentCollection
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
     * Returns MySQL object with documents filtered by specified filter
     * @global object $DB
     * @param array $arOrder
     * @param array $filter Array with filter keys and values
     * @return object CDBResult
     */
    static function getIdDocumentsByFilter($arOrder = array(), $filter)
    {
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

        $find_docId = $filter['DOC'];
        $find_fileName = $filter['FILE_NAME'];
        $find_signInfo = $filter['SIGN'];
        $find_type = $filter['TYPE'];
        $find_status = $filter['STATUS'];

        global $DB;
        $sql = "
            SELECT
                TD.ID
            FROM
                " . DB_TABLE_DOCUMENTS . " TD
            WHERE
                isnull(TD.CHILD_ID)";
        if ($find_docId)
            $sql .= " AND TD.ID = " . $find_docId;
        if ($find_fileName)
            $sql .= " AND TD.NAME LIKE '%" . $find_fileName . "%'";
        if ($find_signInfo)
            $sql .= " AND TD.SIGNERS LIKE '%" . CDatabase::ForSql($find_signInfo) . "%'";
        if ($find_type != "")
            $sql .= " AND TD.TYPE = " . $find_type;
        if ($find_status != "")
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
     * Saves document in DB. If the document doesn't have an id
     * creates new record for it
     * @global object $DB
     * @param object Document
     * @return void
     */
    static function saveDocument($doc)
    {
        if ($doc->getId() == null) {
            TDataBaseDocument::insertDocument($doc);
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
                . 'NAME = "' . CDatabase::ForSql($doc->getName()) . '", '
                . 'PATH = "' . $doc->getPath() . '", '
                . 'TYPE = ' . $doc->getType() . ', '
                . 'STATUS = ' . $doc->getStatus() . ', '
                . "SIGNERS = '" . CDatabase::ForSql($doc->getSigners()) . "', "
                . 'PARENT_ID = ' . $parentId . ', '
                . 'CHILD_ID = ' . $childId . ' '
                . 'WHERE ID = ' . $doc->getId();
            $DB->Query($sql);
            TDataBaseDocument::saveDocumentParent($doc, $doc->getId());
        }
    }

    /**
     * Adds new document in DB
     * @global object $DB
     * @param object Document
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
            . '"' . CDatabase::ForSql($doc->getName()) . '", '
            . '"' . $doc->getPath() . '", '
            . "'" . CDatabase::ForSql($doc->getSigners()) . "', "
            . $doc->getType() . ', '
            . $parentId . ', '
            . $childId
            . ')';
        $DB->Query($sql);
        $doc->setId($DB->LastID());
        TDataBaseDocument::saveDocumentParent($doc, $doc->getId());
    }

    /**
     * Updates document parent with child id
     * @param object $doc Parent document
     * @param intger $id Document id. Default NULL
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
     * Removes document from DB
     * @global object $DB
     * @param object Document $doc
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
        TDataBaseDocument::saveDocumentParent($doc);
    }

    /**
     * Removes document and all of its parents from DB
     * @global object $DB
     * @param object Document $doc
     * @return void
     */
    static function removeDocumentRecursively(&$doc)
    {
        global $DB;
        $parent = null;
        if ($doc->getParent()) {
            $parent = $doc->getParent();
        }
        TDataBaseDocument::removeDocument($doc);
        if ($parent) {
            TDataBaseDocument::removeDocumentRecursively($parent);
        }
    }

    /**
     * Get document from DB by id
     * @global object $DB
     * @param integer $id Document id
     * @return object Document
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
     * Returns collection of last documents by name
     * @global object $DB
     * @param string $name
     * @return object DocumentCollection
     */
    static function getDocumentsByName($name)
    {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_DOCUMENTS
            . ' WHERE NAME = "' . CDatabase::ForSql($name) . '"'
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
     * If property id is null creates new record
     * @global object $DB
     * @param object Property $property
     * @param string $tableName
     * @return void
     */
    static function saveProperty($property, $tableName)
    {
        if ($property->getId() == null) {
            TDataBaseDocument::insertProperty($property, $tableName);
        } else {
            global $DB;
            $sql = 'UPDATE ' . $tableName .
                ' SET DOCUMENT_ID = ' . $property->getDocumentId() . ',
                      TYPE="' . CDatabase::ForSql($property->getType()) . '",
                      VALUE="' . CDatabase::ForSql($property->getValue()) . '"
                WHERE ID = ' . $property->getId();
            $DB->Query($sql);
        }
    }

    /**
     * Adds new property to DB
     * @global object $DB
     * @param object Property $property
     * @param string $tableName
     * @return void
     */
    static function insertProperty($property, $tableName)
    {
        global $DB;
        $sql = 'INSERT INTO ' . $tableName .
              ' (DOCUMENT_ID, TYPE, VALUE)
                VALUES (' .
                    $property->getDocumentId() . ', "' .
                    CDatabase::ForSql($property->getType()) . '", "' .
                    CDatabase::ForSql($property->getValue()) . '")';
        $DB->Query($sql);
        $property->setId($DB->LastID());
    }

    /**
     * Gets property collection from DB by specified type and value fields
     * @global object $DB
     * @param string $tableName
     * @param string $type TYPE field
     * @param string $value VALUE field
     * @return object PropertyCollection
     */
    static function getPropertiesByTypeAndValue($tableName, $type, $value)
    {
        global $DB;
        $sql = 'SELECT * FROM ' . $tableName .
            ' WHERE TYPE = "' . CDatabase::ForSql($type) .
            '" AND VALUE = "' . CDatabase::ForSql($value) . '"';
        $rows = $DB->Query($sql);
        $res = new PropertyCollection();
        while ($array = $rows->Fetch()) {
            $res->add(Property::fromArray($array));
        }
        return $res;
    }

    /**
     * Get single property from DB by specified field
     * @global object $DB
     * @param string $tableName
     * @param string $fldName
     * @param string $value
     * @return object Property
     */
    static function getPropertyBy($tableName, $fldName, $value)
    {
        $props = TDataBaseDocument::getPropertiesBy($tableName, $fldName, $value);
        $res = null;
        if ($props->count()) {
            $res = $props->items(0);
        }
        return $res;
    }

    /**
     * Gets property collection from DB by specified field
     * @global object $DB
     * @param string $tableName
     * @param string $fldName
     * @param string $value
     * @return object PropertyCollection
     */
    static function getPropertiesBy($tableName, $fldName, $value)
    {
        global $DB;
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE  ' . $fldName . ' = "' . CDatabase::ForSql($value) . '"';
        $rows = $DB->Query($sql);
        $res = new PropertyCollection();
        while ($array = $rows->Fetch()) {
            $res->add(Property::fromArray($array));
        }
        return $res;
    }

    /**
     * Gets property collection from DB by document id
     * @param string $tableName
     * @param int $parentId
     * @return object PropertyCollection
     */
    static function getPropertiesByDocumentId($tableName, $parentId)
    {
        return TDataBaseDocument::getPropertiesBy($tableName, 'DOCUMENT_ID', $parentId);
    }

}

