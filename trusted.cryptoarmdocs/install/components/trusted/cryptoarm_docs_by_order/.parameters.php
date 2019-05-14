<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
    'GROUPS' => array(
        'SETTINGS' => array(
            'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_SETTINGS_GROUP_NAME"),
        ),
    ),
    'PARAMETERS' => array(
        'AJAX_MODE' => array(),
/*        'ELEMENTS_ON_PAGE' => array(
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_SETTINGS_PARAMETERS_ON_PAGE"),
            'TYPE' => 'STRING',
            'DEFAULT' => 20,
        ),*/
    )
);
