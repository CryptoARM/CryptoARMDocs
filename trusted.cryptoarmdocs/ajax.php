<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

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
Loader::includeModule($module_id);
Loader::includeModule("trusted.cryptoarmdocsforms");

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

header('Content-Type: application/json; charset=' . LANG_CHARSET);

// AJAX Controller

$command = $_GET['command'];
if (isset($command)) {
    $params = $_POST;
    switch ($command) {
        case "share":
            $res = Docs\AjaxCommand::share($params);
            break;
        case "sendEmail":
            $res = Docs\AjaxCommand::sendEmail($params);
            break;
        case "activateJwtToken":
            $res = Docs\AjaxCommand::activateJwtToken($params);
            break;
        case "registerAccountNumber":
            $res = Docs\AjaxCommand::registerAccountNumber();
            break;
        case "checkAccountBalance":
            $res = Docs\AjaxCommand::checkAccountBalance($params);
            break;
        case "getAccountHistory":
            $res = Docs\AjaxCommand::getAccountHistory($params);
            break;
        case "uploadFile":
            $res = Docs\AjaxCommand::uploadFile($params);
            break;
        case "upload":
            $res = Docs\AjaxCommand::upload($params);
            break;
        case "verify":
            $res = Docs\AjaxCommand::verify($params);
            break;
        case "unblock":
            $res = Docs\AjaxCommand::unblock($params);
            break;
        case "remove":
            $res = Docs\AjaxCommand::remove($params);
            break;
        case "download":
            $res = Docs\AjaxCommand::download($_REQUEST);
            break;
        case "content":
            $res = Docs\AjaxCommand::content($_REQUEST);
            return $res;
            break;
        case "protocol":
            $res = Docs\AjaxCommand::protocol($_GET);
            break;
        case "check":
            $res = Docs\AjaxCommand::check($params);
            break;
        case "blockCheck":
            $res = Docs\AjaxCommand::blockCheck($params);
            break;
        case "unshare":
            $res = Docs\AjaxCommand::unshare($params);
            break;
        case "removeForm":
            $res = Docs\Form::removeIBlockAndDocs($params);
            break;
        case "requireToSign":
            $res = Docs\AjaxCommand::requireToSign($params);
            break;
        case "JSON":
            $postData = file_get_contents('php://input');
            $params = json_decode($postData, true);
            if ($params["method"] === "signAndEncrypt.parameters") {
                $res = Docs\AjaxCommand::generateJson($_REQUEST);
            } else {
                $res = Docs\AjaxCommand::upload($params);
            }
            break;
        case "createTransaction":
            $res = Docs\AjaxCommand::createTransaction($params);
            break;
        case "getInfoForModalWindow":
            $res = Docs\AjaxCommand::getInfoForModalWindow($_REQUEST);
            break;
        case "getTransactionUrlByToken":
            $res = Docs\AjaxCommand::getTransactionUrlByToken($_REQUEST);
            break;
        default:
            $res = array("success" => false, "message" => "Unknown command '" . $command . "'");
    }
} else {
    $res = ["success" => false, "message" => "Command is not found"];
}
echo json_encode($res);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

