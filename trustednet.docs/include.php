<?php

global $APPLICATION;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trustednet.docs/config.php";

foreach (glob(TN_DOCS_MODULE_DIR_CLASSES_GENERAL . "/*.php") as $filename) {
    require_once $filename;
}

foreach (glob(TN_DOCS_MODULE_DIR_CLASSES . "/*.php") as $filename) {
    require_once $filename;
}

CJSCore::RegisterExt(
    "socketio",
    array(
        "js" => "/bitrix/js/trustednet.docs/socket.io.js",
    )
);

CJSCore::RegisterExt(
    "trustednet_docs",
    array(
        "js" => "/bitrix/js/trustednet.docs/docs.js",
        "lang" => "/bitrix/modules/trustednet.docs/lang/ru/javascript.php",
    )
);

CUtil::InitJSCore(array('socketio'));
CUtil::InitJSCore(array('trustednet_docs'));
CUtil::InitJSCore(array("jquery2"));

// End tag should be here because it's required by the bitrix marketplace demo mode
?>
