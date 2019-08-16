<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Trusted\CryptoARM\Docs;

if (CModule::IncludeModuleEx('trusted.cryptoarmdocs') == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
};

Loader::includeModule('trusted.cryptoarmdocs');

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_WF_EDIT_TITLE'));

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
        'ID' => (int)$arResult['VARIABLES']['WF_ID'],
        'EDIT_PAGE_TEMPLATE' => $urlTemplates['EDIT'],
        'LIST_PAGE_URL' => $urlTemplates['LIST'],
        'SHOW_TOOLBAR' => 'Y',
        'SET_TITLE' => 'Y',
    )
);

