<?php
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$module_id = "trusted.cryptoarmdocsfree";

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_INSTALL_TITLE"));
?>


<? if ($module_id === "trusted.cryptoarmdocsfree") : ?>
    <?= (CAdminMessage::ShowNote(Loc::getMessage("TR_CA_DOCS_DROP_OR_KEEP_DB_ONLY"))); ?>
<? endif; ?>

<div style="display: flex;">
    <form action="<?= $APPLICATION->GetCurPage() ?>" style="padding-right: 4px;">
    <?=bitrix_sessid_post()?>
        <input type="hidden" name="lang" value="<?= LANG ?>">
        <input type="hidden" name="id" value="<?= $module_id ?>">
        <input type="hidden" name="install" value="Y">
        <input type="hidden" name="step" value="3">
        <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_KEEP") ?>">
    </form>

    <? if ($module_id === "trusted.cryptoarmdocsfree") : ?>
        <form action="<?= $APPLICATION->GetCurPage() ?>">
        <?=bitrix_sessid_post()?>
            <input type="hidden" name="lang" value="<?= LANG ?>">
            <input type="hidden" name="id" value="trusted.cryptoarmdocsfree">
            <input type="hidden" name="install" value="Y">
            <input type="hidden" name="step" value="4">
            <input type="hidden" name="dropDB" value="Y">
            <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_DROP") ?>">
            <input type="submit" name="choice" value="<?= Loc::getMessage("TR_CA_DOCS_CANCEL") ?>">
        </form>
    <? endif;?>
</div>

