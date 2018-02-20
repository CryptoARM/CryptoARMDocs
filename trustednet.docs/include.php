<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trustednet.docs/config.php";

foreach (glob(TN_DOCS_MODULE_DIR_CLASSES_GENERAL . "/*.php") as $filename) {
    require_once $filename;
}

foreach (glob(TN_DOCS_MODULE_DIR_CLASSES . "/*.php") as $filename) {
    require_once $filename;
}

