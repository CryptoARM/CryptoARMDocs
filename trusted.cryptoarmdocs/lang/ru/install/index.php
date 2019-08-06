<?php

$MESS["TR_CA_DOCS_MODULE_NAME"] = "КриптоАРМ Документы";
$MESS["TR_CA_DOCS_MODULE_DESCRIPTION"] = "Модуль работы с документами";
$MESS["TR_CA_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TR_CA_DOCS_PARTNER_URI"] = "https://trusted.ru";

$MESS["TR_CA_DOCS_CRM_MENU_TITLE"] = "КриптоАРМ Документы";

$MESS["TR_CA_DOCS_MAIL_EVENT_NAME"] = "КриптоАРМ Документы - рассылка документов по заказам";
$MESS["TR_CA_DOCS_MAIL_EVENT_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#ORDER_USER# - имя пользователя, совершившего заказ
#ORDER_ID# - номер заказа
#FILE_NAMES# - список названий документов
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SUBJECT"] = "#SITE_NAME#: Документы по заказу №#ORDER_ID#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_BODY"] = "
Документы: #FILE_NAMES# по заказу №#ORDER_ID# для #ORDER_USER#.
<img src=\"#SITE_URL#/bitrix/components/trusted/docs/email.php?order_id=#ORDER_ID#&rand=#RAND_UID#\" alt=\"\">
";

$MESS["TR_CA_DOCS_MAIL_EVENT_TO_NAME"] = "КриптоАРМ Документы - отправка документов";
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAMES# - список названий документов
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"] = "#SITE_NAME#: Документы";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"] = "Документы: #FILE_NAMES#";

$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_NAME"] = "КриптоАРМ Документы - уведомление";
$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAME# - название документа
#SHARE_FROM# - автор документа
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_SUBJECT"] = "#SITE_NAME#: получен доступ к #FILE_NAME#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_BODY"] = "#SITE_NAME#: #SHARE_FROM# поделился документом #FILE_NAME#";

$MESS["TR_CA_DOCS_CANCEL_INSTALL"] = "Отменить установку";

$MESS["TR_CA_DOCS_BP_SIGN_TEMPLATE"] = "Выбор ответственных за подпись документа";
$MESS["TR_CA_DOCS_BP_AGREED_TEMPLATE"] = "Отправить документ на согласование";