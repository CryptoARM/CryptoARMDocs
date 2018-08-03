<?php
use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
    'GROUPS' => array(
        'SETTINGS' => array(
            'NAME' => Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_SETTINGS_GROUP_NAME"),
        ),
    ),
    'PARAMETERS' => array(
        'CHECK_ORDER_PROPERTY' => array(
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_SETTINGS_PARAMETERS_CHECK_ORDER_PROPERTY"),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        /*'ELEMENTS_ON_PAGE' => array(
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_SETTINGS_PARAMETERS_NAME"),
            'TYPE' => 'STRING',
            'DEFAULT' => 20,
        ),*/
    )
);
