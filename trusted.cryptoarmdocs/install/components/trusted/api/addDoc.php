<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/trusted/api/getUser.php";

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

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

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $module_id . "/lang/ru/classes/Utils.php";

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

if (!$_FILES) {
    $answer = [
        "code" => 953,
        "message" => "document does not exist",
        "data" => []
    ];
    echoAndDie($answer);
}

if (!$_FILES["file"]) {
    $answer = [
        "code" => 953,
        "message" => "incorrect file parameter",
        "data" => []
    ];
    echoAndDie($answer);
}

global $USER;
$USER->Authorize($userId);

$uniqid = (string)uniqid();

$DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

$newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
mkdir($newDocDir);

$newDocFilename = Docs\Utils::mb_basename($_FILES["file"]['name']);
$newDocFilename = preg_replace('/[\s]+/u', '_', $newDocFilename);
$newDocFilename = preg_replace('/[^a-zA-Z' . Loc::getMessage("TR_CA_DOCS_CYR") . '0-9_\.-]/u', '', $newDocFilename);
$absolutePath = $newDocDir . $newDocFilename;
$relativePath = $DOCUMENTS_DIR . $uniqid . '/' . $newDocFilename;

if (move_uploaded_file($_FILES["file"]['tmp_name'], $absolutePath)) {
    $props = new Docs\PropertyCollection();
    $props->add(new Docs\Property("USER", (string)$userId));
    $doc = Docs\Utils::createDocument($relativePath, $props);
}

unset($_FILES["file"]);

$USER->Logout();

$answer = [
    "code" => 200,
    "message" => "ok",
    "data" => [
        $doc->getId() => [
            "code" => 200,
            "message" => "ok",
            "id" => $doc->getId()
        ]
    ]
];

echoAndDie($answer);
