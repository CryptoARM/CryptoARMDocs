<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/trusted/api/getUser.php";

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;

$coreIds = [
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
];
$module_id = 'not found';
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
    if (file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

Loader::includeModule($module_id);

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

header('Content-Type: application/json; charset=' . LANG_CHARSET);

function echoAndDie($answer) {
    echo json_encode($answer);
    die();
}

$userId = getUserIdByToken($_REQUEST["token"]);
$docsId["ids"] = json_decode($_REQUEST["ids"]);

if (!$docsId["ids"]) {
    $answer = [
        "code" => 960,
        "message" => "ids is not find",
        "data" => []
    ];
    return $answer;
}

if ($userId["code"]) {
    echoAndDie($userId);
}

global $USER;
$USER->Authorize($userId);

$data = [];
$response = Docs\AjaxCommand::remove($docsId);

$USER->Logout();

$docsNotFound = array_merge($response["docsNotFound"], $response["docsFileNotFound"]);
$docsNoAccess = $response["docsNoAccess"];
$docsBlocked = $response["docsBlocked"];
$docsOk = $response["docsOk"];

if ($docsNotFound) {
    foreach ($docsNotFound as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 902,
            "message" => "document does not exist",
        ];
    }
}

if ($docsNoAccess) {
    foreach ($docsNoAccess as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 901,
            "message" => "have not permission",
        ];
    }
}
if ($docsBlocked) {
    foreach ($docsBlocked as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 911,
            "message" => "document is blocked",
        ];
    }
}
if ($docsOk) {
    foreach ($docsOk as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 900,
            "message" => "ok",
        ];
    }
}

$answer = [
    "code" => 200,
    "message" => "ok",
    "date" => $data
];

echoAndDie($answer);
