<?php

use Bitrix\Main\Config\Option;

$module_id = "trusted.cryptoarmdocsfree";
define("TR_CA_DOCS_MODULE_ID", $module_id);

define("TR_CA_HOST", preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']));

// Module directories
define("TR_CA_DOCS_MODULE_DIR", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . TR_CA_DOCS_MODULE_ID . "/");
define("TR_CA_DOCS_MODULE_DIR_CLASSES", TR_CA_DOCS_MODULE_DIR . "classes/");
define("TR_CA_DOCS_MODULE_DIR_CLASSES_GENERAL", TR_CA_DOCS_MODULE_DIR . "classes/general/");

// Operations log file
define("TR_CA_DOCS_LOG_FILE", TR_CA_DOCS_MODULE_DIR . "log.txt");

// Common lang file
define("TR_CA_DOCS_COMMON_LANG", TR_CA_DOCS_MODULE_DIR . "lang/" . LANGUAGE_ID . '/common.php');

// AJAX controller is also defined in lang/ru/javascript.php
define("TR_CA_DOCS_AJAX_CONTROLLER", "https://" . TR_CA_HOST . "/bitrix/components/trusted/docs/ajax.php");

// DB tables
define("DB_TABLE_DOCUMENTS", "tr_ca_docs");
define("DB_TABLE_PROPERTY", "tr_ca_docs_property");
define("DB_TABLE_REQUIRE", "tr_ca_docs_require");
define("DB_TABLE_TRANSACTION", "tr_ca_docs_transaction");

// TRANSACTION STATUS
define("DOC_TRANSACTION_NOT_COMPLETED", 0);
define("DOC_TRANSACTION_COMPLETED", 1);

// Transaction type
define("DOC_TRANSACTION_TYPE_SIGN", 0);
define("DOC_TRANSACTION_TYPE_VERIFY", 1);

// iBlock define
define("TR_CA_IB_TYPE_ID", "tr_ca_docs_form");

// Document types
define("DOC_TYPE_FILE", 0);
define("DOC_TYPE_SIGNED_FILE", 1);

// Sign type
define("DOC_SIGN_TYPE_COMBINED", 0);
define("DOC_SIGN_TYPE_DETACHED", 1);

// Sign standard
define("DOC_SIGN_STANDARD_CMS", "BES");
define("DOC_SIGN_STANDARD_CADES", "XLT1");
define("TR_CA_DOCS_SIGN_STANDARD", Option::get(TR_CA_DOCS_MODULE_ID, 'TR_CA_DOCS_SIGN_STANDARD', "BES"));

// Sign type in settings
define("TR_CA_DOCS_TYPE_SIGN", Option::get(TR_CA_DOCS_MODULE_ID, 'TR_CA_DOCS_TYPE_SIGN', 0));

// Time to auto unblock document in settings
define("TR_CA_DOCS_AUTO_UNBLOCK_TIME", Option::get(TR_CA_DOCS_MODULE_ID, 'TR_CA_DOCS_AUTO_UNBLOCK_TIME', 10));

// Document statuses
define("DOC_STATUS_NONE", 0);
define("DOC_STATUS_BLOCKED", 1);
define("DOC_STATUS_CANCELED", 2);
define("DOC_STATUS_ERROR", 3);

// Document access levels
define("DOC_SHARE_READ", "SHARE_READ");
define("DOC_SHARE_SIGN", "SHARE_SIGN");

// License request url
define("LICENSE_SERVICE_URL" , "https://dev.license.trusted.plus/license/account");
define("LICENSE_SERVICE_REGISTER_NEW_ACCOUNT_NUMBER" , LICENSE_SERVICE_URL . "/new");
define("LICENSE_SERVICE_ACTIVATE_CODE" , LICENSE_SERVICE_URL . '/activate/');
define("LICENSE_SERVICE_ACCOUNT_CHECK_BALANCE" , LICENSE_SERVICE_URL . '/check/');
define("LICENSE_SERVICE_ACCOUNT_GET_ONCE_JWT_TOKEN" , LICENSE_SERVICE_URL . '/issuetoken/');
define("LICENSE_SERVICE_ACCOUNT_HISTORY" , LICENSE_SERVICE_URL . '/operations/');

define("LICENSE_ACCOUNT_NUMBER", Option::get(TR_CA_DOCS_MODULE_ID, 'LICENSE_ACCOUNT_NUMBER', ''));
define("PROVIDE_LICENSE", Option::get(TR_CA_DOCS_MODULE_ID, 'PROVIDE_LICENSE', ''));

define('TR_CA_DB_TIME_FORMAT', 'YYYY-MM-DD HH:MI:SS');

// define("TR_CA_DOCS_TEMPLATE_ID", "tr_ca_docs_template_id");

define("TR_CA_DOCS_MODULES_OUT_OF_DATE", "tr_ca_docs_modules_out_of_date");
define("TR_CA_DOCS_MODULES_WERE_NOT_INSTALLED", "tr_ca_docs_modules_were_not_installed");

define("TR_CA_DOCS_PATH_TO_POST_ICONS", "https://" . TR_CA_HOST . "/bitrix/themes/.default/icons/" . TR_CA_DOCS_MODULE_ID . "/");
