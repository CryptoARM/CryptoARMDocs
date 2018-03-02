<?php

define("TN_DOCS_MODULE_ID", "trustednet.docs");

// Module directories
define("TN_DOCS_MODULE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . TN_DOCS_MODULE_ID . "/");
define("TN_DOCS_MODULE_DIR_CLASSES", TN_DOCS_MODULE_DIR . "classes/");
define("TN_DOCS_MODULE_DIR_CLASSES_GENERAL", TN_DOCS_MODULE_DIR . "classes/general/");

define("TN_DOCS_AJAX_CONTROLLER", "https://" . $_SERVER["HTTP_HOST"]. "/bitrix/components/trustednet/trustednet.docs/ajax.php");

// DB tables
define("DB_TABLE_DOCUMENTS", "trn_docs");
define("DB_TABLE_PROPERTY", "trn_docs_property");
define("DB_TABLE_STATUS", "trn_docs_status");

// Document types
define("DOC_TYPE_FILE", 0);
define("DOC_TYPE_SIGNED_FILE", 1);

// Document statuses
define("DOC_STATUS_BLOCKED", 0);
define("DOC_STATUS_CANCEL", 1);
define("DOC_STATUS_ERROR", 2);

