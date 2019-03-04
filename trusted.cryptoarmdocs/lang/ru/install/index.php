<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/trusted.cryptoarmdocs/lang/en/install/index.php";

$MESS["TR_CA_DOCS_MODULE_NAME"] = "КриптоАРМ Документы";
$MESS["TR_CA_DOCS_MODULE_DESCRIPTION"] = "Модуль работы с документами";
$MESS["TR_CA_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TR_CA_DOCS_PARTNER_URI"] = "https://trusted.ru";

$MESS["TR_CA_DOCS_MAIL_EVENT_NAME_RU"] = "КриптоАРМ Документы - рассылка документов по заказам";
$MESS["TR_CA_DOCS_MAIL_EVENT_DESCRIPTION_RU"] = "
#EMAIL# - EMail получателя сообщения
#ORDER_USER# - имя пользователя совершившего заказ
#ORDER_ID# - номер заказа
#FILE_NAMES# - список названий документов
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SUBJECT"] = "#SITE_NAME#: Документы по заказу №#ORDER_ID#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_BODY"] = "
Документы: #FILE_NAMES# по заказу №#ORDER_ID# для #ORDER_USER#.
<img src=\"#SITE_URL#/bitrix/components/trusted/docs/email.php?order_id=#ORDER_ID#\" alt=\"\">
";

$MESS["TR_CA_DOCS_MAIL_EVENT_TO_NAME_RU"] = "КриптоАРМ Документы - отправка документов";
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION_RU"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAMES# - список названий документов
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"] = "#SITE_NAME#: Документы";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"] = "Документы: #FILE_NAMES#";

$MESS["TR_CA_DOCS_CANCEL_INSTALL"] = "Отменить установку";
