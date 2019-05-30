<?php

defined('B_PROLOG_INCLUDED') || die;

$APPLICATION->IncludeComponent(
    'trusted:cryptoarm_docs_crm.detail',
    '',
    array(
        'ID' => $arResult['VARIABLES']['ID'],
    )
);

