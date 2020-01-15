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

$userId = getUserIdByToken($_REQUEST["token"]);
$docId = json_decode($_REQUEST["id"]);
$signToken = $_REQUEST["signToken"];
$signers = $_REQUEST["signers"];

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

if (!$docId) {
    $answer = [
        "code" => 908,
        "message" => "id is not find",
        "data" => []
    ];
    echoAndDie($answer);
}

if (!$signers) {
    $answer = [
        "code" => 951,
        "message" => "incorrect signers parameter",
        "data" => []
    ];
    echoAndDie($answer);
}

if (!$signToken) {
    $answer = [
        "code" => 952,
        "message" => "incorrect sign token parameter",
        "data" => []
    ];
    echoAndDie($answer);
}

global $USER;
$USER->Authorize($userId);

$doc = Docs\Database::getDocumentById($docId);
if ($doc) {
    if (!$doc->accessCheck($userId, DOC_SHARE_SIGN)) {
        $answer = [
            "id" => $docId,
            "code" => 901,
            "message" => "have not permission",
        ];
        echoAndDie($answer);
    }
    $lastDoc = $doc->getLastDocument();
} else {
    $answer = [
        "id" => $docId,
        "code" => 902,
        "message" => "document does not exist",
    ];
    echoAndDie($answer);
}

if ($lastDoc->getId() !== $doc->getId()) {
    $answer = [
        "id" => $docId,
        "code" => 954,
        "message" => "document already has child",
    ];
    echoAndDie($answer);
}

if ($doc->getStatus() !== DOC_STATUS_BLOCKED) {
    $answer = [
        "id" => $docId,
        "code" => 903,
        "message" => "document already unblocked",
    ];
    echoAndDie($answer);
}

if ($doc->getBlockToken() !== $signToken) {
    $res["message"] = "Wrong token";
    return $res;
}

$newDoc = $doc->copy();
$signatures = urldecode($signers);
$newDoc->setSignatures($signatures);
// Append new user to the list of signers
$newDoc->addSigner($doc->getBlockBy());
$newDoc->setType(DOC_TYPE_SIGNED_FILE);
$newDoc->setParent($doc);
$file = $_FILES["file"];
$newDoc->setHash(hash_file('md5', $_FILES["file"]['tmp_name']));

$requires = $newDoc->getRequires()->getList();

foreach ($requires as &$require) {
    if ($require->getUserId() == $doc->getBlockBy()) {
        $require->setSignStatus(true);
    }
}

if ($newDoc->getParent()->getType() == DOC_TYPE_FILE) {
    $newDoc->setName($newDoc->getName() . '.sig');
    $newDoc->setPath($newDoc->getPath() . '.sig');
}

$newDoc->save();

move_uploaded_file(
    $_FILES["file"]['tmp_name'],
    $_SERVER['DOCUMENT_ROOT'] . '/' . rawurldecode($newDoc->getPath())
);

$doc = Database::getDocumentById($docId);
$doc->unblock();
$doc->save();

$answer = [
    "code" => 200,
    "message" => "ok",
    "data" => [
        $doc->getId() => [
            "id" => $doc->getId(),
            "code" => 200,
            "message" => "ok"
        ]
    ]
];

echoAndDie($answer);
