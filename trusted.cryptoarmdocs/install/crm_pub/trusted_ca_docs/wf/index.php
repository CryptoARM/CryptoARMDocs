<?php
use Bitrix\Main\Localization\Loc;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/trusted.cryptoarmdocs/install/crm_pub/trusted_ca_docs/wf/index.php");

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_CRM_WF_TITLE'));

$APPLICATION->IncludeComponent(
    'trusted:cryptoarm_docs_wf',
    '.default',
    array(
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/trusted_ca_docs/wf/',
        'SEF_URL_TEMPLATES' => array(
            'list' => '',
            'edit' => '#ID#/',
        )
    ),
    false
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';

