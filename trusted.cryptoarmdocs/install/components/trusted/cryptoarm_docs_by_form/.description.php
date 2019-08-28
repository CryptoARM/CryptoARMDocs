<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_NAME"),
    'DESCRIPTION' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_DESCRIPTION"),
    'PATH' => [
        'ID' => 'CryptoARM Documents',
        "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_NAME"),
    ],
    'CACHE_PATH' => 'Y',
    'COMPLEX' => 'Y'
];
