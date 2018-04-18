<?php

$MESS["TN_DOCS_MODULE_NAME"] = "TrustedNet Documents";
$MESS["TN_DOCS_MODULE_DESCRIPTION"] = "Модуль работы с документами TrustedNet Documents";
$MESS["TN_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TN_DOCS_PARTNER_URI"] = "http://www.trusted.ru";

$MESS["TN_DOCS_MAIL_EVENT_NAME"] = "TrustedNet Documents - рассылка документов по заказам";
$MESS["TN_DOCS_MAIL_EVENT_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#ORDER_USER# - имя пользователя совершившего заказ
#ORDER_ID# - номер заказа
#FILE_NAME# - название документа
";
$MESS["TN_DOCS_MAIL_TEMPLATE_SUBJECT"] = "#SITE_NAME#: Документ по заказу №#ORDER_ID#";
$MESS["TN_DOCS_MAIL_TEMPLATE_BODY"] = "
Документ #FILE_NAME# по заказу №#ORDER_ID# для #ORDER_USER#.
<img src=\"#SITE_URL#/bitrix/components/trustednet/trustednet.docs/email.php?order_id=#ORDER_ID#\" alt=\"\">
";

$MESS["TN_DOCS_CANCEL_INSTALL"] = "Отменить установку";

