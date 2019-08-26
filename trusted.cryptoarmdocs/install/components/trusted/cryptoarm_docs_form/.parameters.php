<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('trusted.cryptoarmdocs');

$formIBlocks[0] = Loc::getMessage("TR_CA_DOCS_COMP_FORM_PARAMETERS_IBLOCK_ID_NAME");

$docSaveFormat = [
    "pdf" => "PDF",
    // "xml" => "XML",
    // "xsd" => "XSD",
];

$formIBlocks += Docs\Form::getIBlocks();

$arComponentParameters = [
    "GROUPS" => [
        "SETTINGS" => [
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_FORM_GROUP_SETTINGS_NAME"),
        ],
    ],
    "PARAMETERS" => [
        "IBLOCK_ID" => [
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_FORM_PARAMETERS_IBLOCK_ID_NAME"),
            "TYPE" => "LIST",
            "REFRESH" => "Y",
            "MULTIPLE" => "N",
            "VALUES" => $formIBlocks,
            "DEFAULT" => $formIBlocks["default"],
            "ADDITIONAL_VALUES" => "N",
        ],
        "FILE_FORMAT_SAVE" => [
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_FORM_PARAMETERS_FILE_FORMAT_SAVE_NAME"),
            "TYPE" => "LIST",
            "REFRESH" => "Y",
            "MULTIPLE" => "N",
            "VALUES" => $docSaveFormat,
            "DEFAULT" => $docSaveFormat[0],
            "ADDITIONAL_VALUES" => "N",
        ],
        "SEND_EMAIL_TO_USER" => [
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_FORM_PARAMETERS_SEND_EMAIL_TO_USER_NAME"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
        "SEND_EMAIL_TO_ADMIN_ADDRESS" => [
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_FORM_PARAMETERS_SEND_EMAIL_TO_ADMIN_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "ENABLE_RECAPTCHA" => [
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_FORM_PARAMETERS_ENABLE_RECAPTCHA_NAME"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
    ],
];

