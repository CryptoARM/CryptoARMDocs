<?php

$MESS["TR_CA_DOCS_OPT_TAB"] = "Настройки";
$MESS["TR_CA_DOCS_OPT_TAB_TITLE"] = "Настройки модуля КриптоАРМ Документы";
$MESS["TR_CA_DOCS_LICENSE_TAB"] = "Предоставление лицензий";
$MESS["TR_CA_DOCS_LICENSE_TAB_TITLE"] = "Предоставление пользователям одноразовых лицензий";
$MESS["TR_CA_DOCS_ORDER_TAB"] = "Документы по заказам";
$MESS["TR_CA_DOCS_ORDER_TAB_TITLE"] = "Настройки документов с привязкой к заказу";
$MESS["TR_CA_DOCS_FORM_TAB"] = "Формы";
$MESS["TR_CA_DOCS_FORM_TAB_TITLE"] = "Настройки рассылки писем с документами по формам";
$MESS["TR_CA_DOCS_LOGS_TAB"] = "Журнал";
$MESS["TR_CA_DOCS_LOGS_TAB_TITLE"] = "Журнал операций с документами";

$MESS["TR_CA_DOCS_DOCS_DIR"] = "Директория для хранения документов";
$MESS["TR_CA_DOCS_DOCS_DIR_SELECT"] = "Выбрать директорию";

$MESS["TR_CA_DOCS_DOCS_DIR_NO_ACCESS_TO_DIRECTORY"] = "Нет доступа к директории.";
$MESS["TR_CA_DOCS_DOCS_DIR_CANNOT_USE_SYSTEM_DIRECTORY"] = "Нельзя сохранять файлы внутри системной директории.";

$MESS["TR_CA_DOCS_LICENSE_HEADER_SETTINGS"] = "Настройки";
$MESS["TR_CA_DOCS_LICENSE_HEADER_STATISTICS"] = "Статистика";

$MESS["TR_CA_DOCS_LICENSE_ENABLE"] = "Включить предоставление пользователям одноразовых лицензий на подпись документов";
$MESS["TR_CA_DOCS_LICENSE_ACCOUNT_NUMBER"] = "Номер счета:";
$MESS["TR_CA_DOCS_LICENSE_INPUT_ACCOUNT_NUMBER_PLACEHOLDER"] = "Введите сюда номер счета";
$MESS["TR_CA_DOCS_LICENSE_INPUT_ACCOUNT_NUMBER"] = "Сохранить";
$MESS["TR_CA_DOCS_LICENSE_CREATE_NEW_ACCOUNT_NUMBER"] = "Получить";
$MESS["TR_CA_DOCS_LICENSE_JWT_TOKEN"] = "Активировать лицензию на счет";
$MESS["TR_CA_DOCS_LICENSE_TEXTAREA_JWT_TOKEN"] = "Скопируйте сюда лицензию";
$MESS["TR_CA_DOCS_LICENSE_ACTIVATE_JWT_TOKEN"] = "Активировать";
$MESS["TR_CA_DOCS_LICENSE_NUMBER_OF_AVAILABLE_TRANSACTION"] = "Доступных операций подписи на счете";

$MESS["TR_CA_DOCS_LICENSE_CREATE_NEW_ACCOUNT_NUMBER_ALERT"] = "Ваш счет ";
$MESS["TR_CA_DOCS_LICENSE_CREATE_NEW_ACCOUNT_NUMBER_ALERT2"] = " успешно сформирован. В целях безопасности рекомендуем дополнительно сохранить его для возможности восстановления.";
$MESS["TR_CA_DOCS_LICENSE_ACTIVATE_JWT_ACCOUNT_DOES_NOT_EXIST"] = "Номера счета не существует.";
$MESS["TR_CA_DOCS_LICENSE_ACTIVATE_JWT_EMPTY"] = "Поле с лицензией пустое.";
$MESS["TR_CA_DOCS_LICENSE_ACTIVATE_JWT_ALREADY_ACTIVATED"] = "Данная лицензия уже активирована.";
$MESS["TR_CA_DOCS_LICENSE_ACTIVATE_JWT_FORMAT_ERROR"] = "Неверный формат лицензии.";
$MESS["TR_CA_DOCS_LICENSE_ACTIVATE_JWT_ERROR"] = "Неизвестная ошибка.";
$MESS["TR_CA_DOCS_LICENSE_CREATE_NEW_ACCOUNT_NUMBER_CURL_DISABLED"] = "Для получения номера счёта необходимо включить поддержку curl. Обратитесь к администратору. ";

