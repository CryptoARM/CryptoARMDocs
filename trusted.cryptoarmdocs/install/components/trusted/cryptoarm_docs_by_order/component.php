<?php
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;

Loader::includeModule('trusted.cryptoarmdocs');

$docs = Docs\Database::getDocumentsByOrder($arParams["ORDER"]);
$docList = $docs->getList();

$docsInfo = array();

foreach ($docList as $doc) {
    $docsInfo[] = array(
        "ID" => $doc->getId(),
        "NAME" => $doc->getName(),
        "STATUS" => $doc->getStatus(),
        "ORDER" => $arParams["ORDER"],
    );
}

$arResult = new \CDBResult;
$arResult->InitFromArray($docsInfo);
//$arResult->navStart($arParams["ELEMENTS_ON_PAGE"]);

$this->IncludeComponentTemplate();
