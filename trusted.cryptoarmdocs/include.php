<?php

global $APPLICATION;

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

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $module_id . "/config.php";

foreach (glob(TR_CA_DOCS_MODULE_DIR_CLASSES_GENERAL . "/*.php") as $filename) {
    require_once $filename;
}

foreach (glob(TR_CA_DOCS_MODULE_DIR_CLASSES . "/*.php") as $filename) {
    require_once $filename;
}

CJSCore::RegisterExt(
    "trusted_cryptoarm_docs",
    array(
        "js" => "/bitrix/js/" . TR_CA_DOCS_MODULE_ID . "/docs.js",
        "lang" => "/bitrix/modules/" . TR_CA_DOCS_MODULE_ID . "/lang/" . LANGUAGE_ID . "/javascript.php",
    )
);

CUtil::InitJSCore(array('trusted_cryptoarm_docs'));
CUtil::InitJSCore(array("jquery"));

$APPLICATION->SetAdditionalCss("/bitrix/themes/.default/" . TR_CA_DOCS_MODULE_ID . ".css");
$APPLICATION->AddHeadString("<link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600&display=swap' rel='stylesheet' type='text/css'>");
$APPLICATION->AddHeadString("<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet' type='text/css'>");
$APPLICATION->AddHeadString('<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet" />');
// End tag should be here because it's required by the bitrix marketplace demo mode
?>