$MESS["TR_CA_DOCS_LICENSE_HISTORY_EMPTY"] = "Ваша история пуста.";

$MESS["TR_CA_DOCS_LICENSE_ACTIVATE_JWT_SUCCESS"] = "На ваш счет было зачислено ";
$MESS["TR_CA_DOCS_LICENSE_ACTIVATE_JWT_SUCCESS2"] = " операций подписи.";

$MESS['TR_CA_DOCS_CURL_WARNING'] = '<span class="required">Внимание!</span><br>Для работы одноразовых лицензий требуется установить на сервер php-расширение curl!';

$MESS["TR_CA_DOCS_LICENSE_EDIT"] = "Изменить";

$MESS["TR_CA_DOCS_LICENSE_SUBMIT_DELETE_ACCOUNT_NUMBER"] = "Вы уверены, что хотите изменить текущий счет? В случае утери номера счета восстановление будет невозможно.";
$MESS["TR_CA_DOCS_LICENSE_SUBMIT_ACTIVATE_JWT_TOKEN"] = "Вы уверены, что хотите активировать токен?";

$MESS["TR_CA_DOCS_SIGN_STANDARD_HEADING"] = "Настройка стандарта подписи";
$MESS["TR_CA_DOCS_SIGN_STANDARD_DESCRIPTION"] = "CMS - классический формат подписи. содержимое ключей и сертификатов позволяет установить авторство электронного документа, однако в используемом сегодня формате ЭП не фиксируется время её создания и статус сертификата открытого ключа на момент подписи (действителен, отозван, приостановлен). Это затрудняет процедуру доказательства подлинности электронной подписи.<br />CAdES - усовершенствованный формат подписи. Предназначена для разрешения споров между подписывающей и проверяющей сторонами, которые могут возникать в отдаленном будущем, даже годы спустя момента их создания.";
$MESS["TR_CA_DOCS_SIGN_STANDARD"] = "Стандарт подписи";

$MESS["TR_CA_DOCS_SIGN_STANDARD_CMS"] = "CMS";
$MESS["TR_CA_DOCS_SIGN_STANDARD_CADES"] = "CAdES";

$MESS["TR_CA_DOCS_AUTO_UNBLOCK_SELECTOR_5_MIN"] = "5 минут";
$MESS["TR_CA_DOCS_AUTO_UNBLOCK_SELECTOR_10_MIN"] = "10 минут";
$MESS["TR_CA_DOCS_AUTO_UNBLOCK_SELECTOR_15_MIN"] = "15 минут";
$MESS["TR_CA_DOCS_AUTO_UNBLOCK_SELECTOR_30_MIN"] = "30 минут";
$MESS["TR_CA_DOCS_AUTO_UNBLOCK_SELECTOR_60_MIN"] = "60 минут";

$MESS["TR_CA_DOCS_AUTO_UNBLOCK_HEADING"] = "Автоматическая разблокировка документа";
$MESS["TR_CA_DOCS_AUTO_UNBLOCK_DESCRIPTION"] = "Благодаря этой настройке, документ будет автоматически разблокирован спустя данное время.<br /><br /><span class=\"required\">Внимание! Если не успеть уложится в данный промежуток, то процесс подписи будет прерван.</span>";
$MESS["TR_CA_DOCS_AUTO_UNBLOCK"] = "Разблокировать через";

$MESS["TR_CA_DOCS_LICENSE_HISTORY_TEXT"] = "Выписка счета за ";
$MESS["TR_CA_DOCS_LICENSE_HISTORY_SELECTOR_1_DAY"] = "1 день";
$MESS["TR_CA_DOCS_LICENSE_HISTORY_SELECTOR_3_DAYS"] = "3 дня";
$MESS["TR_CA_DOCS_LICENSE_HISTORY_SELECTOR_7_DAYS"] = "7 дней";
$MESS["TR_CA_DOCS_LICENSE_HISTORY_SELECTOR_14_DAYS"] = "14 дней";
$MESS["TR_CA_DOCS_LICENSE_HISTORY_SELECTOR_30_DAYS"] = "30 дней";
$MESS["TR_CA_DOCS_LICENSE_HISTORY_SELECTOR_INF_DAYS"] = "За все время";
$MESS["TR_CA_DOCS_LICENSE_HISTORY_BTN"] = "Получить";

