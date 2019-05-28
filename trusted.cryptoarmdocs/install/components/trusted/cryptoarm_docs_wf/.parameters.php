<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
    'PARAMETERS' => array(
        'SEF_MODE' => array(
            'list' => array(
                'NAME' => Loc::getMessage('TR_CA_DOCS_CRM_DETAILS_URL_TEMPLATE'),
                'DEFAULT' => '',
                'VARIABLES' => array('ID'),
            ),
            'edit' => array(
                'NAME' => Loc::getMessage('TR_CA_DOCS_CRM_EDIT_URL_TEMPLATE'),
                'DEFAULT' => '#ID#/',
                'VARIABLES' => array('ID'),
            ),
        ),
    ),
);

