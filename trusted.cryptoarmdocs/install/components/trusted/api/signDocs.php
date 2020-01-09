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

$response = Docs\Utils::checkDocuments($docsId, DOC_SHARE_SIGN, true);
$docsNotFound = array_merge($response["docsNotFound"], $response["docsFileNotFound"]->toArray());
$docsNoAccess = $response["docsNoAccess"];
$docsBlocked = $response["docsBlocked"]->toArray();
$docsOk = $response["docsOk"]->toArray();

$USER->Logout();

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
        ];
    }
}

if ($docsOk) {
    foreach ($docsOk as $docId) {
        $doc = Docs\Database::getDocumentById($docId);
        $token = Docs\Utils::generateUUID();

        if (PROVIDE_LICENSE) {
            $license = Docs\License::getOneTimeLicense();
            if (!$license['success']) {
                $licenseKey = null;
            } else {
                $licenseKey = $license['data'];
            }
        }

        $url = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] . $doc->getHtmlPath();

        $data[$docId["id"]] = [
            "id" => $doc->getId(),
            "code" => 900,
            "message" => "ok",
            "name" => $doc->getName(),
            "hash" => $doc->getHash(),
            "token" => $token,
            "license" => $licenseKey,
            "url" => $url,
        ];

        $doc->block($token);
        $doc->save();
    }
}

$answer = [
    "code" => 200,
    "message" => "ok",
    "data" => $data
];

echoAndDie($answer);
