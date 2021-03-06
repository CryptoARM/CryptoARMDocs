<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
    'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_USER"),
    'DESCRIPTION' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_LIST"),
    'PATH' => array(
        'ID' => 'CryptoARM Documents',
        "NAME" => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOC"),
    ),
    'CACHE_PATH' => 'Y',
    'COMPLEX' => 'N'
);
