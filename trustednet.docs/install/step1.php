<?php

if (!check_bitrix_sessid()) {
    return;
}

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("TN_DOCS_INSTALL_TITLE"));

function checkDB()
{
    global $DB;
    $tables = array("trn_docs", "trn_docs_property", "trn_docs_status");
    $res = array();
    foreach($tables as $table) {
        $sql = "SHOW TABLES LIKE '" . $table . "'";
        $queryRes = $DB->Query($sql);
        if ($queryRes->Fetch()) {
            $res[] = $table;
        }
    }
    return $res;
}

?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
<?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trustednet.docs">
    <input type="hidden" name="install" value="Y">
    <?php
    $tables = array("trn_docs", "trn_docs_property", "trn_docs_status");
    $tablesInDB = checkDB();
    $tablesNotInDB = array_diff($tables, $tablesInDB);

    $emptyDB = $tablesInDB ? false : true;

    $fullDB = $tablesNotInDB ? false : true;

    if ($emptyDB) {
        echo CAdminMessage::ShowNote(GetMessage("TN_DOCS_NO_DB_TABLES"));
        echo '<input type="hidden" name="step" value="4">';
    } elseif ($fullDB) {
        echo CAdminMessage::ShowNote(GetMessage("TN_DOCS_ALL_DB_TABLES"));
        echo '<input type="hidden" name="step" value="2">';
    } else {
        echo CAdminMessage::ShowMessage(GetMessage("TN_DOCS_DAMAGED_DB") . implode(", ",$tablesNotInDB));
        echo '<input type="hidden" name="step" value="4">';
        echo '<input type="hidden" name="dropDB" value="Y">';
    }
    ?>
    <input type="submit" name="choice" value="<?= GetMessage("TN_DOCS_CONTINUE_INSTALL") ?>">
    <input type="submit" name="choice" value="<?= GetMessage("TN_DOCS_CANCEL_INSTALL") ?>">
</form>