$MESS["TR_CA_DOCS_TYPE_SIGN_COMBINED"] = "Совмещенная";
$MESS["TR_CA_DOCS_TYPE_SIGN_DETACHED"] = "Открепленная";

$MESS["TR_CA_DOCS_TYPE_SIGN_HEADING"] = "Настройка типа подписи";
$MESS["TR_CA_DOCS_TYPE_SIGN_DESCRIPTION"] = "Тип подписи влияет на работу всего модуля. Выбрав тип подписи в данном разделе, подпись будет соотвествующей на всем сайте.<br /><br /><span class=\"required\">Внимание! При подписывании файла одним типом подписи, в будущем его нельзя подписывать другим.</span>";
$MESS["TR_CA_DOCS_TYPE_SIGN"] = "Тип подписи";

$MESS["TR_CA_DOCS_EVENTS_HEADING"] = "Настройки автоматического изменения статуса заказа";
$MESS["TR_CA_DOCS_EVENTS_DESCRIPTION"] = "Заказ с прикрепленными документами можно автоматически переводить в другой статус при совершении некоторых действий.";
$MESS["TR_CA_DOCS_EVENTS_SIGNED_BY_CLIENT"] = "Документ подписан клиентом";
$MESS["TR_CA_DOCS_EVENTS_SIGNED_BY_SELLER"] = "Документ подписан продавцом";
$MESS["TR_CA_DOCS_EVENTS_SIGNED_BY_BOTH"] = "Документ подписан обеими сторонами";
$MESS["TR_CA_DOCS_EVENTS_SIGNED_WAIT_ALL_DOCS"] = "Ждать подписи всех прикрепленных документов";
$MESS["TR_CA_DOCS_EVENTS_EMAIL_SENT"] = "Письмо с документом отправлено";
$MESS["TR_CA_DOCS_EVENTS_EMAIL_READ"] = "Письмо с документом прочитано";
$MESS["TR_CA_DOCS_EVENTS_DO_NOTHING"] = "Не изменять";

$MESS["TR_CA_DOCS_EMAIL_HEADING"] = "Настройки рассылки писем с документами по заказам";
$MESS["TR_CA_DOCS_EMAIL_MAIL_EVENT_ID"] = "Почтовое событие";
$MESS["TR_CA_DOCS_EMAIL_TEMPLATE_ID"] = "Почтовый шаблон";
$MESS["TR_CA_DOCS_EMAIL_NOT_SELECTED"] = "Не выбрано";
$MESS["TR_CA_DOCS_EMAIL_DESCRIPTION"] = 'Для удобства отправки клиентам подписанных документов со страницы "Документы по заказам" модуль автоматически создает почтовое событие и привязанный к нему шаблон. Вы можете отредактировать шаблон по своему усмотрению.<br /><br />В шаблоне письма можно использовать следующие поля:<br />#EMAIL# - почтовый ящик пользователя, совершившего заказ,<br />#ORDER_USER# - имя пользователя, совершившего заказ,<br />#ORDER_ID# - номер заказа,<br />#FILE_NAMES# - список названий документов.';

$MESS["TR_CA_DOCS_REQUIRED_SIGN_EMAIL_HEADING"] = "Настройки рассылки писем с запросом на подпись документов";
$MESS["TR_CA_DOCS_REQUIRED_SIGN_EMAIL_MAIL_EVENT_ID"] = "Почтовое событие";
$MESS["TR_CA_DOCS_REQUIRED_SIGN_EMAIL_TEMPLATE_ID"] = "Почтовый шаблон";
$MESS["TR_CA_DOCS_REQUIRED_SIGN_EMAIL_NOT_SELECTED"] = "Не выбрано";
$MESS["TR_CA_DOCS_REQUIRED_SIGN_EMAIL_DESCRIPTION"] = 'Для удобства отправки клиентам подписанных документов со страницы «Документы требующие подпись» модуль автоматически создает почтовое событие и привязанный к нему шаблон. Вы можете отредактировать шаблон по своему усмотрению.<br /><br />В шаблоне письма можно использовать следующие поля:<br />#EMAIL# - EMail получателя сообщения,<br />#REQUESTING_USER# - пользователь, запросивший подпись,<br />#FILE_NAMES# - список названий документов.';

