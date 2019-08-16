<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

use Bitrix\Main\Loader;

if (CModule::IncludeModuleEx('trusted.cryptoarmdocs') == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    return false;
};

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

