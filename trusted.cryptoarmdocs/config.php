<?php

use Bitrix\Main\Config\Option;

define("TR_CA_DOCS_MODULE_ID", "trusted.cryptoarmdocs");

// Module directories
define("TR_CA_DOCS_MODULE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . TR_CA_DOCS_MODULE_ID . "/");
define("TR_CA_DOCS_MODULE_DIR_CLASSES", TR_CA_DOCS_MODULE_DIR . "classes/");
define("TR_CA_DOCS_MODULE_DIR_CLASSES_GENERAL", TR_CA_DOCS_MODULE_DIR . "classes/general/");

// Operations log file
define("TR_CA_DOCS_LOG_FILE", TR_CA_DOCS_MODULE_DIR . "log.txt");

// AJAX controller is also defined in lang/ru/javascript.php & lang/en/javascript.php
define("TR_CA_DOCS_AJAX_CONTROLLER", "https://" . $_SERVER["HTTP_HOST"]. "/bitrix/components/trusted/docs/ajax.php");

// DB tables
define("DB_TABLE_DOCUMENTS", "tr_ca_docs");
define("DB_TABLE_PROPERTY", "tr_ca_docs_property");

// Document types
define("DOC_TYPE_FILE", 0);
define("DOC_TYPE_SIGNED_FILE", 1);

// Document statuses
define("DOC_STATUS_NONE", 0);
define("DOC_STATUS_BLOCKED", 1);
define("DOC_STATUS_CANCELED", 2);
define("DOC_STATUS_ERROR", 3);

// License request url
define("LICENSE_SERVICE_URL" , "https://licensesvc.trusted.ru/license/account");
define("LICENSE_SERVICE_REGISTER_NEW_ACCOUNT_NUMBER" , LICENSE_SERVICE_URL . "/new");
define("LICENSE_SERVICE_ACTIVATE_CODE" , LICENSE_SERVICE_URL . '/activate/');
define("LICENSE_SERVICE_ACCOUNT_CHECK_BALANCE" , LICENSE_SERVICE_URL . '/check/');
define("LICENSE_SERVICE_GET_JWT_TOKEN" , "/issuetoken");

define("LICENSE_ACCOUNT_NUMBER", Option::get(TR_CA_DOCS_MODULE_ID, 'LICENSE_ACCOUNT_NUMBER', ''));