$MESS["TR_CA_DOCS_DEFAULT_EMAIL_HEADING"] = "Настройки рассылки писем с документами";
$MESS["TR_CA_DOCS_DEFAULT_EMAIL_MAIL_EVENT_ID"] = "Почтовое событие";
$MESS["TR_CA_DOCS_DEFAULT_EMAIL_TEMPLATE_ID"] = "Почтовый шаблон";
$MESS["TR_CA_DOCS_DEFAULT_EMAIL_NOT_SELECTED"] = "Не выбрано";
$MESS["TR_CA_DOCS_DEFAULT_EMAIL_DESCRIPTION"] = "Для удобства отправки клиентам подписанных документов модуль автоматически создает почтовое событие и привязанный к нему стандартный шаблон. Вы можете отредактировать шаблон по своему усмотрению.<br /><br />В шаблоне письма можно использовать следующие поля:<br />#EMAIL# - почтовый ящик пользователя, на который будет отправлено письмо,<br />#FILE_NAMES# - список названий документов.";

$MESS["TR_CA_DOCS_FORM_EMAIL_HEADING"] = "Настройки рассылки писем с документами по формам пользователю";
$MESS["TR_CA_DOCS_FORM_EMAIL_MAIL_EVENT_ID"] = "Почтовое событие";
$MESS["TR_CA_DOCS_FORM_EMAIL_TEMPLATE_ID"] = "Почтовый шаблон";
$MESS["TR_CA_DOCS_FORM_EMAIL_NOT_SELECTED"] = "Не выбрано";
$MESS["TR_CA_DOCS_FORM_EMAIL_DESCRIPTION"] = "Для удобства отправки клиентам подписанных документов модуль автоматически создает почтовое событие и привязанный к нему стандартный шаблон. Вы можете отредактировать шаблон по своему усмотрению.<br /><br />В шаблоне письма можно использовать следующие поля:<br />#EMAIL# - почтовый ящик пользователя, на который будет отправлено письмо,<br />#FILE_NAMES# - список названий документов.";

$MESS["TR_CA_DOCS_FORM_TO_ADMIN_EMAIL_HEADING"] = "Настройки рассылки уведомлений с документами по формам администратору";
$MESS["TR_CA_DOCS_FORM_TO_ADMIN_EMAIL_MAIL_EVENT_ID"] = "Почтовое событие";
$MESS["TR_CA_DOCS_FORM_TO_ADMIN_EMAIL_TEMPLATE_ID"] = "Почтовый шаблон";
$MESS["TR_CA_DOCS_FORM_TO_ADMIN_EMAIL_NOT_SELECTED"] = "Не выбрано";
$MESS["TR_CA_DOCS_FORM_TO_ADMIN_EMAIL_DESCRIPTION"] = "Для удобства отправки администатору оповещения о успешно заполненной формы пользователем модуль автоматически создает почтовое событие и привязанный к нему стандартный шаблон. Вы можете отредактировать шаблон по своему усмотрению.<br /><br />В шаблоне письма можно использовать следующие поля:<br />#EMAIL# - почтовый ящик пользователя, на который будет отправлено письмо,<br />#FILE_NAMES# - список названий документов,<br />#FORM_USER# - автор заполненной формы.";

$MESS["TR_CA_DOCS_FORM_RECAPTCHA_ATTENTION"] = "Для корректной работы reCAPTCHA на сайте в настройках сервиса необходимо применять reCAPTCHA v2, с типом: Флажок \"Я не робот\"";
$MESS["TR_CA_DOCS_FORM_RECAPTCHA_KEY_SITE"] = "reCAPTCHA ключ сайта";
$MESS["TR_CA_DOCS_FORM_RECAPTCHA_KEY_SITE_PLACEHOLDER"] = "Введите сюда ключ сайта";
$MESS["TR_CA_DOCS_FORM_RECAPTCHA_SECRET_KEY"] = "reCAPTCHA секретный ключ";
$MESS["TR_CA_DOCS_FORM_RECAPTCHA_SECRET_KEY_PLACEHOLDER"] = "Введите сюда секретный ключ";

$MESS["TR_CA_DOCS_LOGS_LAST_100"] = "Последние 100 операций:";
$MESS["TR_CA_DOCS_LOGS_NO_LOG_FILE"] = "Записи об операциях отсутствуют.";
$MESS["TR_CA_DOCS_LOGS_DOWNLOAD"] = "Скачать полный журнал операций";
$MESS["TR_CA_DOCS_LOGS_PURGE"] = "Удалить журнал";

$MESS["TR_CA_DOCS_OPT_SAVE"] = "Сохранить";

