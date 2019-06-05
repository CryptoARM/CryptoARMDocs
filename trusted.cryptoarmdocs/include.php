<?php

global $APPLICATION;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trusted.cryptoarmdocs/config.php";

foreach (glob(TR_CA_DOCS_MODULE_DIR_CLASSES_GENERAL . "/*.php") as $filename) {
    require_once $filename;
}

foreach (glob(TR_CA_DOCS_MODULE_DIR_CLASSES . "/*.php") as $filename) {
    require_once $filename;
}

// Without this classes i couldn't use $doc->remove in the index.php
// Probably a bug somewhere
require_once TR_CA_DOCS_MODULE_DIR . 'install/activities/trustedcasign/trustedcasign.php';
require_once TR_CA_DOCS_MODULE_DIR . 'install/activities/trustedcaapprove/trustedcaapprove.php';

CJSCore::RegisterExt(
    "socketio",
    array(
        "js" => "/bitrix/js/trusted.cryptoarmdocs/socket.io.js",
    )
);

CJSCore::RegisterExt(
    "trusted_cryptoarm_docs",
    array(
        "js" => "/bitrix/js/trusted.cryptoarmdocs/docs.js",
        "lang" => "/bitrix/modules/trusted.cryptoarmdocs/lang/" . LANGUAGE_ID . "/javascript.php",
    )
);

CUtil::InitJSCore(array('socketio'));
CUtil::InitJSCore(array('trusted_cryptoarm_docs'));
CUtil::InitJSCore(array("jquery"));

// End tag should be here because it's required by the bitrix marketplace demo mode
?>
