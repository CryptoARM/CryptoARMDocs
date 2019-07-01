<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

global $USER;

Loader::includeModule('trusted.cryptoarmdocs');

function DocUpload($inputIndexFileId, $fileId) {
    echo '<script type="text/javascript">
    window.parent.onUploadDocument ({inputIndexFileId: "' . $inputIndexFileId . '", fileId: "' . $fileId . '"});
    </script>';
}

$DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

foreach ($_POST as $key => $value) {
    if (stristr($key, "input_file_id_")) {
        $inputIndexFileId = str_ireplace("input_file_id_", "", $key);
        $inputIndexFullFileId = "input_file_" . $inputIndexFileId;
        $fileName = $_FILES[$inputIndexFullFileId]["name"];
        if ($fileName) {
            $uniqid = (string)uniqid();
            $newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
            mkdir($newDocDir);

            $newDocFilename = Docs\Utils::mb_basename($fileName);
            $absolutePath = $newDocDir . $newDocFilename;
            $relativePath = '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/' . $newDocFilename;

            if (move_uploaded_file($_FILES[$inputIndexFullFileId]["tmp_name"], $absolutePath)) {
                $props = new Docs\PropertyCollection();
                $props->add(new Docs\Property("USER", (string)$USER->GetID()));

                $doc = Docs\Utils::createDocument($relativePath, $props);
                $fileId = $doc->getId();
                DocUpload($inputIndexFileId, $fileId);
                $_POST["input_file_id_" . $inputIndexFileId] = $fileId;
            }
        }
    }
}

$iblockTypeid = $_POST["iBlock_type_id"];
unset($_POST["iBlock_type_id"]);

Docs\Form::addIBlockForm($iblockTypeid, $_POST);

unset($_FILES[$inputIndexFullFileId]['name']);