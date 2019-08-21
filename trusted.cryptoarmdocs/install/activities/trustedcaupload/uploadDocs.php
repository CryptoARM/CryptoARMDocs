<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

global $USER;

Loader::includeModule('trusted.cryptoarmdocs');

if (!Docs\Utils::checkAuthorization()) {
    return;
}

$DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

foreach ($_FILES as $key => $value) {
    $fileName = $_FILES[$key]["name"];
    if ($fileName) {
        $uniqid = (string)uniqid();
        $newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
        mkdir($newDocDir);

        $newDocFilename = Docs\Utils::mb_basename($fileName);
        $absolutePath = $newDocDir . $newDocFilename;
        $relativePath = '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/' . $newDocFilename;

        if (move_uploaded_file($_FILES[$key]["tmp_name"], $absolutePath)) {
            $props = new Docs\PropertyCollection();
            $props->add(new Docs\Property("USER", (string)$USER->GetID()));

            $doc = Docs\Utils::createDocument($relativePath, $props);
            $fileId = $doc->getId();
        }
    }
}

echo $fileId;

unset($_FILES);
