<?php

define('MODULE_ID', 'trusted.cryptoarmdocs');
define('ENTITY', '\Trusted\CryptoARM\Docs\WorkflowDocument');

$fp = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bizprocdesigner/admin/bizproc_wf_settings.php';
if (is_file($fp)) {
    require($fp);
}

