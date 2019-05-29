<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Trusted\CryptoARM\Docs;

function getBizprocTabHtml($docId)
{
    if (!Loader::includeModule('bizproc')) {
        return null;
    }

    $context = Context::getCurrent();
    $request = $context->getRequest();

    $workflowIdLog = $request->get('bizproc_log');
    $workflowIdTask = $request->get('bizproc_task');
    $workflowStart = $request->get('bizproc_start');

    if (!empty($workflowIdLog)) {
        return $this->getWorkflowLogHtml($docId, $workflowIdLog);
    } elseif (!empty($workflowIdTask)) {
        return $this->getWorkflowTaskHtml($workflowIdTask);
    } elseif ($workflowStart == 'Y') {
        return $this->getWorkflowStartHtml($docId);
    } else {
        return $this->getWorkflowListHtml($docId);
    }
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loader::includeModule('trusted.cryptoarmdocs');

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_CRM_DETAILS_TITLE'));

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/admin/trusted_cryptoarm_docs.php');

$docId = $arResult['VARIABLES']['ID'];

if (!$docId) {
    ShowError(Loc::getMessage('TR_CA_DOCS_CRM_NO_DOC_ID'));
    return;
}

$doc = Docs\Database::getDocumentById($docId);

if (!$doc) {
    ShowError(Loc::getMessage('TR_CA_DOCS_CRM_NO_DOC') . $docId);
    return;
}

if (!$doc->accessCheck(Docs\Utils::currUserId(), DOC_SHARE_READ)) {
    ShowError(Loc::getMessage('TR_CA_DOCS_CRM_NO_ACCESS_TO_DOC') . $docId);
    return;
}

echo 'Id: ' . $docId . '<br>';
echo Loc::getMessage('TR_CA_DOCS_COL_FILENAME') . ': ' . $doc->getName() . '<br>';
echo Loc::getMessage('TR_CA_DOCS_COL_SIGN') . ':<br>';
echo $doc->getSignaturesToTable();

$tabs = array(
    array(
        'id' => 'tab_1',
        'name' => 'name',
        'title' => 'title',
        'display' => false,
        'fields' => array(
            array(
                'id' => 'section_store',
                'name' => 'section_store',
                'type' => 'section',
                'isTactile' => true,
            ),
            array(
                'id' => 'ID',
                'name' => 'ID',
                'type' => 'label',
                'value' => $docId,
                'isTactile' => true,
            ),
            array(
                'id' => 'NAME',
                'name' => 'NAME',
                'type' => 'label',
                'value' => $doc->getName(),
                'isTactile' => true,
            ),
        )
    ),
    array(
        'id' => 'deals',
        'name' => 'CRMSTORES_TAB_DEALS_NAME',
        'title' => 'CRMSTORES_TAB_DEALS_TITLE',
        'fields' => array(
            array(
                'id' => 'DEALS',
                'colspan' => true,
                'type' => 'custom',
                'value' => 'boundDealsHtml',
            )
        )
    )
);

// if (!empty($arResult['BIZPROC_TAB'])) {
    $tabs[] = array(
        'id' => $arResult['BIZPROC_TAB']['ID'],
        'name' => 'CRMSTORES_TAB_BP_NAME',
        'title' => 'CRMSTORES_TAB_BP_TITLE',
        'fields' => array(
            array(
                'id' => 'WORKFLOW_VIEW',
                'colspan' => true,
                'type' => 'custom',
                'value' => getBizprocTabHtml($docId),
            )
        )
    );
// }

$APPLICATION->IncludeComponent(
    'bitrix:crm.interface.form',
    'show',
    array(
        'GRID_ID' => $arResult['GRID_ID'],
        'FORM_ID' => $arResult['FORM_ID'],
        'TACTILE_FORM_ID' => $arResult['TACTILE_FORM_ID'],
        'ENABLE_TACTILE_INTERFACE' => 'Y',
        'SHOW_SETTINGS' => 'Y',
        'DATA' => $arResult['STORE'],
        'TABS' => $tabs,
    ),
    $this->getComponent(),
    array('HIDE_ICONS' => 'Y')
);
