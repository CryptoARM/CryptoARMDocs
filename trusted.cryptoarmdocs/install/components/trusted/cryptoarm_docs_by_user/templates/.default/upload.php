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
            <div onclick="cancelSend()">
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
                    <?echo Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL") ?>
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
// Drag-and-drop functions
let dropArea = document.getElementById('trca_drop_area');

let filesToUpload = [];

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropArea.classList.add('highlight');
}

function unhighlight(e) {
    dropArea.classList.remove('highlight');
}

dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    let dt = e.dataTransfer;
    files = dt.files;
    handleFiles(files, 'trca_upload_file_list_2');
}
let docsIds = new Array;

function uploadFiles() {
    $("#trca_upload_window_first_n_second_step").hide();
    $("#trca_upload_success").show();
}

function addTemporaryListItem(file, i, xhr, files, idDiv) {
    $('#trca_upload_file_list_2').show();
    $('.trca_upload_window_send_form_require_sign').show();
    $('.trca_drop_area').hide();
    var docarea = document.getElementById(idDiv);
    var docDiv = document.createElement('div');
    docDiv.id = "trca_doc_temporary_" + i;
    docDiv.className = "trca_doc_list_item";
    docarea.appendChild(docDiv);
    docDiv.insertAdjacentHTML('beforeend', getProgressCircle(i));
    var docName = document.createElement('div');
    docName.className = "trca_doc_list_item_name onload";
    docName.title = file.name;
    docName.innerHTML = file.name;
    docDiv.appendChild(docName);
    var docSize = document.createElement('div');
    docSize.className = "trca_doc_list_item_size";
    docSize.innerHTML = getFileSize(file.size);
    docDiv.appendChild(docSize);
    var docRemove = document.createElement('div');
    docRemove.className = "trca_doc_list_remove material-icons"
    docRemove.innerHTML = "close";
    docRemove.style.color = '#C4C4C4';
    docRemove.onclick = function() {
        xhr.abort();
        $(docDiv).remove();
        var listItems = $(".trca_doc_list_item");
        if (listItems.length == 0) {
            toFirstStep();
        }
    }
    docDiv.appendChild(docRemove);
}

function cancelUpload() {
    $(".trca_doc_list_remove").each(function(){
        $(this).click();
    })
    hideModal();
}

function addAndUpload(file, docarea, i, files, idDiv) {
    name = 'USER';
    var xhr;
    function getXHR(request) {
        xhr = request;
    }
    value = "<?= Docs\Utils::currUserId() ?>";
    $("#trca_upload_window_header_upload_more").show();
    $("#trca_upload_window_first_step").hide();
    $("#trca_upload_window_second_step").show();
    $("#trca_upload_second_step_footer").show();
    let currDocId;

    function getUploadedDocId(item) {
        docsIds.push(item);
        addFileInList(file, docarea, item, i);
    }

    function fileOnLoad(loaded, total, i) {
        var progress = loaded/total * 180;
        var progressEl = document.querySelector('#progress_'+i); 
        progressEl.style.transform = 'rotate('+progress+'deg)';
    }

    var props  =new Map([
        [name, value],
    ])
    trustedCA.uploadFile(file, props, (item)=>{getUploadedDocId(item)}, null, true, (loaded, total)=>{fileOnLoad(loaded, total, i)}, (request)=>{getXHR(request)});
    addTemporaryListItem(file, i, xhr, files, idDiv);
}

function getProgressCircle(i) {
    return `<div class="circle-out" id=circle_`+i+`> 
                <div class="progress" id="progress_`+i+`"></div> 
                <div class="circle-in"> </div> 
            </div> `;
}

function handleFiles(files, idDiv) {
    console.log(idDiv);
    maxsize = "<?= Docs\Utils::maxUploadFileSize() ?>"
    var docarea = document.getElementById(idDiv);
    for (let i = 0; i < files.length; i++) {
        file = files[i];
        trustedCA.checkFileSize(file, maxsize, () => {
            trustedCA.checkName(file, () => {
                trustedCA.checkAccessFile(file, addAndUpload(file, docarea, i, files, idDiv))
            })
        });
    };
}

