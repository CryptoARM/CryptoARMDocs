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
<img src=\"#SITE_URL#/bitrix/components/trusted/trusted.cryptoarmdocs/email.php?order_id=#ORDER_ID#\" alt=\"\">
";

$MESS["TR_CA_DOCS_CANCEL_INSTALL"] = "Cancel installation";

