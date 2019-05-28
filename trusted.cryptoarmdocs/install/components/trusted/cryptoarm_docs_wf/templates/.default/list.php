<?php

defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
// use Bitrix\Main\Text\HtmlFilter;
// use Bitrix\Main\UI\Extension;
use Trusted\CryptoARM\Docs;

Loader::includeModule("trusted.cryptoarmdocs");

$editUrlTemplate = $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['edit'];
$urlTemplates = array(
    'EDIT' => $editUrlTemplate,
    'EDIT_STATEMACHINE' => $editUrlTemplate . '?init=statemachine',
    'LIST' => $arResult['SEF_FOLDER'],
);

// Extension::load('ui.buttons');

$APPLICATION->IncludeComponent(
    'bitrix:main.interface.toolbar',
    '',
    array(
        'BUTTONS'=>array(
            array(
                'TEXT' => Loc::getMessage('TR_CA_DOCS_WF_NEW_BP_STATEMACHINE'),
                'TITLE' => Loc::getMessage('TR_CA_DOCS_WF_NEW_BP_STATEMACHINE'),
                'LINK' => CComponentEngine::makePathFromTemplate(
                    $urlTemplates['EDIT_STATEMACHINE'],
                    array('ID' => 0)
                ),
                'ICON' => 'btn-new',
            ),
            array(
                'TEXT' => Loc::getMessage('TR_CA_DOCS_WF_NEW_BP_SEQUENTAL'),
                'TITLE' => Loc::getMessage('TR_CA_DOCS_WF_NEW_BP_SEQUENTAL'),
                'LINK' => CComponentEngine::makePathFromTemplate(
                    $urlTemplates['EDIT'],
                    array('ID' => 0)
                ),
                'ICON' => 'btn-new',
            ),
        ),
    )
);

$APPLICATION->IncludeComponent(
    'bitrix:bizproc.workflow.list',
    '.default',
    array(
        'MODULE_ID' => 'trusted.cryptoarmdocs',
        'ENTITY' => Docs\WorkflowDocument::class,
        'DOCUMENT_ID' => 'TR_CA_DOC',
        'CREATE_DEFAULT_TEMPLATE' => 'N',
        'EDIT_URL' => $editUrlTemplate,
        'SET_TITLE' => 'N',
        'TARGET_MODULE_ID' => 'trusted.cryptoarmdocs',
    )
);
