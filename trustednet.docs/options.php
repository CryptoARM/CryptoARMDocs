<?php

include __DIR__ . "/config.php";
$module_id = TN_DOCS_MODULE_ID;
CModule::IncludeModule($module_id);

IncludeModuleLangFile(__FILE__);

$aTabs = array(
    array(
        "DIV" => "tn_docs_options",
        "TAB" => GetMessage("TN_DOCS_OPT_TAB"),
        "TITLE" => GetMessage("TN_DOCS_OPT_TAB_TITLE")
    )
);

$tabControl = new CAdminTabControl("trustedTabControl", $aTabs, true, true);

$DOCUMENTS_DIR = COption::GetOptionString($module_id, 'DOCUMENTS_DIR', "docs");

function TrimDocumentsDir($dir) {
    $dir = trim($dir);
    $dir = trim($dir, "/.");
    // Get rid of /../
    $dir = preg_replace("/\/.*\//", "/", $dir);
    return $dir;
}

function CheckDocumentsDir($dir) {
    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    $fullPath = $docRoot . "/" . $dir;
    // Expand extra /../
    $fullPath = realpath($fullPath);

    if ($dir == '') {
        return GetMessage("TN_DOCS_OPT_EMPTY_DIR_FIELD");
    }

    // Check for existing directory
    if (!is_dir($fullPath)) {
        return GetMessage("TN_DOCS_OPT_NO_DIR");
    }

    // Check if we are in bitrix root
    $len = strlen($docRoot);
    if (strncmp($fullPath, $docRoot, $len) < 0 || strcmp($fullPath, $docRoot) == 0) {
        return GetMessage("TN_DOCS_OPT_CANNOT_USE_SYSTEM_DIRECTORY");
    }

    // Check for entering bitrix system directory
    if (preg_match("/^bitrix($|\/*)/", $dir)) {
        return GetMessage("TN_DOCS_OPT_CANNOT_USE_SYSTEM_DIRECTORY");
    }

    // Check for permissions
    if (is_readable($fullPath) && is_writable($fullPath)) {
        return true;
    } else {
        return GetMessage("TN_DOCS_OPT_NO_ACCESS_TO_DIRECTORY");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
    if (isset($_POST['Update'])) {

        if (isset($_POST['DOCUMENTS_DIR'])) {
            $documentsDirFromPost = (string)$_POST['DOCUMENTS_DIR'];
        }
        $documentsDirFromPost = TrimDocumentsDir($documentsDirFromPost);
        $checkRes = CheckDocumentsDir($documentsDirFromPost);
        if ($checkRes === true) {
            $DOCUMENTS_DIR = $documentsDirFromPost;
            COption::SetOptionString($module_id, 'DOCUMENTS_DIR', $DOCUMENTS_DIR);
        } else
            CAdminMessage::ShowMessage($checkRes);
    }

$tabControl->Begin();
?>

<form method="POST" enctype="multipart/form-data"
      action="<?= $APPLICATION->GetCurPage() ?>?lang=<?= LANGUAGE_ID ?>&mid=<?= $module_id ?>"
      name="trustednetdocs_settings">

    <?= bitrix_sessid_post(); ?>

    <?= $tabControl->BeginNextTab(); ?>

    <tr>
        <td width="20%" class="adm-detail-content-cell-l">
            <?= GetMessage("TN_DOCS_OPT_DOCS_DIR") ?>
        </td>
        <td width="80%">
            <input name="DOCUMENTS_DIR"
                   class="adm-detail-content-cell-r"
                   size="40"
                   value="<?= $DOCUMENTS_DIR ?>"/>
        </td>
    </tr>

    <? $tabControl->Buttons(); ?>

    <?php $tabControl->End(); ?>

    <input type="submit" name="Update" value="<?= GetMessage("TN_DOCS_OPT_SAVE") ?>"/>

</form>

