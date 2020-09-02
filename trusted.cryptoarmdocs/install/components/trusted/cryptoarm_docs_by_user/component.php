<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
if($USER->IsAuthorized()){
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
$blockedDocs = [];

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

        $docObject = Docs\Database::getDocumentById($doc->getId());
        $docRequire = $docObject->getRequires();
        if (in_array($USER->GetID(), $docRequire->getUserList())) {
            $mustToSign = !$docRequire->getSignStatusByUser($USER->GetID());
        } else {
            $mustToSign = false;
        }

        $userIds = Docs\Database::getUserIdsByDocument($doc->getId());
        $status = [];
        $signersString = $doc->getSigners();
        preg_match_all('!\d+!', $signersString, $signersArray);

        foreach ($userIds as $id) {
            if ($doc->accessCheck($id, DOC_SHARE_SIGN)) {
                $sharedAccessLevel = "SIGN";
            } elseif ($doc->accessCheck($id, DOC_SHARE_READ)) {
                $sharedAccessLevel = "READ";
            }
            if (in_array($id, $docRequire->getUserList())) {
                $sharedMustToSign = !$docRequire->getSignStatusByUser($id);
            } else {
                $sharedMustToSign = false;
            }
            $status[] = array(
                'id' => $id,
                'name' => Docs\Utils::getUserName($id),
                'access_level' => $sharedAccessLevel,
                'signed' => in_array($id, $signersArray[0]) ? true : false,
                'mustToSign' => $sharedMustToSign,
             );
        }


        $docsInfo[] = array(
            "ID" => $doc->getId(),
            "NAME" => $doc->getName(),
            "TYPE" => $doc->getType(),
            "TYPE_STRING" => Docs\Utils::getTypeString($doc),
            "ORIGINAL_ID" => $doc->getOriginalId(),
            "STATUS" => $doc->getStatus(),
            "STATUS_STRING" => Docs\Utils::getStatusString($doc),
            "ACCESS_LEVEL" => $accessLevel,
            "OWNER_USERNAME" => Docs\Utils::getUserName($doc->getOwner()),
            "DATE_CREATED" => date("d.m.o H:i", strtotime(Docs\Database::getDocumentById($doc->getId())->getCreated())),
            "MUST_TO_SIGN" => $mustToSign,
            "SHARED_STATUS_JS" => json_encode($status),

        );

        if ($doc->getBlockBy() == $currUserId) {
            $blockedDocs["TOKENS"][] = $doc->getBlockToken();
            $blockedDocs["IDS"][] = $doc->getId();
        }

        $allIds[] = $doc->getId();
    }
}

$blockedDocs = [
    "TOKENS" => json_encode((!is_array($blockedDocs["TOKENS"])) ? $blockedDocs["TOKENS"] :array_unique($blockedDocs["TOKENS"])),
    "IDS" => json_encode((!is_array($blockedDocs["IDS"])) ? $blockedDocs["IDS"] :array_unique($blockedDocs["IDS"])),
];

$arResult = array(
    'DOCS' => $docsInfo,
    'ALL_IDS' => $allIds,
    'ALL_IDS_JS' => json_encode($allIds),
    'BLOCKED_DOCUMENTS' => $blockedDocs,
);

$this->IncludeComponentTemplate();

}