function addFileInList(file, docarea, currDocId, i) {
    filesToUpload.push(file);
    var docDiv = document.createElement('div');
    docDiv.id = "trca_doc_" + currDocId;
    docDiv.className = "trca_doc_list_item";
    $("#trca_doc_temporary_" + i).replaceWith(docDiv);
    $("#trca_doc_temporary_" + i).remove();
    var docName = document.createElement('div');
    docName.className = "trca_doc_list_item_name " + (file.name.substr(file.name.lastIndexOf(".") + 1));
    docName.title = file.name;
    docName.innerHTML = file.name;
    docDiv.appendChild(docName);
    var docSize = document.createElement('div');
    docSize.className = "trca_doc_list_item_size";
    docSize.innerHTML = getFileSize(file.size);
    docDiv.appendChild(docSize);
    var docRemove = document.createElement('div');
    docRemove.className = "trca_doc_list_remove material-icons"
    docRemove.innerHTML = "close";
    docRemove.style.color = '#C4C4C4';
    docRemove.onclick = function() {
        removeFromList(docDiv.id, file);
        var ids = new Array;
        ids.push(currDocId);
        var i = docsIds.indexOf(currDocId);
        docsIds.splice(i, 1);
        trustedCA.ajax("remove", {ids});
    }
    docDiv.appendChild(docRemove);
}

function removeFromList(divid, file) {
    var ind = filesToUpload.indexOf(file);
    filesToUpload.splice(ind, 1);
    $('#' + divid).remove();
    if (filesToUpload.length == 0) {
        toFirstStep()
    };
}

function getFileSize(size) {
    if (size < 1024) {
        return size + ' <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_B")?>';
    } else {
        let sizeString = Math.floor(size / 1024);
        if (sizeString < 1024) {
            return sizeString + ' <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_KB")?>';
        } else {
            sizeString = Math.floor(sizeString / 1024);
            if (sizeString < 1024) {
                return sizeString + ' <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_MB")?>';
            } else {
                sizeString = Math.floor(sizeString / 1024)
                return sizeString + ' <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_GB")?>';
            }
        }
    }
}

function showSendForm() {
    let uploadWindow = document.getElementById("trca_upload_window");
    var docarea = document.getElementById('trca_upload_file_list');
    docarea.style = 'height: 96px; width: 434px; border-radius: 2px;';
    let firstStepLabel = document.getElementById('trca_upload_first_step');
    firstStepLabel.style = 'color:#67B7F7; background:white; border: 1.5px solid #67B7F7;';
    $("#trca_upload_second_step_footer").hide();
    let sendFormHeader = `
    <div class="trca_upload_window_header" id="trca_upload_window_header_2" style="height: 65px; justify-content:flex-start; ">
        <div class="trca_upload_window_header_step">
            <div class="trca_upload_window_header_step_number">
                <span class="trca_upload_window_header_step_number_text">2</span>
            </div>
            <span class="trca_upload_window_header_step_name">
                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_STEP_TWO") ?>
            </span>
        </div>
    </div>`;
    uploadWindow.insertAdjacentHTML('beforeend', sendFormHeader);
    let sendForm = document.createElement('div');
    sendForm.className = 'trca_upload_window_send_form';
    sendForm.id = 'trca_upload_window_send_form'
    uploadWindow.appendChild(sendForm);
    let sendFormContent = `
    <div class="trca_upload_window_send_form_field">
        <label for="trca_upload_send_rec"><?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_RECEPIENT") ?></label>
        <input id="trca_upload_send_rec" placeholder="<?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_RECEPIENT_1") ?>">
    </div>
    <div class="trca_upload_window_send_form_field">
        <label for="trca_upload_send_theme"><?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_THEME") ?></label>
        <input id=trca_upload_send_theme>
    </div>
    <div class="trca_upload_window_send_form_comment_field">
        <textarea id="trca_comment" placeholder="<?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_COMMENT") ?>" style="resize: none" ></textarea>
    </div>
    <div class="trca_upload_window_send_form_require_sign">
        <input type="checkbox" id="trca_upload_window_send_form_require_sign" class="trca_require_checkbox">
        <label for="trca_upload_window_send_form_require_sign"><?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SIGN_BEFORE") ?></label>
    </div>`;
    sendForm.insertAdjacentHTML('beforeend', sendFormContent);
    let sendFormFooter = `
    <div class="trca_upload_window_footer" id="trca_upload_third_step_footer" style="justify-content: space-between">
        <div class="trca_upload_window_footer_docs_actions" style="width: 37%">
            <div class="trca_upload_window_footer_cancel" onclick="showSaveDraftPopup()">
                <span class="trca_upload_window_footer_cancel_text">
                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL") ?>
                </span>
            </div>
            <div class="trca_upload_window_footer_save_in_docs" onclick="uploadFiles()">
                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SAVE") ?>
            </div>
        </div>
            <div class="trca_upload_window_footer_send_button" onclick=send(true) id = "trca_send_button">
                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND") ?>
            </div>
    </div>`;
    uploadWindow.insertAdjacentHTML('beforeend', sendFormFooter);
}

