<?php
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("TN_DOCS_UNINSTALL_TITLE"));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
<?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trustednet.docs">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?echo CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"))?>
    <p><?echo nl2br(Loc::getMessage("TN_DOCS_UNINST_SAVE_PROMPT")) ?></p>
    <p>
        <input type="checkbox" name="savedata" id="savedata" value="Y" checked>
        <label for="savedata"><?echo Loc::getMessage("MOD_UNINST_SAVE_TABLES")?></label>
    </p>
    <input type="submit" name="uninst" value="<?echo Loc::getMessage("MOD_UNINST_DEL")?>">
</form>

