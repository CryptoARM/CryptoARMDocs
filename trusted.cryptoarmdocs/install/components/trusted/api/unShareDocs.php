<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/trusted/api/getUser.php";

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;

$coreId = 'trusted.cryptoarmdocsfree';
$module_id = 'not found';

$corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
if (file_exists($corePathDir)) {
    $module_id = $coreId;
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

switch ($_REQUEST["grandType"]) {
    case "token":
        $userId = getUserIdByToken($_REQUEST["token"]);
        break;
    case "password":
        $userId = getUserIdByLoginAndPass($_REQUEST["login"], $_REQUEST["password"]);
        break;
    default:
        $answer = [
            "code" => 820,
            "message" => "grandType is not correct",
            "data" => []
        ];
        echoAndDie($answer);
}

$docsId = json_decode($_REQUEST["ids"]);

if ($userId["code"]) {
    echoAndDie($userId);
}

if (!$docsId) {
    $answer = [
        "code" => 908,
        "message" => "ids is not find",
        "data" => []
    ];
    echoAndDie($answer);
}

global $USER;
$USER->Authorize($userId);

$checkDocs = Docs\Utils::checkDocuments($docsId, DOC_SHARE_READ, false);
$docsNotFound = array_merge($checkDocs["docsNotFound"], $checkDocs["docsFileNotFound"]->toArray());
$docsNoAccess = $checkDocs["docsNoAccess"];
$docsBlocked = $checkDocs["docsBlocked"]->toArray();
$docsOk = $checkDocs["docsOk"]->toArray();

$ids = [];
$data = [];
$response = [];

foreach ($docsOk as $doc) {
    $ids[] = $doc["id"];
}

foreach ($docsBlocked as $doc) {
    $ids[] = $doc["id"];
}

$params = [
    "docIds" => $ids
];

if (!empty($ids)) {
    $response = Docs\AjaxCommand::unshare($params);
}

$USER->Logout();

if (!empty($response) && !$response["success"]) {
    $answer = [
        "code" => 909,
        "message" => "something wrong",
        "data" => []
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
        $data[$docId["id"]] = [
            "id" => $docId["id"],
            "code" => 900,
            "message" => "ok",
        ];
    }
}

if ($docsOk) {
    foreach ($docsOk as $docId) {
        $data[$docId["id"]] = [
            "id" => $docId["id"],
            "code" => 900,
            "message" => "ok",
        ];
    }
}

$answer = [
    "code" => 200,
    "message" => "ok",
    "data" => $data
];

echoAndDie($answer);
