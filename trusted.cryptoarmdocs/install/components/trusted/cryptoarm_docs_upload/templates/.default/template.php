<?php
defined('B_PROLOG_INCLUDED') || die;

if (!$USER->IsAuthorized()) {
    return;
}

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Trusted\CryptoARM\Docs;

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loader::includeModule('trusted.cryptoarmdocs');

$DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

$redirect = false;

foreach ($arParams['FILES'] as $fileHandle) {

    if (empty($_FILES[$fileHandle]['name'])) {
        continue;
    }
    $redirect = true;

    $uniqid = (string)uniqid();
    $newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
    mkdir($newDocDir);

    $newDocFilename = Docs\Utils::mb_basename($_FILES[$fileHandle]['name']);
    $absolutePath = $newDocDir . $newDocFilename;
    $relativePath = $DOCUMENTS_DIR . $uniqid . '/' . $newDocFilename;

    if (move_uploaded_file($_FILES[$fileHandle]['tmp_name'], $absolutePath)) {
        $props = new Docs\PropertyCollection();

        foreach ($arParams['PROPS'] as $name => $value) {
            $props->add(new Docs\Property((string)$name, (string)$value));
        }

        $doc = Docs\Utils::createDocument($relativePath, $props);

    }

    unset($_FILES[$fileHandle]['name']);
}

if ($redirect) {
    LocalRedirect($request->getRequestUri());
    die();
}

