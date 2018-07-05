<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trustednet.docs/lang/en/install/index.php";

$MESS["TN_DOCS_MODULE_NAME"] = "TrustedNet Documents";
$MESS["TN_DOCS_MODULE_DESCRIPTION"] = "Модуль работы с документами";
$MESS["TN_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TN_DOCS_PARTNER_URI"] = "https://trusted.ru";

$MESS["TN_DOCS_MAIL_EVENT_NAME_RU"] = "TrustedNet Documents - рассылка документов по заказам";
$MESS["TN_DOCS_MAIL_EVENT_DESCRIPTION_RU"] = "
#EMAIL# - EMail получателя сообщения
#ORDER_USER# - имя пользователя совершившего заказ
#ORDER_ID# - номер заказа
#FILE_NAMES# - список названий документов
";
$MESS["TN_DOCS_MAIL_TEMPLATE_SUBJECT_RU"] = "#SITE_NAME#: Документы по заказу №#ORDER_ID#";
$MESS["TN_DOCS_MAIL_TEMPLATE_BODY_RU"] = "
Документы: #FILE_NAMES# по заказу №#ORDER_ID# для #ORDER_USER#.
<img src=\"#SITE_URL#/bitrix/components/trustednet/trustednet.docs/email.php?order_id=#ORDER_ID#\" alt=\"\">
";

$MESS["TN_DOCS_CANCEL_INSTALL"] = "Отменить установку";

