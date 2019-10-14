<?php

$MESS["TR_CA_DOCS_MODULE_NAME"] = "КриптоАРМ Документы";
$MESS["TR_CA_DOCS_MODULE_DESCRIPTION"] = "Модуль работы с документами";
$MESS["TR_CA_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TR_CA_DOCS_PARTNER_URI"] = "https://trusted.ru";

$MESS["TR_CA_DOCS_CRM_MENU_TITLE"] = "КриптоАРМ Документы";

// email by order
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

// email documents
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_NAME"] = "КриптоАРМ Документы - отправка документов";
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAMES# - список названий документов
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"] = "#SITE_NAME#: Документы";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"] = "Документы: #FILE_NAMES#";

// email about share documents
$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_NAME"] = "КриптоАРМ Документы - уведомление о получении доступа к документу";
$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAME# - название документа
#SHARE_FROM# - автор документа
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_SUBJECT"] = "#SITE_NAME#: получен доступ к #FILE_NAME#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_BODY"] = "#SITE_NAME#: #SHARE_FROM# поделился документом #FILE_NAME#";

// email require sign
$MESS["TR_CA_DOCS_MAIL_EVENT_REQUIRED_SIGN_NAME"] = "КриптоАРМ Документы - уведомление о запросе подписи";
$MESS["TR_CA_DOCS_MAIL_EVENT_REQUIRED_SIGN_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAME# - названия документов
#REQUESTING_USER# - пользователь, запросивший подпись 
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_REQUIRED_SIGN_SUBJECT"] = "#SITE_NAME#: Вам пришел запрос на подпись документа";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_REQUIRED_SIGN_BODY"] = "#SITE_NAME#: Данные документы нужно подписать на нашем сайте: #FILE_NAME#.
<img src=\"#SITE_URL#/bitrix/components/trusted/docs/email.php?docs_id=#DOCS_ID#&user_id=#USER_ID#&rand=#RAND_UID#\" alt=\"\">
";

$MESS["TR_CA_DOCS_CANCEL_INSTALL"] = "Отменить установку";

$MESS["TR_CA_DOCS_BP_SIGN_TEMPLATE"] = "Подпись документа выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_AGREED_TEMPLATE"] = "Согласование документа выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_SERVICE_NOTE"] = "Служебная записка";
$MESS["TR_CA_DOCS_BP_ACQUAINTANCE"] = "Ознакомление с документом выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_MONEY_DEMAND"] = "Заявка на получение денежных средств";
$MESS["TR_CA_DOCS_BP_ORDER"] = "Приказ выбранным сотрудникам с прикреплением отчёта о выполнении";

$MESS["TR_CA_DOCS_SMALL_BUSINESS_OR_BUSINESS_REDACTION"] = "бизнес";
$MESS["TR_CA_DOCS_CORP_REDACTION"] = "Корпоративный";
$MESS["TR_CA_DOCS_ENTERPRISE_REDACTION"] = "Энтерпрайз";


