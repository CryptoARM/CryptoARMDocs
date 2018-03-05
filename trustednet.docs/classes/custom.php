<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

//CModule::IncludeModule('sale');

function debug($var, $name = "VAR")
{
    $myfile = fopen($_SERVER["DOCUMENT_ROOT"] . "/log.txt", "a"); $logtime = date("d-m-Y H:i:s", time());
    fwrite($myfile, "$logtime"."\n" . $name . ":\n_START_\n".print_r($var, true) . "\n_END_\n\n");
    fclose($myfile);
}

function getErrorMessageFromResponse($response, $errCode, $errMessage)
{
    $message = $errMessage;
    if (!is_null($response)) {
        $message = isset($response["message"]) ? $response["message"] : $response["error"];
    }
    if (!is_null($response) or $errCode) {
        $respCode = isset($response["code"]) ? $response["code"] : $errCode;
        switch ($respCode) {
            // Unauthorized
            case 1: {
                $message = GetMessage("TN_DOCS_RESP_NO_AUTH");
                break;
            }
            // Client not connected
            case 100: {
                $message = GetMessage("TN_DOCS_RESP_NO_CONNECTION");
                break;
            }
            case 101: {
                break;
            }
            default:
                break;
        }
    }
    if (stristr($message, "Service balance exhausted")) $message = GetMessage("TN_DOCS_RESP_LIMIT_EXHAUSTED");
    if (stristr($message, "Unknown client:")) $message = GetMessage("TN_DOCS_RESP_NO_APP");
    return $message;
}

/**
 * Filter for documents that don't need to be signed.
 * Based on their STATUS property and EXTRA argument
 * to the sign function.
 * @param \Document $doc
 * @param $extra
 */
function checkDocByExtra($doc, $extra)
{
    $status = $doc->getProperties()->getItemByType("STATUS");
    if (!$status) {
        return true;
    }
    $statusValue = $status->getValue();
    if ($extra == "CLIENT") {
        if ($statusValue == "SELLER" || $statusValue == "NONE") {
            return true;
        } else {
            return false;
        }
    } elseif ($extra == "SELLER") {
        if ($statusValue == "CLIENT" || $statusValue == "NONE") {
            return true;
        } else {
            return false;
        }
    }
    return true;
}

function updateDocumentStatus($doc, $params)
{
    $params->setLogo($LOGO);
    $params->setCss($CSS);
}

function viewSignature($doc, $params)
{
    $params->setLogo($LOGO);
    $params->setCss($CSS);
}

function throwError($msg)
{
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(array("success" => false, "message" => $msg));
    die();
}

function checkUser($doc, $USER)
{
    if (!$USER->IsAdmin()) {
        $props = $doc->getProperties();
        if (!$props) {
            throwError("Document doesn't have properties");
        }
        $order = $props->getItemByType("ORDER");
        if ($order) {
            $ORDER_ID = $order->getValue();
            $bxOrder = CSaleOrder::GetById($ORDER_ID);
            if (!$bxOrder) {
                throwError("Bitrix doesn't have order number " . $ORDER_ID);
            }
            $bxUSER_ID = $bxOrder["USER_ID"];
            if ($bxUSER_ID != $USER->GetID()) {
                throwError("User doesn't have permitions to work with file");
            }
        } else {
            throwError("Document doesn't have ORDER property");
        }
    }
}

/**
 *
 * @global type $USER
 * @param \Document $doc
 * @param type $accessToken
 */
function getPermision($doc, $refreshToken = null)
{
    // TODO: NO CHECK
    return true;
    global $USER;

    if ($USER->IsAuthorized()) {
        checkUser($doc, $USER);
    } else {
        try {
            if (!$refreshToken) {
                throwError("User is not authorized");
            }
            $accessToken = null;

            try {
                $accessToken = TAuthCommand::getAccessTokenByRefreshToken($refreshToken);
                $accessToken = $accessToken["access_token"];
            } catch (OAuth2Exception $ex) {
                throwError($ex->message);
            }

            $user = TAuthCommand::getUserProfileByToken($accessToken);
            $tUser = TDataBaseUser::getUserById($user["entityId"]);
            if ($tUser) {
                // User exists
                $USER_ID = $tUser->getUserId();
                $USER->Authorize($USER_ID);
                checkUser($doc, $USER);
            } else {
                throwError("Bitrix doesnt't have TrustedNET client");
            }
        } catch (OAuth2Exception $ex) {
            throwError($ex->message);
        }
    }
    return true;
}

