<?php
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_UNINSTALL_TITLE'));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
<?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="trusted.cryptoarmdocs">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?php echo CAdminMessage::ShowMessage(Loc::getMessage('MOD_UNINST_WARN')); ?>
    <p><?php echo nl2br(Loc::getMessage('TR_CA_DOCS_UNINST_SAVE_PROMPT')); ?></p>
    <p>
        <input type="checkbox" name="deletedata" id="deletedata" value="Y">
        <label for="deletedata"><?php echo Loc::getMessage(
            'TR_CA_DOCS_UNINST_DELETE_DATA'
        ); ?></label>
    </p>
    <input type="submit" name="uninst" value="<?php echo Loc::getMessage('MOD_UNINST_DEL'); ?>">
</form>

