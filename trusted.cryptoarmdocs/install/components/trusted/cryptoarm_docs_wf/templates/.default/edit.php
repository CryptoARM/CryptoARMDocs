<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Trusted\CryptoARM\Docs;

Loader::includeModule('trusted.cryptoarmdocs');

$urlTemplates = array(
    'EDIT' => $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['edit'],
    'LIST' => $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES'],
);

$APPLICATION->IncludeComponent(
    'bitrix:bizproc.workflow.edit',
    '',
    array(
        'MODULE_ID' => 'trusted.cryptoarmdocs',
        'ENTITY' => Docs\WorkflowDocument::class,
        'DOCUMENT_TYPE' => 'TR_CA_DOC',
        'ID' => (int)$arResult['VARIABLES']['ID'],
        'EDIT_PAGE_TEMPLATE' => $urlTemplates['EDIT'],
        'LIST_PAGE_URL' => $urlTemplates['LIST'],
        'SHOW_TOOLBAR' => 'Y',
        'SET_TITLE' => 'Y',
    )
);

