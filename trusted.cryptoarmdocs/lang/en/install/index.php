<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trusted.cryptoarmdocs/lang/ru/install/index.php";

$MESS["TR_CA_DOCS_MODULE_NAME"] = "CryptoARM Documents";
$MESS["TR_CA_DOCS_MODULE_DESCRIPTION"] = "Document management solution";
$MESS["TR_CA_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TR_CA_DOCS_PARTNER_URI"] = "https://trusted.ru";

$MESS["TR_CA_DOCS_MAIL_EVENT_NAME_EN"] = "CryptoARM Documents - documents by order";
$MESS["TR_CA_DOCS_MAIL_EVENT_DESCRIPTION_EN"] = "
#EMAIL# - recipient email
#ORDER_USER# - name of order buyer
#ORDER_ID# - id of the order
#FILE_NAMES# - list of document names
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SUBJECT"] = "#SITE_NAME#: Documents by order #ORDER_ID#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_BODY"] = "
Documents: #FILE_NAMES# by order №#ORDER_ID# for #ORDER_USER#.
<img src=\"#SITE_URL#/bitrix/components/trusted/docs/email.php?order_id=#ORDER_ID#\" alt=\"\">
";

$MESS["TR_CA_DOCS_MAIL_EVENT_TO_NAME_EN"] = "CryptoARM Documents - documents";
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION_EN"] = "
#EMAIL# - recipient email
#FILE_NAMES# - list of document names
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"] = "#SITE_NAME#: Documents";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"] = "Documents: #FILE_NAMES#";

$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_NAME_EN"] = "CryptoARM Documents - notification";
$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION_EN"] = "
#EMAIL# - recipient email
#FILE_NAME# - document name
#SHARE_FROM# - document owner
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_SUBJECT"] = "#SITE_NAME#: shared document #FILE_NAME#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_BODY"] = "#SITE_NAME#: #SHARE_FROM# shared with you a document #FILE_NAME#";

$MESS["TR_CA_DOCS_CANCEL_INSTALL"] = "Cancel installation";
