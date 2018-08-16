<?php
use TrustedNet\Docs;
use Bitrix\Main\Loader;

Loader::includeModule('trustednet.docs');

global $USER;

$docs = Docs\Database::getDocumentsByUser($USER->GetID());
$docList = $docs->getList();

$docsInfo = array();

foreach ($docList as $doc) {
    if ($arParams["CHECK_ORDER_PROPERTY"] === "N" &&  $doc->getProperties()->getPropByType("ORDER")) {
            continue;
    } else {
        $docsInfo[] = array(
            "ID" => $doc->getId(),
            "NAME" => $doc->getName(),
            "STATUS" => $doc->getStatus(),
        );
    }
}

$arResult = new \CDBResult;
$arResult->InitFromArray($docsInfo);
//$arResult->navStart($arParams["ELEMENTS_ON_PAGE"]);

$this->IncludeComponentTemplate();
