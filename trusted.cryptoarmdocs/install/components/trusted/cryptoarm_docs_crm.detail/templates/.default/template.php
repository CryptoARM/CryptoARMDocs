<?php

defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_CRM_DETAILS_TITLE'));

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/admin/trusted_cryptoarm_docs.php');

$doc = $arResult['DOC'];
$docId = $doc->getId();

// -----------------

$tabs = array(
    array(
        'id' => 'tab_1',
        'name' => Loc::getMessage('TR_CA_DOCS_CRM_DETAIL_DOC'),
        'title' => Loc::getMessage('TR_CA_DOCS_CRM_DETAIL_DOC'),
        'display' => true,
        'fields' => array(
            array(
                'id' => 'ID',
                'name' => 'ID',
                'type' => 'label',
                'value' => $docId,
                'isTactile' => false,
            ),
            array(
                'id' => 'NAME',
                'name' => Loc::getMessage('TR_CA_DOCS_COL_FILENAME'),
                'type' => 'label',
                'value' => $doc->getName(),
                'isTactile' => false,
            ),
            array(
                'id' => 'SIGNATURES',
                'name' => Loc::getMessage('TR_CA_DOCS_COL_SIGN'),
                'type' => 'label',
                'value' => $doc->getSignaturesToTable(),
                'isTactile' => false,
            ),
        )
    ),
);

if (!empty($arResult['BIZPROC_TAB'])) {
    $tabs[] = array(
        'id' => $arResult['BIZPROC_TAB']['ID'],
        'name' => Loc::getMessage('TR_CA_DOCS_CRM_DETAIL_WF'),
        'title' => Loc::getMessage('TR_CA_DOCS_CRM_DETAIL_WF_DESC'),
        'fields' => array(
            array(
                'id' => 'WORKFLOW_VIEW',
                'colspan' => true,
                'type' => 'custom',
                'value' => $arResult['BIZPROC_TAB']['HTML']
            )
        )
    );
}

$APPLICATION->IncludeComponent(
    'bitrix:crm.interface.form',
    'show',
    array(
        'GRID_ID' => $arResult['GRID_ID'],
        'FORM_ID' => $arResult['FORM_ID'],
        'TACTILE_FORM_ID' => $arResult['TACTILE_FORM_ID'],
        'SHOW_TABS' => 'Y',
        'SHOW_SETTINGS' => 'Y',
        'DATA' => $doc->toArray(),
        'TABS' => $tabs,
    ),
    $this->getComponent(),
    array('HIDE_ICONS' => 'Y')
);
?>

<script>
$('#form_id_section_wrapper').css({visibility: 'hidden', width: '0px', padding: '0px', margin: '0px'});
</script>

