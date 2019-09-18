<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
    'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_UPLOAD"),
    'DESCRIPTION' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_UPLOAD_LIST"),
    'CACHE_PATH' => 'Y',
    'COMPLEX' => 'N'
);
