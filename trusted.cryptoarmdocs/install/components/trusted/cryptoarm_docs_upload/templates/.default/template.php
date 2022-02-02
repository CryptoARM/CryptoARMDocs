<?php
defined('B_PROLOG_INCLUDED') || die;

if (!$USER->IsAuthorized()) {
    return;
}

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$module_id = 'trusted.cryptoarmdocsfree';
Loader::includeModule($module_id);

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
    $newDocFilename = preg_replace('/[\s]+/u', '_', $newDocFilename);
    $newDocFilename = preg_replace('/[^a-zA-Z' . Loc::getMessage("TR_CA_DOCS_CYR") . '0-9_\.-]/u', '', $newDocFilename);
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

