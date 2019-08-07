<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
    'GROUPS' => array(
        'SETTINGS' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_USER_SETTINGS_GROUP_NAME'),
            'SORT' => 10,
        ),
    ),
    'PARAMETERS' => array(
        'CHECK_ORDER_PROPERTY' => array(
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage(
                'TR_CA_DOCS_COMP_DOCS_BY_USER_SETTINGS_PARAMETERS_CHECK_ORDER_PROPERTY'
            ),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ),
        'ALLOW_REMOVAL' => array(
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage(
                'TR_CA_DOCS_COMP_DOCS_BY_USER_SETTINGS_PARAMETERS_ALLOW_REMOVAL'
            ),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ),
        'ALLOW_ADDING' => array(
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage(
                'TR_CA_DOCS_COMP_DOCS_BY_USER_SETTINGS_PARAMETERS_ALLOW_ADDING'
            ),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ),
        /*'ELEMENTS_ON_PAGE' => array(
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SETTINGS_PARAMETERS_NAME"),
            'TYPE' => 'STRING',
            'DEFAULT' => 20,
        ),*/
        'AJAX_MODE' => array(),
    ),
);
