<?php

defined('B_PROLOG_INCLUDED') || die;

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (CModule::IncludeModuleEx("trusted.cryptoarmdocs") == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
};

Loader::includeModule("trusted.cryptoarmdocs");

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_WF_LIST_TITLE'));

// bizproc.workflow.list expects variable #ID#
$editUrlTemplate = str_replace(
    '#WF_ID#',
    '#ID#',
    $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['wf_edit']
);
$urlTemplates = array(
    'EDIT' => $editUrlTemplate,
    'EDIT_STATEMACHINE' => $editUrlTemplate . '?init=statemachine',
    'LIST' => $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['wf_list'],
);

if ($USER->IsAdmin()) {
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
}

echo '<br />' . Loc::getMessage("TR_CA_DOCS_WF_TEMPLATE_DESCRIPTION") .
'<a target="_blank" href="https://docs.google.com/document/d/1o1kvVXJ7LgZ5UeN1W2bz44plPOhWWM4sj7dr6S-koAo/edit#heading=h.iq6cj4xi6sin">' .
Loc::getMessage("TR_CA_DOCS_WF_TEMPLATE_DESCRIPTION2") . '</a>' . ".";

