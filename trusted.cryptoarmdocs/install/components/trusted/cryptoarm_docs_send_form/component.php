<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loader::includeModule('trusted.cryptoarmdocs');
Loader::includeModule('iblock');

$arResult = [];
$arResult["PROPERTY"] = Docs\Form::getIBlockProperty($arParams["IBLOCK_ID"]);

if (Docs\Utils::checkAuthorization()) {
    $arResult["compNotVisibility"] = false;
    if ($arParams["IBLOCK_ID"] == "default" || $arParams["IBLOCK_ID"] == null) {
        $arResult["compNotVisibility"] = "error with iblock settings";
    } else {
        if ($arParams["SEND_EMAIL_TO_ADMIN"] === "Y") {
            if (!(Docs\Utils::validateEmailAddress($arParams["SEND_EMAIL_TO_ADMIN_ADDRESS"]))) {
                $arResult["compNotVisibility"] = "error with validate email address";
            }
        }
    }
} else {
    $arResult["compNotVisibility"] = "not authorized";
}

$arResult["isAdmin"] = Docs\Utils::isAdmin(Docs\Utils::currUserId());

$arResult["SEND_EMAIL_TO_USER"] = $arParams["SEND_EMAIL_TO_USER"] == "Y" ? Docs\Utils::getUserEmail() : false;
$arResult["SEND_EMAIL_TO_ADMIN"] = $arParams["SEND_EMAIL_TO_ADMIN"] == "Y" ? $arParams["SEND_EMAIL_TO_ADMIN_ADDRESS"] : false;

$this->IncludeComponentTemplate();