/**
 *
 * @param \Document $doc
 */
function getContent($doc, $token)
{
    $res = getPermision($doc, $token);
    return $res;
}

/**
 *
 * @param \Document $doc
 * @param type $accessToken
 * @return boolean
 */
function beforeUploadSignature($doc, $token)
{
    $res = getPermision($doc, $token);
    return $res;
}

/**
 *
 * @param \Document $doc
 * @param mixed $file
 */
function uploadSignature($doc, $file, $extra = null)
{
    if ($doc->getParent()->getType() == DOC_TYPE_FILE) {
        $doc->setName($doc->getName() . '.sig');
        $doc->setPath($doc->getPath() . '.sig');
    }
    $role = $extra["role"];
    $props = $doc->getProperties();
    $statusProp = $props->getItemByType("ROLES");
    if ($statusProp) {
        if ($statusProp->getValue() == "CLIENT" && $role == "SELLER") {
            $statusProp->setValue("BOTH");
        }
        if ($statusProp->getValue() == "SELLER" && $role == "CLIENT") {
            $statusProp->setValue("BOTH");
        }
        if ($statusProp->getValue() == "NONE") {
            if ($extra) {
                $statusProp->setValue($role);
            }
        }
    }
    copy($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/' . rawurldecode($doc->getPath()));
}

class TSignUtils
{

    /**
     * Creates new document
     * @param string $file Path to file
     * @param boolean $copy If true copies the document into module folder
     * @param string $propertyType User-set property (ORDER)
     * @param string $propertyValue User-set property value (order number)
     * @param string $type File type. By default DOC_TYPE_FILE
     * @return \Document
     */
    public static function createDocument($file, $copy, $propertyType = null, $propertyValue = null, $type = DOC_TYPE_FILE)
    {
        $name = TrustedDirectory::getFileName($file);
        if ($copy && TrustedDirectory::exists($file) && !is_dir($file)) {
            $order_folder = TRUSTED_PROJECT_UPLOAD . '/' . $propertyType;
            $order_local_folder = $_SERVER['DOCUMENT_ROOT']. '/' . $order_folder;
            if (!TrustedDirectory::exists($order_local_folder)) {
                TrustedDirectory::create($order_folder);
            }
            $new_path = $order_local_folder . '/' . $name;

            copy($file, $new_path);
            unlink($file);
            $file = $new_path;
        }
        $doc = new Document();
        $doc->setPath(str_replace($name, rawurlencode($name), $file));
        $doc->setName($name);
        $doc->setType($type);
        $docId = $doc->getId();
        $props = $doc->getProperties();
        if ($propertyType) {
            $props->add(new Property($docId, $propertyType, $propertyValue));
            // Documents by order need an additional parameter
            if ($propertyType == "ORDER") {
                $props->add(new Property($docId, "ROLES", "NONE"));
            }
        }
        $doc->save();

        return $doc;
    }

}

class TrustedDirectory
{

    protected $path;

    public static function getFileName($path)
    {
        $dirs = explode("/", $path);
        $len = count($dirs);
        return $dirs[--$len];
    }

    public static function create($path, $cb = null)
    {
        $dirs = explode("/", $path);

        $pos = $_SERVER['DOCUMENT_ROOT'];
        $created = false;
        foreach ($dirs as &$dir) {
            $pos .= '/' . $dir;
            if (!TrustedDirectory::exists($path)) {
                mkdir($pos, 0777);
                $created = true;
            }
        }
        $res = TrustedDirectory::open($path);
        if ($created && isset($cb)) {
            $cb($res);
        }
        return $res;
    }

    public static function exists($path)
    {
        if (file_exists($path)) {
            return true;
        } else {
            return false;
        }
    }

    public static function open($path)
    {
        $res = null;
        if (TrustedDirectory::exists(TrustedDirectory::getLocalRoot() . '/' . $path)) {
            $res = new TrustedDirectory();
            $res->path = $path;
        }
        return $res;
    }

    public static function getLocalRoot()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getSystemPath()
    {
        return TrustedDirectory::getLocalRoot() . '/' . $this->path;
    }

    public function getHttpPath()
    {
        return TrustedDirectory::getHttpRoot() . '/' . $this->path;
    }

    public static function getHttpRoot()
    {
        return TRUSTED_PROJECT_HOST;
    }

    public function remove($cb = null)
    {
        unlink($this->path);
        if (isset($cb)) {
            $cb();
        }
    }

}

/**
 * Return array of order ids
 * @global type $DB
 * @return mixed
 */
function getOrders()
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
 * Returns mysqli_result Object with order ids with filter applied
 * @global type $DB
 * @param array $arOrder
 * @param type $filter
 * @return mysqli_result Object
 */
function getOrdersByFilter($arOrder = array(), $filter)
{

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

    $find_order = $filter['ORDER'];
    $find_order_status = $filter['ORDER_STATUS'];
    $find_clientEmail = $filter['CLIENT_EMAIL'];
    $find_clientName = $filter['CLIENT_NAME'];
    $find_clientLastName = $filter['CLIENT_LASTNAME'];
    $find_docState = $filter['DOC_STATE'];

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
    if ($find_order)
        $sql .= " AND OrderList.VALUE = " . $find_order;
    if ($find_order_status)
        $sql .= " AND BO.STATUS_ID = '" . $find_order_status . "'";
    if ($find_clientName)
        $sql .= " AND BU.NAME LIKE '%" . $find_clientName . "%'";
    if ($find_clientLastName)
        $sql .= " AND BU.LAST_NAME LIKE '%" . $find_clientLastName . "%'";
    if ($find_clientEmail)
        $sql .= " AND BU.EMAIL LIKE '%" . $find_clientEmail . "%'";
    if ($find_docState)
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
 * Return array of document ids by the order id
 * @param string $order
 * @return Array
 */
function getIdsByOrder($order)
{
    $docs = getDocumentsByOrder($order);
    $list = $docs->getList();
    $ids = array();
    foreach ($list as &$doc) {
        $ids[] = $doc->getId();
    }
    return $ids;
}

/**
 * Returns DocumentCollection by order id
 * @global type $DB
 * @param string $order
 * @return \DocumentCollection
 */
function getDocumentsByOrder($order)
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
 * @param $ids
 * @return mixed
 */

function getOrderByDocunent($ids)
{
    global $DB;
    $sql = 'SELECT VALUE FROM ' . DB_TABLE_PROPERTY . ' '
        . 'WHERE '
        . 'DOCUMENT_ID = "' . $ids . '" AND '
        . 'TYPE = "ORDER"';
    $rows = $DB->Query($sql);
    $orderId = $rows->Fetch();
    return $orderId;
}

function getStateString($doc)
{
    $state = $doc->getProperties()->getItemByType("ROLES");
    $str = "";
    if ($state) {
        $state_value = $state->getValue();
        switch ($state_value) {
            case "CLIENT":
                $str = GetMessage("ROLES_CLIENT");
                break;
            case "SELLER":
                $str = GetMessage("ROLES_SELLER");
                break;
            case "BOTH":
                $str = GetMessage("ROLES_BOTH");
                break;
            case "NONE":
                $str = GetMessage("ROLES_NONE");
                break;
            default:
        }
    } else {
        $str = GetMessage("ROLES_NONE");
    }
    return $str;
}

function checkStatus($oid)
{
    $docs = getDocumentsByOrder($oid);
    $list = $docs->getList();
    $res = false;
    foreach ($list as &$doc) {
        $res = true;
        if ($doc->getStatus() && $doc->getStatus()->getValue() == DOC_STATUS_BLOCKED) {
            $res = false;
            break;
        }
    }
    return $res;
}

if (isset($_POST)) {
    switch ($_POST['action']) {
        case 'getListDocsByOrder':
            $oid = $_POST["oid"];
            $docs = getDocumentsByOrder($oid);
            $list = $docs->getList();
            $ids = showListDocs($list);
            break;
        case 'checkStatus':
            $res = checkStatus($_POST["oid"]);
            if (!$res) {
                header("HTTP/1.1 500 Internal Server Error");
                echo "Our error";
            } else {
                $docs = getDocumentsByOrder($oid);
                $list = $docs->getList();
                $ids = showListDocs($list);
            }
            break;
    }
}

