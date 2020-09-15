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
<div id="trca_upload_succesful_send" class="trca_upload_success">
    <div>
        <span>
            <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_MES_1") ?></span>
        <span style="color:#67B7F7">
            <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND_MES_2") ?></span>
    </div>
    <div>
        <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL_SENDING") ?>
    </div>
    <div class="material-icons" style="cursor: pointer; color: rgba(0, 0, 0, 0.158);" onclick="hideModal()">
        close
    </div>
</div>
<div id="trca_upload_component">
    <div class="trca_upload_button" onclick="showModal()">
        <div style="font-size: 35px; font-weight: 100">+</div>
        <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_BUTTON") ?>
    </div>
    <div id="trca_upload_window_steps" class="trca_upload_modal_window" style="display: none">
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
                    <div class="trca_upload_window_footer_send_button" onclick="send(false)">
                        <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SAVE") ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="trca_upload_success" style="display: none" id="trca_upload_success">
            <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_POPUP_SUCCESS") ?>
            <div class="material-icons" style="cursor: pointer; color: rgba(0, 0, 0, 0.158);" onclick="hideModal()">
                close
            </div>
        </div>
        <div id="trca_upload_window_first_n_second_step">
            <div class="trca_upload_window" id="trca_upload_window">
                <div class="trca_upload_window_header_close" onclick="hideModal()">
                    <div class="material-icons">
                        close
                    </div>
                </div>
                <div class="trca_upload_window_header" id="trca_upload_window_header">
                    <div class="trca_upload_window_header_step">
                        <div class="trca_upload_window_header_step_number" id="trca_upload_first_step">
                            <span class="trca_upload_window_header_step_number_text">1</span>
                        </div>
                        <span class="trca_upload_window_header_step_name">
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_STEP_ONE") ?>
                        </span>
                    </div>
                    <input type="file" onchange="handleFiles(this.files)" id="fileElem" multiple>
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
                                        onchange="handleFiles(this.files)">
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
                        <div class="trca_upload_window_footer_cancel" style="margin-left:25px" onclick="hideModal()">
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
    </div>
</div>
<?
    }
}
?>
<script>
function showModal() {
    $("#trca_upload_window_steps").show();
}
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
    handleFiles(files);
}
let docsIds = new Array;

function uploadFiles() {
    // filesToUpload.forEach((file) => {
    //     name = 'USER';
    //     value = "<?= Docs\Utils::currUserId() ?>"
    //     var props = new Map([
    //         [name, value],
    //     ]);
    //     trustedCA.uploadFile(file, props, (item)=>{docIds.push(item)}, null, false);
    // })

    $("#trca_upload_window_first_n_second_step").hide();
    $("#trca_upload_success").show();
}

function addAndUpload(file, docarea, i) {
    name = 'USER';
    value = "<?= Docs\Utils::currUserId() ?>";
    let currDocId;
    function getUploadedDocId(item) {
        docsIds.push(item);
        addFileInList(file, docarea, item);
        $("#trca_upload_window_header_upload_more").show();
        $("#trca_upload_window_first_step").hide();
        $("#trca_upload_window_second_step").show();
        $("#trca_upload_second_step_footer").show();
    }
    var props  =new Map([
        [name, value],
    ])
    trustedCA.uploadFile(file, props, (item)=>{getUploadedDocId(item)}, null, true);
}

function handleFiles(files) {
    console.log(files);
    maxsize = "<?= Docs\Utils::maxUploadFileSize() ?>"
    var docarea = document.getElementById('trca_upload_file_list');
    for (let i = 0; i < files.length; i++) {
        file = files[i];
        trustedCA.checkFileSize(file, maxsize, () => {
            trustedCA.checkName(file, () => {
                trustedCA.checkAccessFile(file, addAndUpload(file, docarea, i))
            })
        });
    };
    // timeout = setTimeout(()=> {
    // if (filesToUpload.length != 0) {
    //     $("#trca_upload_window_header_upload_more").show();
    //     $("#trca_upload_window_first_step").hide();
    //     $("#trca_upload_window_second_step").show();
    //     $("#trca_upload_second_step_footer").show();
    // }}, 5000);
}

function addFileInList(file, docarea, currDocId) {
    filesToUpload.push(file);
    var docDiv = document.createElement('div');
    docDiv.id = "trca_doc_" + currDocId;
    docDiv.className = "trca_doc_list_item";
    docarea.appendChild(docDiv);
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
    $('#' + divid).hide();
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

// function uploadFiles() {
//     filesToUpload.forEach((file) => {
//         name = 'USER';
//         value = "<?= Docs\Utils::currUserId() ?>"
//         var props = new Map([
//             [name, value],
//         ]);
//         trustedCA.uploadFile(file, props, (item)=>{docIds.push(item)}, null);
//     })
//     console.log(idsToShare);

//     $("#trca_upload_window_first_n_second_step").hide();
//     $("#trca_upload_success").show();
// }

function showSendForm() {
    // $("#trca_upload_window_first_n_second_step").hide();
    // $("#trca_upload_window_third_step").show();
    let uploadWindow = document.getElementById("trca_upload_window");
    // uploadWindow.style.height = '491px';
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
            <div class="trca_upload_window_footer_send_button" onclick=send(true)>
                <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_SEND") ?>
            </div>
    </div>`;
    uploadWindow.insertAdjacentHTML('beforeend', sendFormFooter);
}

function send(send = false) {
    var recepientEmail = document.getElementById("trca_upload_send_rec").value;
    var theme = document.getElementById("trca_upload_send_theme").value;
    var comment = document.getElementById("trca_comment").value;
    console.log(docsIds);
    trustedCA.ajax("newMessage", {recepientEmail, theme, comment, docsIds, send})
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
    trustedCA.reloadDoc(); //!ПОТОМ УДАЛИТЬ
}

function toFirstStep() {
    $("*#trca_upload_success").hide();
    $("*#trca_upload_window_first_n_second_step").show();
    let firstStepLabel = document.getElementById('trca_upload_first_step');
    firstStepLabel.style = '';
    $("*#trca_upload_window_header_2").hide();
    $("*#trca_upload_window_send_form").hide();
    let uploadWindow = document.getElementById("trca_upload_window");
    $("*#trca_upload_window_header_upload_more").hide();
    $("*#trca_upload_window_first_step").show();
    $("*#trca_upload_window_second_step").hide();
    $("*#trca_upload_third_step_footer").hide();
}
</script>