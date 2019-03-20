<?php
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;

Loader::includeModule('trusted.cryptoarmdocs');

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

