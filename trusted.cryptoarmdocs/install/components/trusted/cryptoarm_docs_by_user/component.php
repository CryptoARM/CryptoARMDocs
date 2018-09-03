<?php
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;

Loader::includeModule('trusted.cryptoarmdocs');

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
            "USER" => $USER,
            "ParamOfRemoval" => $arParams["POSSIBILITY_OF_REMOVAL"],
            "ParamOfAdding" => $arParams["POSSIBILITY_OF_ADDING"],
        );
    }
}

$arResult = new \CDBResult;
$arResult->InitFromArray($docsInfo);
//$arResult->navStart($arParams["ELEMENTS_ON_PAGE"]);

$this->IncludeComponentTemplate();
