<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = array(
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
);
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $coreId . "/";
    if(file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

if (CModule::IncludeModuleEx($module_id) == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
}

if (isModuleInstalled($module_id)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/Database.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/Utils.php';
}

$currUserId = Docs\Utils::currUserId();

$docs = Docs\Database::getDocumentsByUser($currUserId, true);

$docsInfo = array();
$allIds = array();

foreach ($docs->getList() as $doc) {
    if ($arParams["CHECK_ORDER_PROPERTY"] === "N" &&  $doc->getProperties()->getPropByType("ORDER")) {
            continue;
    } else {
        if ($doc->getOwner() == $currUserId) {
            $accessLevel = "OWNER";
        } elseif ($doc->accessCheck($currUserId, DOC_SHARE_SIGN)) {
            $accessLevel = "SIGN";
        } elseif ($doc->accessCheck($currUserId, DOC_SHARE_READ)) {
            $accessLevel = "READ";
        }
        $docsInfo[] = array(
            "ID" => $doc->getId(),
            "NAME" => $doc->getName(),
            "TYPE" => $doc->getType(),
            "TYPE_STRING" => Docs\Utils::getTypeString($doc),
            "STATUS" => $doc->getStatus(),
            "STATUS_STRING" => Docs\Utils::getStatusString($doc),
            "ACCESS_LEVEL" => $accessLevel,
            "OWNER_USERNAME" => Docs\Utils::getUserName($doc->getOwner()),
            "DATE_CREATED" => date("d.m.o H:i", strtotime(Docs\Database::getDocumentById($doc->getId())->getCreated())),
        );
        $allIds[] = $doc->getId();
    }
}

$arResult = array(
    'DOCS' => $docsInfo,
    'ALL_IDS' => $allIds,
    'ALL_IDS_JS' => json_encode($allIds),
);

$this->IncludeComponentTemplate();

