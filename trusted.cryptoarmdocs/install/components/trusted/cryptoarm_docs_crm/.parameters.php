<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
    'PARAMETERS' => array(
        'SEF_MODE' => array(
            'list' => array(
                'NAME' => Loc::getMessage('TR_CA_DOCS_CRM_LIST'),
                'DEFAULT' => '',
                'VARIABLES' => array(),
            ),
            'detail' => array(
                'NAME' => Loc::getMessage('TR_CA_DOCS_CRM_DETAIL'),
                'DEFAULT' => '#ID#/',
                'VARIABLES' => array('ID'),
            ),
            'wf_list' => array(
                'NAME' => Loc::getMessage('TR_CA_DOCS_CRM_WF_LIST'),
                'DEFAULT' => 'wf/',
                'VARIABLES' => array(),
            ),
            'wf_edit' => array(
                'NAME' => Loc::getMessage('TR_CA_DOCS_CRM_WF_EDIT'),
                'DEFAULT' => 'wf/#WF_ID#',
                'VARIABLES' => array('WF_ID'),
            ),
        ),
    ),
);

