<?php
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_INSTALL_TITLE"));

// Finds records in DB for which files were deleted
function getLostDocs()
{
    global $DOCUMENT_ROOT, $DB;
    $io = CBXVirtualIo::GetInstance();
    $sql = "SELECT * FROM `tr_ca_docs` WHERE CHILD_ID is null";
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
    <!--fBs--><input type="hidden" name="id" value="trusted.cryptoarmdocsbusiness"><!--fMs tags for core name changing script-->
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
        CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_MISSING_FILES"));
        echo "<p>";
        echo Loc::getMessage("TR_CA_DOCS_MISSING_FILES_LIST") . "<br>";
        echo implode("<br>", array_map("urldecode", $lostDocsNames));
        echo "</p>";
        echo '<input type="hidden" name="dropLostDocs" value="' . htmlentities(serialize($lostDocsIds)) . '">';
    } else {
        CAdminMessage::ShowNote(Loc::getMessage("TR_CA_DOCS_ALL_FILES_FOUND"));
    }
    ?>
    <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CONTINUE") ?>">
    <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CANCEL") ?>">
</form>

