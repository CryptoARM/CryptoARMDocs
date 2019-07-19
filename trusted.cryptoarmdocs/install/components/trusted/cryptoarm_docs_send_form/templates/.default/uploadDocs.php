<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

global $USER;

Loader::includeModule('trusted.cryptoarmdocs');

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
                $_POST["input_file_id_" . $inputIndexFileId] = $fileId;
                $fileListToUpdate[] = $fileId;
            }
        }
    }
}

$iBlockTypeId = $_POST["iBlock_type_id"];
$iBlockId = Docs\Form::addIBlockForm($iBlockTypeId, $_POST);

if ($iBlockId["success"]) {
    $pdf = Docs\Form::createPDF($iBlockTypeId, $iBlockId);
    if (!empty($fileListToUpdate)) {
        foreach ($fileListToUpdate as $fileId) {
            $doc = Docs\Database::getDocumentById($fileId);
            $props = $doc->getProperties();
            $props->add(new Docs\Property("FORM", $iBlockId["data"]));
            $doc->save();
        }
    }
    $fileListToUpdate[] = $pdf["data"];
    $extra = [
        "send_email_to_user" => $_POST["send_email_to_user"],
        "send_email_to_admin" => $_POST["send_email_to_admin"],
        "formId" => $iBlockId["data"]
    ];

    echo '<script>window.parent.trustedCA.sign(' . json_encode($fileListToUpdate) . ', ' . json_encode($extra) . ')</script>';
}

unset($_FILES);

/*$docsCollection = Docs\Database::getDocumentsByPropertyTypeAndValue("FORM", 449);

$docsCollectionSign = array();
$signValue = false;

foreach ($docsCollection->getList() as $doc) {
    $signValue = $doc->getSigners() ?: false;
    $docsCollectionSign[] = $doc->getId();
    if (!$signValue) {
        break;
    }
}*/

//$response = Docs\Form::sendEmail($fileListToUpdate, $_POST["send_email_to_user"], $_POST["send_email_to_admin"]);
//Docs\Utils::dump($response);