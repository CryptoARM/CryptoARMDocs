<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;

Loader::includeModule('trusted.cryptoarmdocs');

if ($USER->IsAuthorized()) {
    $docs = Docs\Database::getDocumentsByOrder($arParams["ORDER"]);
} else {
    $docs = new Docs\DocumentCollection();
}

$docList = $docs->getList();

$docsInfo = array();
$allIds = array();

foreach ($docList as $doc) {
    $docsInfo[] = array(
        "ID" => $doc->getId(),
        "NAME" => $doc->getName(),
        "TYPE" => $doc->getType(),
        "TYPE_STRING" => Docs\Utils::getTypeString($doc),
        "STATUS" => $doc->getStatus(),
        "STATUS_STRING" => Docs\Utils::getStatusString($doc),
    );
    $allIds[] = $doc->getId();
}

$arResult = array(
    'DOCS' => $docsInfo,
    'ALL_IDS' => $allIds,
    'ALL_IDS_JS' => json_encode($allIds),
);

$this->IncludeComponentTemplate();

