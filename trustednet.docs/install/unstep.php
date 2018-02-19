<?if (!check_bitrix_sessid()) return;?>

<?= CAdminMessage::ShowNote(GetMessage("TN_DOCS_UNSTEP_DONE")); ?>

<form action="<? echo $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<? echo LANG ?>">
    <input type="submit" name="" value="<? echo GetMessage("MOD_BACK") ?>">
</form>

