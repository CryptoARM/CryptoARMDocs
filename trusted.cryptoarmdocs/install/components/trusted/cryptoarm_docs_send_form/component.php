<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loader::includeModule('trusted.cryptoarmdocs');
Loader::includeModule('iblock');

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
//    $arResult["PROPERTY"][$prop_fields["ID"]] = $prop_fields;
    $arResult["PROPERTY"][$prop_fields["ID"]]["ID"] = $prop_fields["ID"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["NAME"] = $prop_fields["NAME"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["PROPERTY_TYPE"] = $prop_fields["PROPERTY_TYPE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["MULTIPLE"] = $prop_fields["MULTIPLE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["LIST_TYPE"] = $prop_fields["LIST_TYPE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["DEFAULT_VALUE"] = $prop_fields["DEFAULT_VALUE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["IS_REQUIRED"] = $prop_fields["IS_REQUIRED"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["SORT"] = $prop_fields["SORT"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["CODE"] = $prop_fields["CODE"];
    $arResult["PROPERTY"][$prop_fields["ID"]]["USER_TYPE"] = $prop_fields["USER_TYPE"];
}

while ($propAdd_fields = $propertiesAdditional->GetNext()) {
    $arResult["PROPERTY"][$propAdd_fields["PROPERTY_ID"]]["ADDITIONAL"][$propAdd_fields["ID"]] = $propAdd_fields["VALUE"];
}

if (Docs\Utils::checkAuthorization()) {
    $arResult["compVisibility"] = true;
    if ($arParams["IBLOCK_ID"] == "default" || $arParams["IBLOCK_ID"] == null) {
        $arResult["compVisibility"] = false;
    } else {
        if ($arParams["SEND_EMAIL_TO_ADMIN"] === "Y") {
            if (!(Docs\Utils::validateEmailAddress($arParams["SEND_EMAIL_TO_ADMIN_ADDRESS"]))) {
                $arResult["compVisibility"] = false;
            }
        }
    }
} else {
    $arResult["compVisibility"] = false;
}

$this->IncludeComponentTemplate();