let messId;

function send(send = false, ids = null, parentId = null) {
    $("#trca_send_answer").hide();
    var recepientEmail = $("#trca_upload_send_rec").val();
    var theme = $("#trca_upload_send_theme").val();
    var comment = $("#trca_comment").val();
    if (ids != null) {
        docsIds = ids;
    }
    function writeMesId(d) {
        messId = d.messId;
    };
    trustedCA.ajax("newMessage", {recepientEmail, theme, comment, docsIds, send, parentId}, (d)=>{writeMesId(d)});
    if (send == true) {
        $("#trca_upload_window_first_n_second_step").hide();
        $('#trca_upload_succesful_send').show();
    }
}

function cancelSend() {
    trustedCA.ajax("sendCancel", {messId});
    hideModal();
}

function showSaveDraftPopup() {
    $("#trca_upload_window_first_n_second_step").hide();
    $("#trca_upload_save_draft").show();

}

function hideModal() {
    toFirstStep();
    filesToUpload = [];
    $('.trca_doc_list_item').hide();
    $("#trca_upload_window_steps").hide();
    $("#trca_upload_save_draft").hide();
    $("#trca_upload_succesful_send").hide();
    $("#trca_upload_window_download").hide();
}

function toFirstStep() {
    $("*#trca_upload_success").hide();
    $("*#trca_upload_window_first_n_second_step").show();
    let firstStepLabel = document.getElementById('trca_upload_first_step');
    firstStepLabel.style = '';
    $("*#trca_upload_window_header_2").remove();
    $("*#trca_upload_window_send_form").remove();
    let uploadWindow = document.getElementById("trca_upload_window");
    $("*#trca_upload_window_header_upload_more").hide();
    $("*#trca_upload_window_first_step").show();
    $("*#trca_upload_window_second_step").hide();
    $("*#trca_upload_third_step_footer").remove();
}

function initAnswer() {
    let re = $("#trca_edo_info_message_detail_theme").text();
    let email = $("#trca_email_outgoing").text();
    let parentId = $('#trca_edo_info_message').attr("info_id");
    $('#trca_send_answer_theme').text("Re: " + re)
    $('#trca_upload_send_rec').val(email);
    $('#trca_send_button').attr("onclick", "send(true, null," + parentId +")");
    $("#trca_upload_window_first_n_second_step").hide();
    $("#trca_upload_window_steps").show();
    $("#trca_upload_component").show();
    $("#trca_send_answer").show();
}
</script>