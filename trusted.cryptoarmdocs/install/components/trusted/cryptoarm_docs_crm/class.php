<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Trusted\CryptoARM\Docs;

Loader::includeModule('trusted.cryptoarmdocs');

class TrustedCACrmComponent extends CBitrixComponent
{
    const SEF_DEFAULT_TEMPLATES = array(
        'wf_list' => 'wf/',
        'wf_edit' => 'wf/#WF_ID#',
        'list' => '',
    );


    public function executeComponent() {
        if (empty($this->arParams['SEF_MODE']) || $this->arParams['SEF_MODE'] != 'Y') {
            ShowError('SEF not enabled');
            return;
        }

        if (empty($this->arParams['SEF_FOLDER'])) {
            ShowError('SEF folder is empty');
            return;
        }

        if (!is_array($this->arParams['SEF_URL_TEMPLATES'])) {
            $this->arParams['SEF_URL_TEMPLATES'] = array();
        }

        $sefTemplates = array_merge(self::SEF_DEFAULT_TEMPLATES, $this->arParams['SEF_URL_TEMPLATES']);

        $page = CComponentEngine::parseComponentPath(
            $this->arParams['SEF_FOLDER'],
            $sefTemplates,
            $arVariables
        );

        if (empty($page)) {
            $page = 'list';
        }

        $this->arResult = array(
            'SEF_FOLDER' => $this->arParams['SEF_FOLDER'],
            'SEF_URL_TEMPLATES' => $sefTemplates,
            'VARIABLES' => $arVariables,
            'WORKFLOW_TEMPLATES' => $this->getWorkflowTemplates(),
        );

        $this->includeComponentTemplate($page);
    }


    private function getWorkflowTemplates() {
        if (!Loader::includeModule('bizproc')) {
            return null;
        }
        return CBPDocument::GetWorkflowTemplatesForDocumentType(Docs\WorkflowDocument::getComplexDocumentType());
    }

}

