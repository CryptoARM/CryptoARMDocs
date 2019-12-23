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
$emailAddress = $_REQUEST["email"];

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

if (!$emailAddress) {
    $answer = [
        "code" => 907,
        "message" => "email is not find",
        "data" => []
    ];
    echoAndDie($answer);
}

if (!Docs\Utils::validateEmailAddress($emailAddress)) {
    $answer = [
        "code" => 950,
        "message" => "email is not valid",
        "data" => []
    ];
    echoAndDie($answer);
}

if (!Docs\Utils::getUserIdByEmail($emailAddress)) {
    $answer = [
        "code" => 906,
        "message" => "user is not find",
        "data" => []
    ];
    echoAndDie($answer);
}

global $USER;
$USER->Authorize($userId);

$response = Docs\Utils::checkDocuments($docsId, null, false);
$docsNotFound = array_merge($response["docsNotFound"], $response["docsFileNotFound"]->toArray());
$docsNoAccess = $response["docsNoAccess"];
$docsBlocked = $response["docsBlocked"]->toArray();
$docsOk = $response["docsOk"]->toArray();

$ids = [];

foreach ($docsOk as $doc) {
    $ids[] = $doc["id"];
}

foreach ($docsBlocked as $doc) {
    $ids[] = $doc["id"];
}

$data = [];
$params = [
    "email" => $emailAddress,
    "ids" => $ids,
];

$response = [];

if (!empty($ids)) {
    $response = Docs\AjaxCommand::requireToSign($params);
}

$USER->Logout();

if (!empty($response) && !$response["success"]) {
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
