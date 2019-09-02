<?php

defined('B_PROLOG_INCLUDED') || die;

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\UI;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

if (CModule::IncludeModuleEx('trusted.cryptoarmdocs') == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
};

Loader::includeModule('trusted.cryptoarmdocs');
CJSCore::Init('bp_starter');

UI\Extension::load("ui.buttons.icons");

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_CRM_LIST_TITLE'));

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/admin/trusted_cryptoarm_docs.php');

if ($_POST['action_button_crm_docs_grid'] == 'delete') {
    $filterOwner['OWNER'] = $USER->getId();
    $docsDBUser = Docs\Database::getDocumentIdsByFilter(null, $filterOwner);

    while ($docDBUser = $docsDBUser->Fetch()) {
        $docsIdUser[] = $docDBUser['ID'];
    }

    foreach ($_POST['ID'] as $ID) {
        if (array_search($ID, $docsIdUser) === false){
            $idsUnshare['ids'][] = $ID;
        } else {
            $idsRemove['ids'][] = $ID;
        }
    }

    if ($idsRemove)
        Docs\AjaxCommand::remove($idsRemove);

    if ($idsUnshare)
        Docs\AjaxCommand::unshare($idsUnshare);
}

$schema = array(
    'GRID_ID' => 'crm_docs_grid',
    'SHOW_PAGINATION' => true,
    'SHOW_PAGESIZE' => true,
    'SHOW_TOTAL_COUNTER' => false,
    'ALLOW_SORT' => true,
    'STRUCTURE' => array(
        'ID' => array(
            'NAME' => 'ID',
            'TYPE' => 'int',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'text',
        ),
        'NAME' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_FILENAME'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'text',
        ),
        'SIGNATURES' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_SIGN'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => false,
            // 'FILTER_TYPE' => 'text',
        ),
        'TYPE' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_TYPE'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'list',
            'FILTER_ITEMS' => array(
                Loc::getMessage("TR_CA_DOCS_TYPE_" . DOC_TYPE_FILE),
                Loc::getMessage("TR_CA_DOCS_TYPE_" . DOC_TYPE_SIGNED_FILE),
            ),
            'FILTER_PARAMS' => array(
                'multiple' => 'Y'
            ),
        ),
        'OWNER' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_OWNER'),
            'TYPE' => 'user',
            'DEFAULT' => true,
            'SORT' => false,
            'FILTER_TYPE' => 'dest_selector',
            'FILTER_PARAMS' => array(
                'apiVersion' => '3',
                'context' => 'OWNER',
                'contextCode' => 'U',
                'enableAll' => 'N',
                'enableSonetgroups' => 'N',
                'allowEmailInvation' => 'N',
                'allowSearchEmailUsers' => 'Y',
                'departmentSelectDisable' => 'Y',
                'isNumeric' => 'Y',
                'prefix' => 'U',
            ),
        ),
    ),
);

$gridBuilder = new Docs\GridBuilder($schema);
$filter = ($gridBuilder->filter);
$filter['SHARE_USER'] = $USER->getId();
$sort = $gridBuilder->sort;

$rowsId = Docs\Database::getDocumentIdsByFilter($sort, $filter);

$rowsId->NavStart($gridBuilder->pagination["nPageSize"]);
$rowsId->bShowAll = true;
$gridBuilder->navigation = $rowsId;

while ($rowId = $rowsId->Fetch()) {
    $docs[] = Docs\Database::getDocumentById($rowId['ID']);
}

$rows = array();

