<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loader::includeModule('trusted.cryptoarmdocs');
Loader::includeModule('iblock');

$arResult = [];
$arResult["PROPERTY"] = Docs\Form::getIBlockProperty($arParams["IBLOCK_ID"]);

if (!Docs\Utils::checkAuthorization()) {
    echo '<font color="#FF0000">ERROR not authorized</font>';
    return;
}
if ($arParams["IBLOCK_ID"] == "default" || $arParams["IBLOCK_ID"] == null) {
    echo '<font color="#FF0000">ERROR iblock not specified</font>';
    return;
}
if (!Docs\Form::getIBlockName($arParams["IBLOCK_ID"])) {
    echo '<font color="#FF0000">ERROR iblock not found</font>';
    return;
}
if ($arParams["SEND_EMAIL_TO_ADMIN_ADDRESS"]) {
    if (!(Docs\Utils::validateEmailAddress($arParams["SEND_EMAIL_TO_ADMIN_ADDRESS"]))) {
        echo '<font color="#FF0000">ERROR incorrect email</font>';
        return;
    }
}

$arResult["MAX_UPLOAD_FILE_SIZE"] = Docs\Utils::maxUploadFileSize();
$arResult["SEND_EMAIL_TO_USER"] = $arParams["SEND_EMAIL_TO_USER"] == "Y" ? Docs\Utils::getUserEmail() : false;
$arResult["SEND_EMAIL_TO_ADMIN_ADDRESS"] = $arParams["SEND_EMAIL_TO_ADMIN_ADDRESS"];

$this->IncludeComponentTemplate();

