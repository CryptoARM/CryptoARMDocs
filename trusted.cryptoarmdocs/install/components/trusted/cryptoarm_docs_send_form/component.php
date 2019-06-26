<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('trusted.cryptoarmdocs');
Loader::includeModule('iblock');

global $USER;

if ($arParams["IBLOCK_ID"] === "null") {
    $this->IncludeComponentTemplate();
}


$arResult = array();

$properties = CIBlockProperty::GetList(
    Array(
        "sort" => "asc",
        "name" => "asc",
    ),
    Array(
        "ACTIVE" => "Y",
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
    )
);

$propertiesAdditional = CIBlockPropertyEnum::GetList(
    Array(
        "sort" => "asc",
        "name" => "asc",
    ),
    Array(
        "ACTIVE" => "Y",
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
    )
);

while ($prop_fields = $properties->GetNext()) {
//    $arResult["PROPERTY"][$prop_fields["ID"]]["NAME"] = $prop_fields;
    $arResult["PROPERTY"][$prop_fields["ID"]]["ID"] = $prop_fields["ID"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["NAME"] = $prop_fields["NAME"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["PROPERTY_TYPE"] = $prop_fields["PROPERTY_TYPE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["MULTIPLE"] = $prop_fields["MULTIPLE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["ACTIVE"] = $prop_fields["ACTIVE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["LIST_TYPE"] = $prop_fields["LIST_TYPE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["DEFAULT_VALUE"] = $prop_fields["DEFAULT_VALUE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["SORT"] = $prop_fields["SORT"];
}

while ($propAdd_fields = $propertiesAdditional->GetNext()) {
    $arResult["PROPERTY"][$propAdd_fields["PROPERTY_ID"]]["ADDICTION"][$propAdd_fields["ID"]] = $propAdd_fields["VALUE"];
}

$this->IncludeComponentTemplate();
