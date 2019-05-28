<?php

define('MODULE_ID', 'trusted.cryptoarmdocs');
define('ENTITY', '\Trusted\CryptoARM\Docs\WorkflowDocument');

$fp = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bizproc/admin/bizproc_selector.php';
if (is_file($fp)) {
    require($fp);
}

