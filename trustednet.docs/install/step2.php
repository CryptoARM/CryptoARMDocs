<?php

if (!check_bitrix_sessid()) {
    return;
}

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("TN_DOCS_INSTALL_TITLE"));
?>

<?= CAdminMessage::ShowNote(GetMessage("TN_DOCS_DROP_OR_KEEP")) ?>

<div style="display: flex;">
    <form action="<?= $APPLICATION->GetCurPage() ?>" style="padding-right: 4px;">
    <?=bitrix_sessid_post()?>
        <input type="hidden" name="lang" value="<?= LANG ?>">
        <input type="hidden" name="id" value="trustednet.docs">
        <input type="hidden" name="install" value="Y">
        <input type="hidden" name="step" value="3">
        <input type="submit" name="choice" value="<?= GetMessage("TN_DOCS_KEEP") ?>">
    </form>
    <form action="<?= $APPLICATION->GetCurPage() ?>">
    <?=bitrix_sessid_post()?>
        <input type="hidden" name="lang" value="<?= LANG ?>">
        <input type="hidden" name="id" value="trustednet.docs">
        <input type="hidden" name="install" value="Y">
        <input type="hidden" name="step" value="4">
        <input type="hidden" name="dropDB" value="Y">
        <input type="submit" name="choice" value="<?= GetMessage("TN_DOCS_DROP") ?>">
        <input type="submit" name="choice" value="<?= GetMessage("TN_DOCS_CANCEL") ?>">
    </form>
</div>

