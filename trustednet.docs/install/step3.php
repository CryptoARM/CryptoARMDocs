<?php
if (!check_bitrix_sessid()) {
    return;
}

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("TN_DOCS_INSTALL_TITLE"));

// Finds records in DB for which files were deleted
function getLostDocs()
{
    global $DOCUMENT_ROOT, $DB;
    $io = CBXVirtualIo::GetInstance();
    $sql = "SELECT * FROM `trn_docs` WHERE CHILD_ID is null";
    $rows = $DB->Query($sql);
    $docs = array();
    while ($array = $rows->Fetch()) {
        $docs[] = $array;
    }
    $lostDocs = array();
    foreach ($docs as $doc) {
        $path = $DOCUMENT_ROOT . urldecode($doc["PATH"]);
        if (!$io->FileExists($path)) {
            $lostDocs[] = $doc;
        }
    }
    return $lostDocs;
}
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
<?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trustednet.docs">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="4">
    <?php
    $lostDocs = getLostDocs();
    if ($lostDocs) {
        $lostDocsNames = array();
        foreach ($lostDocs as $doc) {
            $lostDocsNames[] = $doc["PATH"];
            $lostDocsIds[] = $doc["ID"];
        }
        CAdminMessage::ShowMessage(GetMessage("TN_DOCS_MISSING_FILES"));
        echo "<p>";
        echo GetMessage("TN_DOCS_MISSING_FILES_LIST") . "<br>";
        echo implode("<br>", array_map("urldecode", $lostDocsNames));
        echo "</p>";
        echo '<input type="hidden" name="dropLostDocs" value="' . htmlentities(serialize($lostDocsIds)) . '">';
    } else {
        CAdminMessage::ShowNote(GetMessage("TN_DOCS_ALL_FILES_FOUND"));
    }
    ?>
    <input type="submit" name="choice" value="<?= GetMessage("TN_DOCS_CONTINUE") ?>">
    <input type="submit" name="choice" value="<?= GetMessage("TN_DOCS_CANCEL") ?>">
</form>

