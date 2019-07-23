<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loader::includeModule('trusted.cryptoarmdocs');
Loader::includeModule('iblock');

$arResult = array();
$arResult["PROPERTY"] = Docs\Form::getIBlockProperty($arParams["IBLOCK_ID"]);

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

$arResult["SEND_EMAIL_TO_USER"] = $arParams["SEND_EMAIL_TO_USER"] == "Y" ? Docs\Utils::getUserEmail() : false;
$arResult["SEND_EMAIL_TO_ADMIN"] = $arParams["SEND_EMAIL_TO_ADMIN"] == "Y" ? $arParams["SEND_EMAIL_TO_ADMIN_ADDRESS"] : false;

$this->IncludeComponentTemplate();
