<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loader::includeModule('iblock');

$formIBlocks["default"] = Loc::getMessage("TR_CA_DOCS_COMP_SEND_FORM_PARAMETERS_IBLOCK_ID_NAME");

$dbIblocks = CIBlock::GetList(
    Array(
        "sort" => "asc",
        "name" => "asc",
    ),
    Array(
        "TYPE" => "tr_ca_docs_form",
        "CHECK_PERMISSIONS" => "N",
    )
);

$docSaveFormat = array(
    "pdf" => "PDF",
    "xml" => "XML",
    "xsd" => "XSD",
);

while ($arIblock = $dbIblocks->Fetch()) {
    $formIBlocks[htmlspecialcharsEx($arIblock["ID"])] = htmlspecialcharsEx($arIblock["NAME"]);
}

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
