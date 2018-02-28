<?php

define("TN_DOCS_MODULE_ID", "trustednet.docs");

// Module directories
define("TN_DOCS_MODULE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . TN_DOCS_MODULE_ID . "/");
define("TN_DOCS_MODULE_DIR_CLASSES", TN_DOCS_MODULE_DIR . "classes/");
define("TN_DOCS_MODULE_DIR_CLASSES_GENERAL", TN_DOCS_MODULE_DIR . "classes/general/");

//define("TN_DOCS_AJAX_CONTROLLER", "https://localhost:8088/");
define("TN_DOCS_AJAX_CONTROLLER", "https://" . $_SERVER["HTTP_HOST"]. "/bitrix/components/trustednet/trustednet.docs/ajax.php");

// DB tables
define("DB_TABLE_DOCUMENTS", "trn_docs");
define("DB_TABLE_PROPERTY", "trn_docs_property");
define("DB_TABLE_STATUS", "trn_docs_status");

// Document types
define("DOCUMENT_TYPE_FILE", 0);
define("DOCUMENT_TYPE_SIGNATURE", 1);

// Document statuses
define("DOCUMENT_STATUS_DONE", 0);
define("DOCUMENT_STATUS_PROCESSING", 1);
define("DOCUMENT_STATUS_CANCEL", 2);
define("DOCUMENT_STATUS_ERROR", 3);

