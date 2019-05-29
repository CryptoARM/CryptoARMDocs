<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->IncludeComponent(
    'trusted:cryptoarm_docs_crm',
    '.default',
    array(
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/trusted_ca_docs/',
        // 'SEF_URL_TEMPLATES' => array(
        //     'list' => '',
        //     'detail' => '#ID#/',
        //     'detail' => '#ID#/',
        // ),
    ),
    false
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';

