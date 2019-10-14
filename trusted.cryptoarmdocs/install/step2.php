<?php
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

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

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_INSTALL_TITLE"));
?>


<? if ($module_id === "trusted.cryptoarmdocscrp" || "trusted.cryptoarmdocsbusiness") {
    (CAdminMessage::ShowNote(Loc::getMessage("TR_CA_DOCS_DROP_OR_KEEP")));
}  elseif ($module_id === "trusted.cryptoarmdocsstart") {
    (CAdminMessage::ShowNote(Loc::getMessage("TR_CA_DOCS_DROP_OR_KEEP_DB_ONLY")));
}?>

<div style="display: flex;">
    <form action="<?= $APPLICATION->GetCurPage() ?>" style="padding-right: 4px;">
    <?=bitrix_sessid_post()?>
        <input type="hidden" name="lang" value="<?= LANG ?>">
        <input type="hidden" name="id" value="trusted.cryptoarmdocscrp">
        <input type="hidden" name="install" value="Y">
        <input type="hidden" name="step" value="3">
        <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_KEEP") ?>">
    </form>

    <? if ($module_id === "trusted.cryptoarmdocscrp" || "trusted.cryptoarmdocsbusiness") : ?>
        <form action="<?= $APPLICATION->GetCurPage() ?>">
        <?=bitrix_sessid_post()?>
            <input type="hidden" name="lang" value="<?= LANG ?>">
            <input type="hidden" name="id" value="trusted.cryptoarmdocscrp">
            <input type="hidden" name="install" value="Y">
            <input type="hidden" name="step" value="4">
            <input type="hidden" name="dropDBandIB" value="Y">
            <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_DROP") ?>">
            <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CANCEL") ?>">
        </form>
    <? elseif ($module_id === "trusted.cryptoarmdocsstart") : ?>
        <form action="<?= $APPLICATION->GetCurPage() ?>">
        <?=bitrix_sessid_post()?>
            <input type="hidden" name="lang" value="<?= LANG ?>">
            <input type="hidden" name="id" value="trusted.cryptoarmdocstart">
            <input type="hidden" name="install" value="Y">
            <input type="hidden" name="step" value="4">
            <input type="hidden" name="dropDB" value="Y">
            <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_DROP") ?>">
            <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CANCEL") ?>">
        </form>
    <? endif;?>
</div>

