<?php
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

//checks the name of currently installed core from highest possible version to lowest
$coreIds = array(
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
);
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $coreId . "/";
    if(file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}
Loader::includeModule($module_id);

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

$eventEmailRead = Option::get($module_id, "EVENT_EMAIL_READ", "");

$orderId = (int)$_GET["order_id"];
if ($orderId) {
    $docIds = Docs\Database::getIdsByOrder($orderId);
    foreach ($docIds as $docId) {
        $doc = Docs\Database::getDocumentById($docId);
        $props = $doc->getProperties();
        if ($emailProp = $props->getPropByType("EMAIL")) {
            $emailProp->setValue("READ");
        }
        $doc->save();

        // Update order status once in this foreach
        if ($eventEmailRead) {
            Docs\DocumentsByOrder::changeOrderStatus($doc, $eventEmailRead);
            $eventEmailRead = "";
        }
    }
}

$image = "email.png";
$imageInfo = getimagesize($image);
$imageMimetype = $imageInfo["mime"];

header('Content-type: '.$imageMimetype);
echo file_get_contents($image);

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";

