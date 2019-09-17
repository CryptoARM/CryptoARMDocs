<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
    'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_CRM"),
    'DESCRIPTION' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_CRM_UPLOAD_LIST"),
    'CACHE_PATH' => 'Y',
    'COMPLEX' => 'N'
);
