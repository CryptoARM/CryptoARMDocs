<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;


# TODO: Use single cancel page for all terminations
# TODO: Add curl check during installation

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

// $APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_INSTALL_TITLE"));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="<?= TR_CA_DOCS_MODULE_ID ?>">
    <input type="hidden" name="install" value="N">
    <?php
    if ($modulesOutOfDate = Option::get(TR_CA_DOCS_MODULE_ID, TR_CA_DOCS_MODULES_OUT_OF_DATE)) {
        CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_SOME_MODULES_ARE_OUT_OF_DATE_AND_CANT_BE_USED") . $modulesOutOfDate . Loc::getMessage("TR_CA_DOCS_SOME_MODULES_ARE_OUT_OF_DATE_AND_CANT_BE_USED2"));
    }
    if ($modulesNotInstalled = Option::get(TR_CA_DOCS_MODULE_ID, TR_CA_DOCS_MODULES_WERE_NOT_INSTALLED)) {
        CAdminMessage::ShowMessage(Loc::getMessage("TR_CA_DOCS_SOME_MODULES_WERE_NOT_INSTALLED") . $modulesNotInstalled . ".");
    }
    ?>
    <input type="submit" name="choice" value="<?= Loc::getMessage("MOD_BACK") ?>">
</form>
