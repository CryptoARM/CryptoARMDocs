<?php
use Trusted\CryptoARM\Docs;

$MESS["TR_CA_DOCS_AJAX_CONTROLLER"] = "/bitrix/components/trusted/docs/ajax.php";

$MESS["TR_CA_DOCS_ERROR_NO_AUTH"] = "Для выполнения этой операции необходима авторизация";
$MESS["TR_CA_DOCS_ERROR_NO_IDS"] = "В запросе не заданы идентификаторы документов";
$MESS["TR_CA_DOCS_ERROR_FILE_NOT_FOUND"] = "Не найдены файлы, соответствующие следующим документам:";
$MESS["TR_CA_DOCS_ERROR_DOC_NOT_FOUND"] = "Не найдены документы со следующими идентификаторами: ";
$MESS["TR_CA_DOCS_ERROR_DOC_BLOCKED"] = "Некоторые документы заблокированы с связи с отправкой на подпись:";
$MESS["TR_CA_DOCS_ERROR_DOC_ROLE_SIGNED"] = "Некоторые документы уже подписаны:";
$MESS["TR_CA_DOCS_ERROR_DOC_UNSIGNED"] = "Следущие документы не имеют подписи:";
$MESS["TR_CA_DOCS_ERROR_DOC_NO_ACCESS"] = "Нет доступа к некоторым документам: ";
$MESS["TR_CA_DOCS_ERROR_DOC_WRONG_SIGN_TYPE"] = "Неверый тип подписи для: ";

$MESS["TR_CA_DOCS_ALERT_NO_CLIENT"] = "Для подписи документов установите и запустите КриптоАРМ ГОСТ. Скачать КриптоАРМ ГОСТ можно в нашем интернет-магазине: https://cryptoarm.ru/cryptoarm-gost";
$MESS["TR_CA_DOCS_ALERT_HTTP_WARNING"] = "Подпись документа невозможна на незащищенном соединении (\"HTTP\" протокол).";
$MESS["TR_CA_DOCS_ALERT_DOC_NOT_FOUND"] = "Документы со следующими индентификаторами не были обнаружены в базе данных";
$MESS["TR_CA_DOCS_ALERT_DOC_BLOCKED"] = "Некоторые документы заблокированы и не могут быть отправлены на подпись";
$MESS["TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM"] = "Вы действительно хотите удалить документ? Эту операцию невозможно отменить.";
$MESS["TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM_MANY"] = "Вы действительно хотите удалить документы? Эту операцию невозможно отменить.";
$MESS["TR_CA_DOCS_ALERT_REMOVE_FORM_ACTION_CONFIRM"] = "Вы действительно хотите удалить форму и прикрепленные документы? Эту операцию невозможно отменить.";
$MESS["TR_CA_DOCS_ALERT_REMOVE_FORM_ACTION_CONFIRM_MANY"] = "Вы действительно хотите удалить формы и прикрепленные документы? Эту операцию невозможно отменить.";
$MESS["TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE"] = "Некоторые файлы не были найдены в хранилище:";
$MESS["TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_POST"] = "Удалить эти записи о файлах из базы данных?";
$MESS["TR_CA_DOCS_ALERT_LOST_DOC"] = "Некоторые файлы не были обнаружены в хранилище:";

$MESS["TR_CA_DOCS_ACT_SEND_MAIL_TO_PROMPT"] = "Укажите e-mail, на который вы хотите отправить документы:";
$MESS["TR_CA_DOCS_ACT_SEND_MAIL_SUCCESS"] = "Письмо отправлено";
$MESS["TR_CA_DOCS_ACT_SEND_MAIL_FAILURE"] = "Ошибка: письмо не отправлено";

$MESS["TR_CA_DOCS_ACT_SHARE"] = "Укажите e-mail пользователя 1С-Битрикс:";
$MESS["TR_CA_DOCS_ACT_SHARE_SUCCESS_1"] = "Пользователь c e-mail ";
$MESS["TR_CA_DOCS_ACT_SHARE_SUCCESS_2"] = " получил доступ к документу";
$MESS["TR_CA_DOCS_ACT_REQUIRE_SUCCESS_1"] = "Пользователю c e-mail ";
$MESS["TR_CA_DOCS_ACT_REQUIRE_SUCCESS_2"] = " отправлен запрос на подпись";
$MESS["TR_CA_DOCS_ACT_SHARE_NO_USER_1"] = "Пользователь с e-mail ";
$MESS["TR_CA_DOCS_ACT_SHARE_NO_USER_2"] = " не найден";

$MESS["TR_CA_DOCS_ACT_DOWNLOAD_FILE_1"] = "Размер загружаемых файлов не должен превышать ";
$MESS["TR_CA_DOCS_ACT_DOWNLOAD_FILE_2"] = " Mb.";
$MESS["TR_CA_DOCS_ACT_DOWNLOAD_FILE_ZERO_SIZE"] = "Загружаемый файл не должен быть пустым.";

$MESS["TR_CA_DOCS_UNSHARE_CONFIRM"] = " Документ будет удалён из вашего списка, но останется у пользователя, который вам его предоставил. Продолжить?";
$MESS["TR_CA_DOCS_UNSHARE_FROM_MODAL_CONFIRM"] = " Документ будет удалён из списка пользователя. Продолжить?";
$MESS["TR_CA_DOCS_ACT_ERROR_NAME"] = "В названии файла используются недопустимые символы";

$MESS["TR_CA_DOCS_SIGN_TYPE"] = TR_CA_DOCS_TYPE_SIGN;

$MESS["TR_CA_DOCS_MODAL_MESSAGE_1"] = "Документ передан в КриптоАрм";
$MESS["TR_CA_DOCS_MODAL_MESSAGE_2"] = "Для завершения процесса подпишите документ, либо нажмите ";
$MESS["TR_CA_DOCS_MODAL_MESSAGE_MANY_1"] = "Документы переданы в КриптоАрм.";
$MESS["TR_CA_DOCS_MODAL_MESSAGE_MANY_2"] = "Для завершения процесса подпишите документы, либо нажмите ";
$MESS["TR_CA_DOCS_MODAL_CANCEL"] = "Отменить подпись";

$MESS["TR_CA_DOCS_COMP_FORM_INPUT_FILE"] = "Добавить файл";

$MESS["TR_CA_DOCS_NO_ACCESS_FILE"] = "Не удалось загрузить файл ";

$MESS['BPAA_ACT_ADD_FILE'] = "Прикрепить файл";

$MESS["TR_CA_DOCS_CLOSE_INFO_WINDOW"] = "Закрыть";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_SHARE"] = "Поделиться";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_DOWNLOAD"] = "Скачать";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_SIGN"] = "Подписать";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_VERIFY"] = "Проверить";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_PROTOCOL"] = "Протокол";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_UNSHARE"] = "Закрыть доступ к документу";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_NOT_SHARED"] = "Документ доступен только вам";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_STATUS_READ"] = "Только чтение";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_STATUS_MUST_TO_SIGN"] = "Отправлен запрос на подпись";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_STATUS_SIGNED"] = "Подписан";
$MESS["TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_STATUS_UNSIGNED"] = " Не подписан";



