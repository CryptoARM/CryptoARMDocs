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

if ($userId["code"]) {
    echoAndDie($userId);
}

$data = [];
$docsId = json_decode($_REQUEST["ids"]);

foreach ($docsId as $docId) {
    $doc = Docs\Database::getDocumentById($docId);

    if (!$doc) {
        $data[$docId] = [
            "id" => $docId,
            "code" => 902,
            "message" => "document does not exist",
            "name" => null,
            "type" => null,
            "status" => null,
            "hash" => null,
            "access" => null,
            "date" => null,
            "require" => null,
            "owner" => null
        ];
        continue;
    }

    $ownerUserId = $doc->getOwner();

    if ($ownerUserId == $userId) {
        $accessLevel = 930;
    } elseif ($doc->accessCheck($userId, DOC_SHARE_SIGN)) {
        $accessLevel = 931;
    } elseif ($doc->accessCheck($userId, DOC_SHARE_READ)) {
        $accessLevel = 932;
    } else {
        $data[$docId] = [
            "id" => $docId,
            "code" => 901,
            "message" => "have not permission",
            "name" => null,
            "type" => null,
            "status" => null,
            "hash" => null,
            "access" => null,
            "date" => null,
            "require" => null,
            "owner" => null
        ];
        continue;
    }

    switch ($doc->getStatus()) {
        case DOC_STATUS_NONE:
            $status = 910;
            break;
        case DOC_STATUS_BLOCKED:
            $status = 911;
            break;
        case DOC_STATUS_CANCELED:
            $status = 912;
            break;
        case DOC_STATUS_ERROR:
            $status = 913;
            break;
    }


    $docRequire = $doc->getRequires();
    if (in_array($userId, $docRequire->getUserList())) {
        if (!$docRequire->getSignStatusByUser($userId)) {
            $require = 941;
        }
    } else {
        $require = 940;
    }

    $data[$docId] = [
        "id" => $docId,
        "code" => 900,
        "message" => "ok",
        "name" => $doc->getName(),
        "type" => $doc->getType() ? 921 : 920,
        "status" => $status,
        "hash" => $doc->getHash(),
        "access" => $accessLevel,
        "date" => $doc->getCreated(),
        "require" => $require,
        "owner" => Docs\Utils::getUserName($ownerUserId)
    ];
}

$answer = [
    "code" => 200,
    "message" => "ok",
    "date" => $data
];

echoAndDie($answer);
