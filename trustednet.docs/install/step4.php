<?php

if (!check_bitrix_sessid()) {
    return;
}

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("TN_DOCS_INSTALL_TITLE"));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trustednet.docs">
    <input type="hidden" name="install" value="N">
    <?= CAdminMessage::ShowMessage(GetMessage("TN_DOCS_CANCELLED")) ?>
    <input type="submit" name="choice" value="<?= GetMessage("MOD_BACK") ?>">
</form>

