<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_FORM_NAME"),
    'DESCRIPTION' => Loc::getMessage("TR_CA_DOCS_COMP_FORM_DESCRIPTION"),
    'PATH' => array(
        'ID' => 'CryptoARM Documents',
        "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_FORM_PATH_NAME"),
    ),
    'CACHE_PATH' => 'Y',
];

