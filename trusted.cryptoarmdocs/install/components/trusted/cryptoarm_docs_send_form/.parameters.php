<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('trusted.cryptoarmdocs');

$formIBlocks["default"] = Loc::getMessage("TR_CA_DOCS_COMP_SEND_FORM_PARAMETERS_IBLOCK_ID_NAME");

$docSaveFormat = array(
    "pdf" => "PDF",
    "xml" => "XML",
    "xsd" => "XSD",
);

$formIBlocks += Docs\Form::getIBlocks();

$arComponentParameters = array(
    "GROUPS" => array(
        "SETTINGS" => array(
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_SEND_FORM_GROUP_SETTINGS_NAME"),
        ),
    ),
    "PARAMETERS" => array(
        "IBLOCK_ID" => array(
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_SEND_FORM_PARAMETERS_IBLOCK_ID_NAME"),
            "TYPE" => "LIST",
            "REFRESH" => "Y",
            "MULTIPLE" => "N",
            "VALUES" => $formIBlocks,
            "DEFAULT" => $formIBlocks["default"],
            "ADDITIONAL_VALUES" => "N",
        ),
        "FILE_FORMAT_SAVE" => array(
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_SEND_FORM_PARAMETERS_FILE_FORMAT_SAVE_NAME"),
            "TYPE" => "LIST",
            "REFRESH" => "Y",
            "MULTIPLE" => "N",
            "VALUES" => $docSaveFormat,
            "DEFAULT" => $docSaveFormat["default"],
            "ADDITIONAL_VALUES" => "N",
        ),
        "SEND_EMAIL_TO_USER" => array(
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_SEND_FORM_PARAMETERS_SEND_EMAIL_TO_USER_NAME"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "SEND_EMAIL_TO_ADMIN" => array(
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_SEND_FORM_PARAMETERS_SEND_EMAIL_TO_ADMIN_NAME"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "SEND_EMAIL_TO_ADMIN_ADDRESS" => array(
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_SEND_FORM_PARAMETERS_SEND_EMAIL_TO_ADMIN_ADDRESS_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
    )
);
