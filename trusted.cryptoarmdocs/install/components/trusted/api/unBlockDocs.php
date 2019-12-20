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
$docsId = json_decode($_REQUEST["ids"]);

if (!$docsId) {
    $answer = [
        "code" => 908,
        "message" => "ids is not find",
        "data" => []
    ];
    return $answer;
}

if ($userId["code"]) {
    echoAndDie($userId);
}

$response = Docs\Utils::checkDocuments($docsId, null, true);
$docsNotFound = array_merge($response["docsNotFound"], $response["docsFileNotFound"]);
$docsNoAccess = $response["docsNoAccess"];
$docsBlocked = $response["docsBlocked"];
$docsOk = $response["docsOk"];

global $USER;
$USER->Authorize($userId);

$data = [];
$response = Docs\AjaxCommand::unblock($docsBlocked);

$USER->Logout();

if (!$response["success"]) {
    $answer = [
        "code" => 909,
        "message" => "something wrong",
        "date" => []
    ];
    echoAndDie($answer);
}

if ($docsNotFound) {
    foreach ($docsNotFound as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 902,
            "message" => "document does not exist",
        ];
    }
}

if ($docsBlocked) {
    foreach ($docsBlocked as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 200,
            "message" => "ublocked",
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

if ($docsOk) {
    foreach ($docsOk as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 903,
            "message" => "already unblocked",
        ];
    }
}

$answer = [
    "code" => 200,
    "message" => "ok",
    "data" => $data
];

echoAndDie($answer);