foreach ($docs as $doc) {
    $docId = $doc->getId();
    $signatures = $doc->getSignaturesToArray();
    $docStatus = $doc->getStatus();
    $docIdOwner = $doc->getOwner();
    $docOwner = Docs\Utils::getUserName($docIdOwner);

    $docTypeString = Docs\Utils::GetTypeString($doc);
    if ($docStatus !== DOC_STATUS_NONE) {
        $docTypeString .= '<br>' .
            Loc::getMessage('TR_CA_DOCS_STATUS') .
            Docs\Utils::GetStatusString($doc);
    }

    $actions = array();
    if ($docStatus === DOC_STATUS_NONE) {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_SIGN'),
            'onclick' => "trustedCA.sign([$docId])",
            'default' => true,
        );
    }
    if ($docStatus === DOC_STATUS_BLOCKED) {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_UNBLOCK'),
            'onclick' => "trustedCA.unblock([$docId], {$gridBuilder->reloadGridJs})",
            'default' => true,
        );
    }

    if ($doc->getType() === DOC_TYPE_SIGNED_FILE) {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_VERIFY'),
            'onclick' => "trustedCA.verify([$docId])",
            'default' => false,
        );
    }

    $actions[] = array(
        'text' => Loc::getMessage('TR_CA_DOCS_ACT_SEND_MAIL_TO'),
        'onclick' => "trustedCA.promptAndSendEmail([$dockId], 'MAIL_EVENT_ID_TO', {}, 'MAIL_TEMPLATE_ID_TO')",
        'default' => false,
    );

    $actions[] = array(
        'text' => Loc::getMessage('TR_CA_DOCS_ACT_PROTOCOL'),
        'onclick' => "trustedCA.protocol($docId)",
        'default' => false,
    );

    if (!empty($arResult['WORKFLOW_TEMPLATES'])) {
        $startWorkflowActions = array();
        foreach ($arResult['WORKFLOW_TEMPLATES'] as $workflowTemplate) {
            $starterParams = array(
                'moduleId' => TR_CA_DOCS_MODULE_ID,
                'entity' => Docs\WorkflowDocument::class,
                'documentType' => 'TR_CA_DOC',
                'documentId' => $docId,
                'templateId' => $workflowTemplate['ID'],
                'templateName' => $workflowTemplate['NAME'],
                'hasParameters' => $workflowTemplate['HAS_PARAMETERS']
            );
            $popupMessage = Loc::getMessage('TR_CA_DOCS_POPUP_MESSAGE_1') . $workflowTemplate['NAME'] . Loc::getMessage('TR_CA_DOCS_POPUP_MESSAGE_2');
            $startWorkflowActions[] = array(
                'TITLE' => $workflowTemplate['DESCRIPTION'],
                'TEXT' => $workflowTemplate['NAME'],
                'ONCLICK' => sprintf(
                    'BX.Bizproc.Starter.singleStart(%s, %s)',
                    Json::encode($starterParams),
                    $gridBuilder->reloadGridJs . ', trustedCA.showPopupMessage("' . $popupMessage . '", "check_circles", "positive")'
                )
            );
        }
        // $actions[] = array('SEPARATOR' => true);
        $actions[] = array(
            'TITLE' => Loc::getMessage('TR_CA_DOCS_CRM_START_WORKFLOW'),
            'TEXT' => Loc::getMessage('TR_CA_DOCS_CRM_START_WORKFLOW'),
            'MENU' => $startWorkflowActions
        );
    }
    if ($docIdOwner == $USER->GetID()) {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_SHARE_DOC'),
            'onclick' => "trustedCA.promptAndShare([$docId],'SHARE_SIGN')",
            'default' => false,
        );
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_REMOVE'),
            'onclick' => "trustedCA.remove([$docId], false, {$gridBuilder->reloadGridJs})",
            'default' => false,
        );
    } else {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_UNSHARE'),
            'onclick' => "trustedCA.unshare([$docId], false, {$gridBuilder->reloadGridJs})",
            'default' => false,
        );
    }

    $downloadJs = "trustedCA.download([$docId], true)";
    $docName = "<a style='cursor:pointer;' onclick='$downloadJs' ondblclick='event.stopPropagation()' title='" . Loc::getMessage('TR_CA_DOCS_DOWNLOAD_DOC') . "'>{$doc->getName()}</a>";
    $rows[] = array(
        'id' => $docId,
        'columns' => array(
            'ID' => $docId,
            'NAME' => $docName,
            'SIGNATURES' => $doc->getSignaturesToTable(),
            'TYPE' => $docTypeString,
            'OWNER' => $docOwner,
        ),
        'actions' => $actions,
    );
}

$gridBuilder->showGrid($rows);

$maxSize  = Docs\Utils::maxUploadFileSize();
$onSuccess = "() => { $('#tr_ca_form_upload').submit() }";
$onFailure = "() => { $('#tr_ca_upload_input').val(null) }";
$accessFileJS = "() => { trustedCA.checkAccessFile(this.files[0], $onSuccess, $onFailure) }";
$sizeFileJS = "trustedCA.checkFileSize(this.files[0], $maxSize, $accessFileJS, $onFailure)";
ob_start();
$gridBuilder->showFilter();
?>
<form enctype="multipart/form-data" method="POST" id= "tr_ca_form_upload" style="margin-left: 10px;">
    <div class="ui-btn ui-btn-primary ui-btn-icon-add crm-btn-toolbar-add tr_ca_upload_wrapper">
        <input class="tr_ca_upload_input" id= "tr_ca_upload_input" name="tr_ca_upload_comp_crm" type="file"
               onchange="<?= $sizeFileJS ?>">
        <?= Loc::getMessage('TR_CA_DOCS_CRM_ADD_DOC') ?>
    </div>
</form>
<?
$output = ob_get_contents();
ob_end_clean();
$APPLICATION->AddViewContent('pagetitle', $output, 100);
$reloadDoc = "trustedCA.reloadGrid('crm_docs_grid')";
?>
<a id="trca-reload-doc" onclick="<?= $reloadDoc ?>"></a>
