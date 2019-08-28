<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
    'GROUPS' => [
        'SETTINGS' => [
            'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_SETTINGS_GROUP_NAME"),
        ],
    ],
    'PARAMETERS' => [
        'PERMISSION_REMOVE' => [
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_SETTINGS_PERMISSION_REMOVE"),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 20,
        ],
    ]
];
