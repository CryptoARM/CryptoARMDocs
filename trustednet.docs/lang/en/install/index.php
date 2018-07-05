<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trustednet.docs/lang/ru/install/index.php";

$MESS["TN_DOCS_MODULE_NAME"] = "TrustedNet Documents";
$MESS["TN_DOCS_MODULE_DESCRIPTION"] = "Document management solution";
$MESS["TN_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TN_DOCS_PARTNER_URI"] = "https://trusted.ru";

$MESS["TN_DOCS_MAIL_EVENT_NAME_EN"] = "TrustedNet Documents - documents by order";
$MESS["TN_DOCS_MAIL_EVENT_DESCRIPTION_EN"] = "
#EMAIL# - recipient email
#ORDER_USER# - name of order buyer
#ORDER_ID# - id of the order
#FILE_NAMES# - list of document names
";
$MESS["TN_DOCS_MAIL_TEMPLATE_SUBJECT"] = "#SITE_NAME#: Documents by order #ORDER_ID#";
$MESS["TN_DOCS_MAIL_TEMPLATE_BODY"] = "
Documents: #FILE_NAMES# by order №#ORDER_ID# for #ORDER_USER#.
<img src=\"#SITE_URL#/bitrix/components/trustednet/trustednet.docs/email.php?order_id=#ORDER_ID#\" alt=\"\">
";

$MESS["TN_DOCS_CANCEL_INSTALL"] = "Cancel installation";

