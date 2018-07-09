<?php
use TrustedNet\Docs;
use Bitrix\Main\Loader;
Loader::includeModule("trustednet.docs");

if (Docs\Utils::isSecure()) {
    $MESS["TN_DOCS_AJAX_CONTROLLER"] = "https://" . $_SERVER["HTTP_HOST"] . "/bitrix/components/trustednet/trustednet.docs/ajax.php";
} else {
    $MESS["TN_DOCS_AJAX_CONTROLLER"] = "http://" . $_SERVER["HTTP_HOST"]. "/bitrix/components/trustednet/trustednet.docs/ajax.php";
}

$MESS["TN_DOCS_ERROR_FILE_NOT_FOUND"] = "Files not found for the following documents:";
$MESS["TN_DOCS_ERROR_DOC_NOT_FOUND"] = "Documents with the following ids were not found: ";
$MESS["TN_DOCS_ERROR_DOC_BLOCKED"] = "The following documents are blocked:";
$MESS["TN_DOCS_ERROR_DOC_ROLE_SIGNED"] = "The following documents are already signed:";

$MESS["TN_DOCS_ALERT_NO_CLIENT"] = "Document signing requires installation of Trusted.eSign app. Visit our online shop to purchase a license https://cryptoarm.ru/shop/cryptoarm-gost";
$MESS["TN_DOCS_ALERT_HTTP_WARNING"] = "Document signing cannot not be performed without encrypted connection. Install SSL certificate and switch your site to the HTTPS.";
$MESS["TN_DOCS_ALERT_DOC_NOT_FOUND"] = "Documents with the following ids were not found";
$MESS["TN_DOCS_ALERT_DOC_BLOCKED"] = "The following documents are blocked";
$MESS["TN_DOCS_ALERT_REMOVE_ACTION_CONFIRM"] = "Are you sure you want to remove a document? This operation cannot be reverted.";
$MESS["TN_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE"] = "Files not nound for following documents:";
$MESS["TN_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_POST"] = "Remove those entries?";
$MESS["TN_DOCS_ALERT_LOST_DOC"] = "Files not nound for following documents:";
