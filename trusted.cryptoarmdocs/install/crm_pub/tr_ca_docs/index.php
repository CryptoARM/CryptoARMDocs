<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

if ($USER->IsAuthorized()) {
    $APPLICATION->IncludeComponent(
        'trusted:cryptoarm_docs_upload',
        '.default',
        array(
            'FILES' => array('tr_ca_upload_comp_crm'),
            'PROPS' => array(
                'USER' => $USER->GetID(),
            ),
        ),
        false
    );

    $APPLICATION->IncludeComponent(
        'trusted:cryptoarm_docs_crm',
        '.default',
        array(
            'SEF_MODE' => 'Y',
            'SEF_FOLDER' => '/tr_ca_docs/',
        ),
        false
    );
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
