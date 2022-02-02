<?php
use Bitrix\Main\Localization\Loc;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = array(
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
);
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $coreId . "/";
    if(file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_INSTALL_TITLE"));

function checkDB()
{
    global $DB;
    $tables = array("tr_ca_docs", "tr_ca_docs_property");
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
    <input type="hidden" name="id" value="<?= $module_id ?>">
    <input type="hidden" name="install" value="Y">
    <?php
    $tables = array("tr_ca_docs", "tr_ca_docs_property");
    $tablesInDB = checkDB();
    $tablesNotInDB = array_diff($tables, $tablesInDB);

    $emptyDB = $tablesInDB ? false : true;

    $fullDB = $tablesNotInDB ? false : true;

    if ($emptyDB) {
        echo CAdminMessage::ShowNote(Loc::getMessage("TR_CA_DOCS_NO_DB_TABLES"));
        echo '<input type="hidden" name="step" value="4">';
    } elseif ($fullDB) {
        echo CAdminMessage::ShowNote(Loc::getMessage("TR_CA_DOCS_ALL_DB_TABLES"));
        echo '<input type="hidden" name="step" value="2">';
    } else {
        echo CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_DAMAGED_DB") . implode(", ",$tablesNotInDB));
        echo '<input type="hidden" name="step" value="4">';
        echo '<input type="hidden" name="dropDB" value="Y">';
    }
    ?>
    <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CONTINUE_INSTALL") ?>">
    <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CANCEL_INSTALL") ?>">
</form>

