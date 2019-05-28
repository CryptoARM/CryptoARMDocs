<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/install/crm_pub/trusted_ca_docs/index.php');

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_CRM_TITLE'));

$APPLICATION->IncludeComponent(
    'trusted:cryptoarm_docs_crm_personal',
    '.default',
    array(
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/trusted_ca_docs/',
        'SEF_URL_TEMPLATES' => array(
            'list' => '',
            'edit' => '#ID#/',
        )
    ),
    false
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';

