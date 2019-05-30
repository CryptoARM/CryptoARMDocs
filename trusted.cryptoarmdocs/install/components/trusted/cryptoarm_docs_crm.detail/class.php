<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;
use Trusted\CryptoARM\Docs;

Loader::includeModule('trusted.cryptoarmdocs');

class TrustedCACrmDetailsComponent extends CBitrixComponent
{
    const FORM_ID = 'TR_CA_DOC_DETAIL';
    const BIZPROC_TAB_ID = 'bizproc';


    public function executeComponent() {

        $docId = $this->arParams['ID'];

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

        $this->arResult = array(
            'FORM_ID' => self::FORM_ID,
            'TACTILE_FORM_ID' => 'FORM_ID',
            'GRID_ID' => 'GRID_ID',
            'DOC' => $doc,
            'BIZPROC_TAB' => array(
                'ID' => self::BIZPROC_TAB_ID,
                'HTML' => $this->getBizprocTabHtml($docId),
            ),
        );

        $this->includeComponentTemplate();
    }


    private function getBizprocTabHtml($docId)
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


    private function getWorkflowListHtml($docId)
    {
        global $APPLICATION;

        $context = Context::getCurrent();
        $request = $context->getRequest();

        $requestUri = new Uri($request->getRequestUri());
        $requestUri->addParams(array(
            self::FORM_ID . '_active_tab' => self::BIZPROC_TAB_ID,
        ));
        $requestUri->deleteParams(array('action'));

        $logUrlTemplate = CHTTP::urlAddParams($requestUri->getUri(), array('bizproc_log' => '#ID#'));
        $taskUrlTemplate = CHTTP::urlAddParams($requestUri->getUri(), array('bizproc_task' => '#ID#'));
        $startUrl = CHTTP::urlAddParams($requestUri->getUri(), array('bizproc_start' => 'Y'));

        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:bizproc.document',
            '',
            array(
                'MODULE_ID' => 'trusted.cryptoarmdocs',
                'ENTITY' => Docs\WorkflowDocument::class,
                'DOCUMENT_TYPE' => 'TR_CA_DOC',
                'DOCUMENT_ID' => $docId,
                'TASK_EDIT_URL' => $taskUrlTemplate,
                'WORKFLOW_LOG_URL' => $logUrlTemplate,
                'WORKFLOW_START_URL' => $startUrl,
                'POST_FORM_URI' => '',
                'back_url' => $requestUri->getUri(),
                'SET_TITLE' => 'N'
            ),
            null,
            array('HIDE_ICONS' => 'Y')
        );
        return ob_get_clean();
    }

    private function getWorkflowTaskHtml($taskId)
    {
        global $APPLICATION;

        $context = Context::getCurrent();
        $request = $context->getRequest();

        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:bizproc.task',
            '',
            Array(
                'TASK_ID' => $taskId,
                'USER_ID' => 0,
                'WORKFLOW_ID' => '',
                'DOCUMENT_URL' => $request->getRequestUri(),
                'SET_TITLE' => 'N',
                'SET_NAV_CHAIN' => 'N'
            ),
            null,
            array('HIDE_ICONS' => 'Y')
        );
        return ob_get_clean();
    }

    private function getWorkflowStartHtml($docId)
    {
        global $APPLICATION;

        $context = Context::getCurrent();
        $request = $context->getRequest();

        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:bizproc.workflow.start',
            '',
            array(
                'MODULE_ID' => 'trusted.cryptoarmdocs',
                'ENTITY' => Docs\WorkflowDocument::class,
                'DOCUMENT_TYPE' => 'TR_CA_DOC',
                'DOCUMENT_ID' => $docId,
                'TEMPLATE_ID' => $request->get('workflow_template_id'),
                'SET_TITLE'	=>	'N'
            ),
            null,
            array('HIDE_ICONS' => 'Y')
        );
        return ob_get_clean();
    }

    private function getWorkflowLogHtml($docId, $workflowId)
    {
        global $APPLICATION;

        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:bizproc.log',
            '',
            array(
                'MODULE_ID' => 'trusted.cryptoarmdocs',
                'ENTITY' => Docs\WorkflowDocument::class,
                'DOCUMENT_TYPE' => 'TR_CA_DOC',
                'COMPONENT_VERSION' => 2,
                'DOCUMENT_ID' => $docId,
                'ID' => $workflowId,
                'SET_TITLE' => 'N',
                'INLINE_MODE' => 'Y',
                'AJAX_MODE' => 'N',
                'NAME_TEMPLATE' => CSite::GetNameFormat()
            ),
            null,
            array('HIDE_ICONS' => 'Y')
        );
        return ob_get_clean();
    }

}

