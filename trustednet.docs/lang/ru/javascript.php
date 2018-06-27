<?php
use TrustedNet\Docs;
use Bitrix\Main\Loader;
Loader::includeModule("trustednet.docs");

if (Docs\Utils::isSecure()) {
    $MESS["TN_DOCS_AJAX_CONTROLLER"] = "https://" . $_SERVER["HTTP_HOST"] . "/bitrix/components/trustednet/trustednet.docs/ajax.php";
} else {
    $MESS["TN_DOCS_AJAX_CONTROLLER"] = "http://" . $_SERVER["HTTP_HOST"]. "/bitrix/components/trustednet/trustednet.docs/ajax.php";
}


$MESS["TN_DOCS_ERROR_FILE_NOT_FOUND"] = "Не найдены файлы соответствующие следующим документам:";
$MESS["TN_DOCS_ERROR_DOC_NOT_FOUND"] = "Нет найдены документы со следующими идентификаторами: ";
$MESS["TN_DOCS_ERROR_DOC_BLOCKED"] = "Некоторые документы заблокированы с связи с отправкой на подпись:";
$MESS["TN_DOCS_ERROR_DOC_ROLE_SIGNED"] = "Некоторые документы уже подписаны:";

$MESS["TN_DOCS_ALERT_NO_CLIENT"] = "Для подписи документов установите и запустите КриптоАРМ ГОСТ. Приобрести КриптоАРМ ГОСТ можно в нашем интернет-магазине https://cryptoarm.ru/shop/cryptoarm-gost";
$MESS["TN_DOCS_ALERT_HTTP_WARNING"] = "Подпись документа невозможна на незащищенном соединении (\"HTTP\" протокол).";
$MESS["TN_DOCS_ALERT_DOC_NOT_FOUND"] = "Документы со следующими индентификаторами не были обнаружены в базе данных";
$MESS["TN_DOCS_ALERT_DOC_BLOCKED"] = "Некоторые документы заблокированы и не могут быть отправлены на подпись";
$MESS["TN_DOCS_ALERT_REMOVE_ACTION_CONFIRM"] = "Вы действительно хотите удалить документ? Эту операцию невозможно отменить.";
$MESS["TN_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE"] = "Некоторые файлы не были найдены в хранилище:";
$MESS["TN_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_POST"] = "Удалить эти записи о файлах из базы данных?";
$MESS["TN_DOCS_ALERT_LOST_DOC"] = "Некоторые файлы не были обнаружены в хранилище:";

