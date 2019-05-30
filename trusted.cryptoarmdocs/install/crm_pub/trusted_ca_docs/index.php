<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->IncludeComponent(
    'trusted:cryptoarm_docs_crm',
    '.default',
    array(
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/trusted_ca_docs/',
    ),
    false
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';

