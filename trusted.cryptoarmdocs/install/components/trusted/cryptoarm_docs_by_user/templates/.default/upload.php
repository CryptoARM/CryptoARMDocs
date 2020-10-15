<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = [
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
];
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
    if (file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}
?>
<?
if ($arParams["ALLOW_ADDING"] === 'Y') {
    if ($USER->IsAuthorized()) {
        $maxSize = Docs\Utils::maxUploadFileSize();
        ?>
<!-- <div id="trca_upload_succesful_send" class="trca_upload_success">
    <div>s
        <span>
            <?//= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_MES_1") ?></span>
        <span style="color:#67B7F7">
            <?//= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_MES_2") ?></span>
    </div>
    <div onclick="cancel()">
        <?//= Loc::getMessage("trca_edo_info_message_answer") ?>
    </div>
    <div class="material-icons" style="cursor: pointer; color: rgba(0, 0, 0, 0.158);" onclick="hideModal()">
        close
    </div>
</div> -->
<div id="trca_data" userid="<?= Docs\Utils::currUserId() ?>" maxsize="<?= $maxSize ?>"></div>
<div id="trca_upload_component" style="display:none">
    <!-- <div class="trca_upload_button" onclick="showModal()">
        <div style="font-size: 35px; font-weight: 100">+</div>
        <?//= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_BUTTON") ?>
    </div> -->
    <div id="trca_upload_window_steps" class="trca_upload_modal_window" >
        <div class="trca_upload_save_draft" id="trca_upload_save_draft" style="display:none">
            <div class="trca_upload_save_draft_header">
                <div class="trca_upload_window_header_step_name">
                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SAVE_DRAFT") ?>
                </div>
                <div onclick="hideModal()" class="trca_upload_window_header_close" style="width:20px; height:20px;">
                    <div class="material-icons"
                        style="font-size: 20px; color: rgba(0, 0, 0, 0.158);">
                        close
                    </div>
                </div>
            </div>
            <div class="trca_upload_save_draft_text">
                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SAVE_DRAFT_TEXT") ?>
            </div>
            <div class="trca_upload_save_draft_footer">
                <div style="width:276px; display: flex; justify-content: space-around; align-items: center" onclick="hideModal()">
                    <div style="color: #868687">
                        <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_DONT_SAVE") ?>
                    </div>
                    <div class="trca_upload_window_footer_send_button" id="trca_send_save_draft_button" onclick="send(false)">
                        <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SAVE") ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="trca_upload_succesful_send" class="trca_upload_success" style="display: none">
            <div>
                <span>
                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_MES_1") ?></span>
                <span style="color:#67B7F7">
                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_MES_2") ?></span>
            </div>
            <div id="trca_upload_cancel_send">
                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL_SENDING") ?>
            </div>
            <div class="material-icons" style="cursor: pointer; color: rgba(0, 0, 0, 0.158);" onclick="hideModal()">
                    close
            </div>
        </div>
        <div class="trca_upload_success" style="display: none" id="trca_upload_success">
            <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_POPUP_SUCCESS") ?>
            <div class="material-icons" style="cursor: pointer; color: rgba(0, 0, 0, 0.158);" onclick="hideModal()">
                close
            </div>
        </div>
        <div id="trca_upload_window_first_n_second_step" style="display: none">
            <div class="trca_upload_window" id="trca_upload_window">
                <div class="trca_upload_window_header_close" onclick="hideModal()">
                    <div class="material-icons">
                        close
                    </div>
                </div>
                <div class="trca_upload_window_header" id="trca_upload_window_header">
                    <div class="trca_upload_window_header_step" id="trca_first_step">
                        <div class="trca_upload_window_header_step_number" id="trca_upload_first_step">
                            <span class="trca_upload_window_header_step_number_text">1</span>
                        </div>
                        <span class="trca_upload_window_header_step_name" id="trca_upload_first_step_name">
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_STEP_ONE") ?>
                        </span>
                    </div>
                    <input type="file" onchange="handleFiles(this.files, 'trca_upload_file_list')" id="fileElem" multiple>
                    <label for="fileElem">
                        <div class="trca_upload_window_header_upload_more" id="trca_upload_window_header_upload_more"
                            style="display: none">
                            <span>
                                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_MORE") ?></span>
                        </div>
                    </label>
                </div>
                <div id="trca_upload_window_first_step">
                    <div id="trca_drop">
                        <div id="trca_drop_area">
                            <form class="trca_upload_form" id="trca_upload_form">
                                <div class="trca_upload_form_icon">
                                    <div class="material-icons">
                                        description
                                    </div>
                                    <div class="material-icons">
                                        arrow_downward
                                    </div>
                                </div>
                                <div class="trca_upload_form_text">
                                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_1") ?>
                                    <input type="file" class="trca_upload_file_input" id="fileElem" multiple
                                        onchange="handleFiles(this.files , 'trca_upload_file_list')">
                                    <label for="fileElem" class="trca_upload_file_label">
                                        <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_2") ?></label>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="trca_upload_window_footer">
                        <div class="trca_upload_window_footer_cancel" onclick="hideModal()">
                            <span class="trca_upload_window_footer_cancel_text">
                                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL") ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="trca_upload_window_second_step" style="display: none">
                    <div class="trca_upload_file_list" id="trca_upload_file_list">
                    </div>
                    <div class="trca_upload_window_footer" style="justify-content:space-between"
                        id="trca_upload_second_step_footer">
                        <div class="trca_upload_window_footer_cancel" style="margin-left:25px" onclick="cancelUpload()">
                            <span class="trca_upload_window_footer_cancel_text">
                                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL") ?>
                            </span>
                        </div>
                        <div class="trca_upload_window_footer_docs_actions">
                            <div class="trca_upload_window_footer_save_in_docs">
                                <span onclick="uploadFiles()" style="cursor: pointer">
                                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SAVE_IN_DOCS")?>
                                </span>
                            </div>
                            <div class="trca_upload_window_footer_send_button" onclick="showSendForm()">
                                <?=  Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND")?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="trca_upload_window_download"  class="trca_upload_window_download" style="display: none;">
            <div class="trca_edo_info_close" onclick="hideModal()"></div>
            <div class="trca_upload_window_download_title">
                <span class="trca_upload_window_header_step_name">
                    Выгрузка документов
                </span>
            </div>
            <div class="trca_upload_window_download_radio">
                <div>
                    <input type="radio" name="download_files" value="docs">Выгрузить документы
                </div>
                <div>
                    <input type="radio" name="download_files" value="all">Выгрузить весь документооборот
                </div>
            </div>
            <div class="trca_upload_window_download_footer">
                <div class="trca_upload_window_download_cancel" onclick="hideModal()">
                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL") ?>
                </div>
                <div class="trca_upload_window_footer_send_button" onclick="downloadFiles('<?= $zipName ?>')">
                    Выгрузить
                </div>
            </div>
        </div>
        <div id="trca_send_answer" class="trca_send_answer" style="display: none;">
            <div class="trca_edo_info_close" onclick="hideModal()"></div>
            <div id="trca_send_answer_theme" class="trca_send_answer_theme"></div>
            <div class="trca_send_answer_header_row">
                <div>Кому:</div>
                <input id="trca_upload_send_rec" style="border:none" readonly>
            </div>
            <div class="trca_send_answer_header_row">
                <div>Тема:</div>
                <input id="trca_upload_send_theme">
            </div>
            <div class="trca_send_answer_comment">
                <textarea id="trca_comment" type="text" placeholder="Комментарий для получателя "></textarea>
            </div>
            <div class="trca_upload_file_list" id="trca_upload_file_list_2" style="display: none; width: 100%;">
            </div>
            <div class="trca_upload_window_send_form_require_sign" style="display: none;">
                <input type="checkbox" id="trca_upload_window_send_form_require_sign" class="trca_require_checkbox">
                <label for="trca_upload_window_send_form_require_sign"><?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SIGN_BEFORE") ?></label>
            </div>
            <div class="trca_upload_window_footer" style="height: auto;">
                    <input id="fileElem_2" type="file" class="trca_upload_file_input" multiple
                        onchange='handleFiles(this.files, "trca_upload_file_list_2")' style="display: none;">
                    <label for="fileElem_2" style="margin-bottom: 0;">
                        <div class="trca_upload_window_footer_add_file">
                            Добавить документы
                        </div>
                    </label>
                <div class="trca_upload_window_footer_send_button" id = "trca_send_button">
                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND") ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?
    }
}
?>
<script>
    initForUpload()
</script>