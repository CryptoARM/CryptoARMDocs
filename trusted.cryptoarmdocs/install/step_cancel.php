<?php
use Bitrix\Main\Localization\Loc;

# TODO: Use single cancel page for all terminations
# TODO: Add curl check during installation

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_INSTALL_TITLE'));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trusted.cryptoarmdocs">
    <input type="hidden" name="install" value="N">
    <?= CAdminMessage::ShowMessage(Loc::getMessage('TR_CA_DOCS_CANCELLED')) ?>
    <input type="submit" name="choice" value="<?= Loc::getMessage('MOD_BACK') ?>">
</form>

