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
    break;
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

if ($userId["code"]) {
    echoAndDie($userId);
}

$data = [];
$docsList = Docs\Database::getDocumentsByUser($userId, true);

foreach ($docsList->getList() as $doc) {
    $data[] = $doc->getId();
}

$answer = [
    "code" => 200,
    "message" => "ok",
    "data" => $data
];

echoAndDie($answer);
