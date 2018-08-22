<?php
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
Loader::includeModule("trusted.cryptoarmdocs");

if (Docs\Utils::isSecure()) {
    $MESS["TR_CA_DOCS_AJAX_CONTROLLER"] = "https://" . $_SERVER["HTTP_HOST"] . "/bitrix/components/trusted/docs/ajax.php";
} else {
    $MESS["TR_CA_DOCS_AJAX_CONTROLLER"] = "http://" . $_SERVER["HTTP_HOST"]. "/bitrix/components/trusted/docs/ajax.php";
}

$MESS["TR_CA_DOCS_ERROR_FILE_NOT_FOUND"] = "Не найдены файлы соответствующие следующим документам:";
$MESS["TR_CA_DOCS_ERROR_DOC_NOT_FOUND"] = "Нет найдены документы со следующими идентификаторами: ";
$MESS["TR_CA_DOCS_ERROR_DOC_BLOCKED"] = "Некоторые документы заблокированы с связи с отправкой на подпись:";
$MESS["TR_CA_DOCS_ERROR_DOC_ROLE_SIGNED"] = "Некоторые документы уже подписаны:";

$MESS["TR_CA_DOCS_ALERT_NO_CLIENT"] = "Для подписи документов установите и запустите КриптоАРМ ГОСТ. Приобрести КриптоАРМ ГОСТ можно в нашем интернет-магазине https://cryptoarm.ru/shop/cryptoarm-gost";
$MESS["TR_CA_DOCS_ALERT_HTTP_WARNING"] = "Подпись документа невозможна на незащищенном соединении (\"HTTP\" протокол).";
$MESS["TR_CA_DOCS_ALERT_DOC_NOT_FOUND"] = "Документы со следующими индентификаторами не были обнаружены в базе данных";
$MESS["TR_CA_DOCS_ALERT_DOC_BLOCKED"] = "Некоторые документы заблокированы и не могут быть отправлены на подпись";
$MESS["TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM"] = "Вы действительно хотите удалить документ? Эту операцию невозможно отменить.";
$MESS["TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE"] = "Некоторые файлы не были найдены в хранилище:";
$MESS["TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_POST"] = "Удалить эти записи о файлах из базы данных?";
$MESS["TR_CA_DOCS_ALERT_LOST_DOC"] = "Некоторые файлы не были обнаружены в хранилище:";

