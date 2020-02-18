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
$signType = $_REQUEST["signType"];

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

if (is_null($signType) || !in_array($signType, [0,1])) {
    $answer = [
        "code" => 970,
        "message" => "signType is not correct",
        "data" => []
    ];
    echoAndDie($answer);
}

$checkDocs = Docs\Utils::checkDocuments($docsId, DOC_SHARE_SIGN, false, true, $signType);
$docsNotFound = array_merge($checkDocs["docsNotFound"], $checkDocs["docsFileNotFound"]->toArray());
$docsNoAccess = $checkDocs["docsNoAccess"];
$docsBlocked = $checkDocs["docsBlocked"]->toArray();
$docsOk = $checkDocs["docsOk"]->toArray();
$docsWrongSignType = $checkDocs["docsWrongSignType"];

if ($docsNotFound) {
    foreach ($docsNotFound as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 902,
            "message" => "document does not exist",
            "name" => null,
            "hash" => null,
            "token" => null,
            "license" => null,
            "url" => null,
            "signUrl" => null,
        ];
    }
}

if ($docsWrongSignType) {
    foreach ($docsWrongSignType as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 970,
            "message" => "signType is not correct",
            "name" => null,
            "hash" => null,
            "token" => null,
            "license" => null,
            "url" => null,
            "signUrl" => null,
        ];
    }
}

if ($docsNoAccess) {
    foreach ($docsNoAccess as $docId) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 901,
            "message" => "have not permission",
            "name" => null,
            "hash" => null,
            "token" => null,
            "license" => null,
            "url" => null,
            "signUrl" => null,
        ];
    }
}

if ($docsBlocked) {
    foreach ($docsBlocked as $docId) {
        $data[$docId["id"]] = [
            "id" => $docId["id"],
            "code" => 911,
            "message" => "document is blocked",
            "name" => null,
            "hash" => null,
            "token" => null,
            "license" => null,
            "url" => null,
            "signUrl" => null,
        ];
    }
}

if ($docsOk) {
    foreach ($docsOk as $docId) {
        $doc = Docs\Database::getDocumentById($docId["id"]);
        $token = Docs\Utils::generateUUID();

        if (PROVIDE_LICENSE) {
            $license = Docs\License::getOneTimeLicense();
            if (!$license['success']) {
                $licenseKey = null;
            } else {
                $licenseKey = $license['data'];
            }
        }

        if ($doc->getSignType() === DOC_SIGN_TYPE_DETACHED) {
            $originalDoc = Docs\Database::getDocumentById($doc->getOriginalId());
            $originalDocUrl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] . $originalDoc->getHtmlPath();
            $signUrl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] . $doc->getHtmlPath();
        } else {
            $originalDocUrl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] . $doc->getHtmlPath();
            $signUrl = null;
        }

        $data[$docId["id"]] = [
            "id" => $doc->getId(),
            "code" => 900,
            "message" => "ok",
            "name" => $doc->getName(),
            "hash" => $doc->getHash(),
            "token" => $token,
            "license" => $licenseKey,
            "url" => $originalDocUrl,
            "signUrl" => $signUrl,
        ];

        $doc->block($token);
        $doc->save();
    }
}

$USER->Logout();

$answer = [
    "code" => 200,
    "message" => "ok",
    "data" => $data
];

echoAndDie($answer);
