<?php
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("TN_DOCS_INSTALL_TITLE"));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trustednet.docs">
    <input type="hidden" name="install" value="N">
    <?= CAdminMessage::ShowMessage(Loc::getMessage("TN_DOCS_NO_D7")) ?>
    <input type="submit" name="choice" value="<?= Loc::getMessage("MOD_BACK") ?>">
</form>

