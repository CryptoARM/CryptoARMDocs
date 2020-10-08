<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Trusted\CryptoARM\Docs\Messages;
use Trusted\CryptoARM\Docs\Utils;

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

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$allIds = $arResult['ALL_IDS'];
$allIdsJs = $arResult['ALL_IDS_JS'];
$docs = $arResult['DOCS'];
$asd = true;

if ($USER->GetFullName()) {
    $compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetFullName();
} else {
    $compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetEmail();
}

include(__DIR__ . "/upload.php");
;зфк
?>

<!-- <a id="trca-reload-doc" href="<?//= $_SERVER["REQUEST_URI"] ?>"></a> -->
<div id="trca_label_edit_window" class="trca_create_label_window" style="height: auto">
    <div class="trca_create_label_window_header">
        <span><?= Loc::getMessage("TR_CA_DOCS_COMP_EDIT_LABEL") ?></span>
        <div class="material-icons" style="font-size: 20px; color: rgba(0, 0, 0, 0.158);" >
            close
        </div>
    </div>
    <div class="trca_edit_label_create" onclick="showCreateLabelWindow()">
        <span class="material-icons" style="transform: rotate(45deg); font-size: 20px; margin-right: 8px;">close</span>
        <span><?= Loc::getMessage("TR_CA_DOCS_COMP_EDIT_LABEL_CREATE") ?></span>
    </div>
    <div class="trca_edit_label_list" id="trca_edit_label_list">

    </div>
    <div class="trca_edit_label_footer">
        <div class="trca_upload_window_footer_cancel">
            <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL") ?>
        </div>
    </div>
</div>
<div id="trca_create_label_modal" class="trca_upload_modal_window" style="display:none">
    <div id="trca_create_label_window" class="trca_create_label_window">
        <div class="trca_create_label_window_header">
            <span><?= Loc::getMessage("TR_CA_DOCS_COMP_CREATE_LABEL") ?></span>
            <div class="material-icons" style="font-size: 20px; color: rgba(0, 0, 0, 0.158);" onclick="hideCreateLabelWindow()">
                close
            </div>
        </div>
        <div class="trca_create_label_window_name">
            <input type="text" required id="trca_label_text">
            <span class="bar"></span>
            <label for="trca_label_text"><?= Loc::getMessage("TR_CA_DOCS_COMP_LABEL_NAME") ?></label>
        </div>
        <div class="trca_create_label_window_colors">
            <div class="trca_create_label_window_color">
                <input id="first_color" type="radio" name="radio" value="trca_label_one">
                <label class="trca_label_one" for="first_color"></label>
            </div>
            <div class="trca_create_label_window_color">
                <input id="second_color" type="radio" name="radio" value="trca_label_two">
                <label class="trca_label_two" for="second_color"></label>
            </div>
            <div class="trca_create_label_window_color">
                <input id="third_color" type="radio" name="radio" value="trca_label_three">
                <label class="trca_label_three" for="third_color"></label>
            </div>
            <div class="trca_create_label_window_color">
                <input id="fourth_color" type="radio" name="radio" value="trca_label_four">
                <label class="trca_label_four"" for="fourth_color"></label>
            </div>
            <div class="trca_create_label_window_color">
                <input id="fifth_color" type="radio" name="radio" value="trca_label_five">
                <label class="trca_label_five" for="fifth_color"></label>
            </div>
            <div class="trca_create_label_window_color">
                <input id="sixth_color" type="radio" name="radio" value="trca_label_six">
                <label class="trca_label_six" for="sixth_color"></label>
            </div>
            <div class="trca_create_label_window_color ">
                <input id="seventh_color" type="radio" name="radio" value="trca_label_seven">
                <label class="trca_label_seven" for="seventh_color"></label>
            </div>
            <div class="trca_create_label_window_color ">
                <input id="eighth_color" type="radio" name="radio" value="trca_label_eight">
                <label class="trca_label_eight" for="eighth_color"></label>
            </div>
            <div class="trca_create_label_window_color ">
                <input id="ninth_color" type="radio" name="radio" value="trca_label_nine">
                <label class='trca_label_nine' for="ninth_color"></label>
            </div>
        </div>
        <div class="trca_create_label_window_footer">
            <div style="width:50%; display:flex; flex-direction:row; justify-content: space-around; align-items=center">
                <div id="trca_create_label_window_footer_cancel_button" style="align-items: center; display: flex" onclick="hideCreateLabelWindow()">
                    <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_CANCEL") ?>
                </div>
                <div class="trca_upload_window_footer_send_button" onclick="createLabel()">
                    <?= Loc::getMessage("TR_CA_DOCS_COMP_CREATE") ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="trca_edo" class="trca_edo">
    <div class="trca_edo_body">
        <div class="trca_edo_menu ">
            <div class="trca_edo_upload_button"  onclick="showModal()">
                <span>Загрузить документы </span>
            </div>
            <div class="trca_edo_message trca_menu">
                <div class="trca_edo_menu_item">
                    <div class="trca_edo_check"></div>
                    <span>Сообщения</span>
                </div>
                <div class="trca_edo_inbox submenu" onclick="getMessageList('outgoing', 0)">
                    <span>Исходящие</span>
                </div>
                <div class="trca_edo_outbox submenu" onclick="getMessageList('incoming', 0)">
                    <span>Входящие</span>
                </div>
                <div class="trca_edo_draft submenu" onclick="getMessageList('drafts', 0)">
                    <span>Черновики</span>
                </div>
            </div>
            <div class="trca_edo_documents trca_menu">
                <div class="trca_edo_menu_item">
                    <div class="trca_edo_check"></div>
                    <span>Документы</span>
                </div>
                <div class="trca_edo_download submenu active_menu" onclick="getDocList(0, 0)">
                    <span>Загруженые</span>
                </div>
                <div class="trca_edo_available submenu" onclick="getDocList(1, 0)">
                    <span>Доступные</span>
                </div>
            </div>
            <div class="trca_edo_label trca_menu">
                <div class="trca_edo_menu_item">
                    <div class="trca_edo_check"></div>
                    <span>Метки</span>
                </div>
                <div class="trca_edo_labels" id="trca_edo_labels">
                    <div class="trca_label label_orange">Важно</div>
                    <div class="trca_label label_violet">Партнер</div>
                    <div class="trca_label label_blue">Тест</div>
                    <div class="trca_label label_green">Тест</div>
                </div>
            </div>
        </div>
        <div class="trca_edo_content">
            <div class="trca_edo_header_menu">
                <div id="trca_edo_header_menu_buttons" class="trca_edo_header_menu_buttons">
                    <div class="trca_header_check">
                        <input type="checkbox">
                    </div>
                    <div id="trca_label_window" class="trca_label_window" style="display: none">
                        <div class="trca_label_window_search" style="height:42px">
                            <div class="trca_create_label_window_name">
                                <input type="text"  id="trca_label_search" style="width:160px">
                                <span class="bar" style="width:160px"></span>
                                <label for="trca_label_text"><?= Loc::getMessage("TR_CA_DOCS_COMP_FIND_LABEL") ?></label>
                            </div>
                        </div>
                        <div class="trca_label_window_list" id="trca_label_window_list">

                        </div>
                        <div id="trca_label_window_footer" class="trca_label_window_footer">
                            <div id="trca_label_assign" class="trca_label_window_footer_button">
                                <span><?= Loc::getMessage("TR_CA_DOCS_COMP_LABEL_ASSIGN") ?></span>
                            </div>
                            <div id="trca_label_new" class="trca_label_window_footer_button" onclick="showCreateLabelWindow()">
                                <span><?= Loc::getMessage("TR_CA_DOCS_COMP_NEW_LABEL") ?></span>
                            </div>
                            <div id="trca_label_edit" class="trca_label_window_footer_button">
                                <span><?= Loc::getMessage("TR_CA_DOCS_COMP_EDIT") ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="trca_header_button trca_button_send" title="<?= Loc::getMessage("TR_CA_DOCS_COMP_SEND_FILE") ?>"></div>
                    <div class="trca_header_button trca_button_download" title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOWNLOAD_FILE") ?>" onclick="uploadFile()"></div>
                    <div class="trca_header_button trca_button_remove" title="<?= Loc::getMessage("TR_CA_DOCS_COMP_REMOVE_FILE") ?>" onclick="remove()"></div>
                </div>
                <div class="trca_edo_header_menu_search">
                    <div class="trca_button_search"></div>
                    <input placeholder="Поиск" id="trca_search">
                    <!-- <div class="trca_button_close"></div> -->
                </div>
            </div>
            <div class="trca_edo_items">
                <div id="trca_edo_items_table" class="trca_edo_items_table">
                    <div class="trca_edo_item" file_id="<?= $doc["ID"] ?>">
                        <div class="trca_edo_check_item">
                            <input type="checkbox">
                        </div>
                        <div class="trca_edo_item_properties">
                            <div class="trca_edo_item_first_col">
                                <div class="trca_edo_item_first_col_row first c_black f_s_16"></div>
                                <div class="trca_edo_item_first_col_row second c_black"></div>
                                <div class="trca_edo_item_first_col_row third c_gray"></div>
                                <div class="trca_edo_item_first_col_row fourth c_gray"></div>
                                <div></div>
                            </div>
                            <!-- <div class="<?//= $style ?>"></div>
                            <div class="trca_edo_item_status">
                                <?//= $status ?>
                            </div> -->
                            <div class="trca_edo_item_time">
                                <?= $docCreated ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="trca_edo_info" style="display: none;">
                    <div class="trca_edo_info_close"></div>
                    <div class="trca_edo_info_title">
                        <span><?=  Loc::getMessage("TR_CA_DOCS_COMP_DOC_INFO") ?></span>
                        <div class="trca_edo_info_text">
                            <div class="trca_edo_info_doc_properties">
                                <div class="trca_edo_info_doc_first">
                                    <?=  Loc::getMessage("TR_CA_DOCS_COMP_OWNER") ?>
                                </div>
                                <div id="info_file_owner" class="trca_edo_info_doc_second"></div>
                            </div>
                            <!-- <div class="trca_edo_info_doc_properties">
                                <div class="trca_edo_info_doc_first">
                                    <?//=  Loc::getMessage("TR_CA_DOCS_COMP_STATUS") ?>
                                </div>
                                <div id="info_file_status" class="trca_edo_info_doc_second"></div>
                            </div> -->
                            <div class="trca_edo_info_doc_properties">
                                <div class="trca_edo_info_doc_first">
                                    <?=  Loc::getMessage("TR_CA_DOCS_COMP_SIZE") ?>
                                </div>
                                <div id="info_file_size" class="trca_edo_info_doc_second"></div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="trca_edo_info_title">
                        <span><?//=  Loc::getMessage("TR_CA_DOCS_COMP_SIGN_INFO") ?></span>
                        <div class="trca_edo_info_text">
                            <div class="trca_edo_info_sign sign_green">
                                <div class="trca_edo_info_sign_info">
                                    Вы подписали и отправили документ partners@digt.ru
                                    <br>
                                    10 мая 07:58
                                </div>
                            </div>

                            <div class="trca_edo_info_sign sign_orange">
                                Требуется подпись 123@digt.ru
                            </div>
                        </div>
                    </div> -->
                    <div id="trca_edo_info_title_message" class="trca_edo_info_title" style="display: none;">
                        <span><?=  Loc::getMessage("TR_CA_DOCS_COMP_MESSAGES") ?></span>
                        <div class="trca_edo_info_text">
                            <div class="trca_edo_info_message_content">
                                <div class="trca_edo_info_message_header">
                                    <div class="trca_edo_info_message_email"></div>
                                    <div class="trca_edo_info_message_time"></div>
                                </div>
                                <div class="trca_edo_info_message_topic"></div>
                                <div class="trca_edo_info_message_text"></div>
                                <!-- <div class="trca_edo_info_message_docs">
                                </div> -->
                            </div>
                            <div class="trca_edo_info_message_open"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
getLabelList();
getLabelListForEditWindow();

function getLabels(onSuccess = null) {
    $.ajax ({
        url: AJAX_CONTROLLER + '?command=getUserLabels',
        type: 'post',
        success: function (d) {
            d.labels.forEach(label => {
                if (typeof onSuccess == 'function') {
                    onSuccess(label);
                }
            })
        }
    })
}

function getColorTable(area, id) {
    var labelsClasses = ['trca_label_one','trca_label_two','trca_label_three','trca_label_four','trca_label_five','trca_label_six','trca_label_seven','trca_label_eight','trca_label_nine'];
    var colorTable = document.createElement('div');
    colorTable.className = 'trca_edit_label_color_table';
    area.appendChild(colorTable);
    labelsClasses.forEach(labelClass => {
        var colorDiv = document.createElement('div');
        colorDiv.className = 'trca_edit_label_color';
        colorTable.appendChild(colorDiv);
        var radioColor = document.createElement('input');
        radioColor.setAttribute('type', 'radio');
        radioColor.setAttribute('name', 'radio');
        radioColor.setAttribute('value', labelClass);
        radioColor.id = labelClass + id;
        colorDiv.appendChild(radioColor);
        let radioColorLabel = document.createElement('label');
        radioColorLabel.className = labelClass;
        radioColorLabel.setAttribute('for', labelClass + id);
        colorDiv.appendChild(radioColorLabel);
        $(radioColor).change(function () {
            if ($(radioColor).attr("checked")) {
                editLabel(id, null, labelClass)
            }
        });
    })
    $(colorTable).hide();
    return colorTable;
}

function getLabelListForEditWindow() {
    let labelList = document.getElementById("trca_edit_label_list");
    labelList.innerHTML = "";
    let showLabelInEditWindow = (label) => {
        var listItem = document.createElement('div');
        listItem.className = "trca_edit_label_list_item";
        labelList.appendChild(listItem);
        var labelInfoSpace = document.createElement('div');
        labelInfoSpace.className = "trca_edit_label_item_info_space";
        listItem.appendChild(labelInfoSpace);
        var iconPlace = document.createElement('div');
        iconPlace.className = 'trca_edit_label_icon_place';
        labelInfoSpace.appendChild(iconPlace);
        var icon = document.createElement('div');
        icon.className = 'trca_edit_label_list_item_name_png png_' + label.style;
        iconPlace.appendChild(icon);
        var colorTable = getColorTable(iconPlace, label.id);
        iconPlace.appendChild(colorTable);
        iconPlace.onclick = function() {
            var visible = $(colorTable).is(":visible");
            $(".trca_edit_label_color_table").each(function() {
                $(this).hide();
            })
            if (!visible)
                $(colorTable).show();
        }
        var listItemName = document.createElement('div');
        listItemName.className = 'trca_edit_label_list_item_name';
        listItemName.innerText = label.text;
        labelInfoSpace.appendChild(listItemName);
        var labelItemButtons = document.createElement('div');
        labelItemButtons.className = 'trca_edit_label_list_item_buttons';
        listItem.appendChild(labelItemButtons);
        var editButton = document.createElement('div');
        editButton.className = 'trca_edit_edit_button';
        var removeButton = document.createElement('div');
        removeButton.className = 'trca_edit_remove_button';
        removeButton.onclick = function() {
            removeLabel(label.id);
        }
        editButton.onclick = function() {
            inputLabelNameInEdit(listItemName, label.id);
        }
        labelItemButtons.appendChild(editButton);
        labelItemButtons.appendChild(removeButton);
        listItem.onmouseover = function() {
            labelItemButtons.style.opacity='1';
        };
        listItem.onmouseout = function() {
            labelItemButtons.style.opacity='0';
        };
    }
    getLabels(showLabelInEditWindow);
}

function inputLabelNameInEdit(labelName, id) {
    var inputArea = document.createElement('div');
    inputArea.className = 'trca_edit_label_list_item_name';
    $(labelName).replaceWith(inputArea);
    var input = document.createElement('input');
    input.value = labelName.innerText;
    inputArea.appendChild(input);
    input.focus();
    input.addEventListener('focusout', () => {
        let text = input.value;
        console.log(text);
        editLabel(id, text);
        $(inputArea).replaceWith(labelName);
        labelName.innerText = text;
    })
}

function editLabel(labelId, text = null, style = null) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=editLabel',
        type: 'post',
        data: {labelId: labelId, newText: text, newStyle: style},
        success: function(d) {
            getLabelList();
        }
    })
}

function removeLabel(labelId) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=removeLabel',
        type:'post',
        data: {labelId: labelId},
        success: function(d) {
            getLabelList();
            getLabelListForEditWindow();
        }
    })
}

function getLabelList() {
    let labelList = document.getElementById("trca_edo_labels");
    labelList.innerHTML = "";
    let showLabel = (label) => {
        var labelDiv = document.createElement('div');
        labelDiv.className = 'trca_label ' + label.style;
        labelDiv.innerText = label.text;
        labelDiv.setAttribute('label_id', label.id);
        labelDiv.onclick = function() {
            getMessagesByLabel(label.id);
        }
        labelDiv.draggable = true;
        labelList.appendChild(labelDiv);
    }
    getLabels(showLabel);
}

function showLabel(label) {

}

function getMessagesByLabel(labelId) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=getMessagesByLabel',
        type: 'post',
        data: {labelId: labelId},
        success: function (d) {
            createtableMessages(d.messages);
            infoItemInitialization();
            chechActionInitialization();
        }
    })
}

function showModal() {
    $("#trca_upload_window_steps").show();
    $("#trca_upload_window_first_n_second_step").show();
    $('#trca_upload_first_step_name').remove();
    $("#trca_sending_first_step_name").remove();
    let docListName = `
        <span class="trca_upload_window_header_step_name" id="trca_sending_first_step_name">
            <?= Loc::getMessage("TR_CA_DOCS_COMP_UPLOAD_STEP_ONE") ?>
        </span>`;
    let stepname = document.getElementById("trca_first_step");
    stepname.insertAdjacentHTML('beforeend', docListName);
    $("#trca_upload_first_step_name").show();
}

function uploadFile() {
    $("#trca_upload_window_steps").show();
    $("#trca_upload_window_download").show();
    getChecked();
}

function createLabel() {
    let style;
    $('input[value^="trca_label_"]').each(function(){
        if($(this).prop("checked")) {
            style = $(this).val();
        };
    });
    if(!style) {
        console.log("not choose")
    } else {
        console.log(style);
        var text = $("#trca_label_text").val();

        trustedCA.ajax("createLabel", {style, text})
    }
    hideCreateLabelWindow();
    getLabelList();
}

function addFilesInSendListFromUploaded(file_ids) {
    $("#trca_upload_window_first_step").hide();
    $("#trca_upload_window_second_step").show();
    $("#trca_upload_second_step_footer").show();
    file_ids.forEach(file_id => {
        $.ajax({
            url: AJAX_CONTROLLER + '?command=getInfoDoc',
            type: 'post',
            data: {id: file_id},
            success: function (d) {
                var docarea = document.getElementById('trca_upload_file_list');
                var docDiv = document.createElement('div');
                docDiv.id = "trca_doc_" + file_id;
                docDiv.className = "trca_doc_list_item";
                docarea.appendChild(docDiv);
                var docName = document.createElement('div');
                docName.className = "trca_doc_list_item_name " + (d.data.docname.substr(d.data.docname.lastIndexOf(".") + 1));
                docName.title = d.data.docname;
                docName.innerHTML = d.data.docname;
                docDiv.appendChild(docName);
                var docSize = document.createElement('div');
                docSize.className = "trca_doc_list_item_size";
                docSize.innerHTML = d.data.docsize;
                docDiv.appendChild(docSize);
                var docRemove = document.createElement('div');
                docRemove.className = "trca_doc_list_remove material-icons"
                docRemove.innerHTML = "close";
                docRemove.style.color = '#C4C4C4';
                docRemove.onclick = function() {
                    var id = file_ids.indexOf(file_id);
                    file_ids.splice(id, 1);
                    $("#trca_doc_" + file_id).remove();
                    if (file_ids.length == 0) {
                        hideModal();
                    }
                }
                docDiv.appendChild(docRemove);
            }
        });
    });
}

function sendFilesForm() {
    let file_ids = getChecked();
    if (file_ids.length != 0) {
        showModal();
        addFilesInSendListFromUploaded(file_ids);
        showSendForm();
        $(".trca_upload_window_header_step_number").each(function() {
            $(this).css({'opacity':'0'});
        });
        let docListName = `
        <span class="trca_upload_window_header_step_name" id="trca_sending_first_step_name">
            Список Документов
        </span>`;
        $('#trca_upload_first_step_name').remove();
        $("#trca_sending_first_step_name").remove();
        let stepname = document.getElementById("trca_first_step");
        stepname.insertAdjacentHTML('beforeend', docListName);
        let sendButton = document.getElementById("trca_send_button");
        sendButton.onclick = function() {
            send(true, file_ids);
        }
        let saveDraftButton = document.getElementById("trca_send_save_draft_button");
        saveDraftButton.onclick = function() {
            send(false, file_ids);
        }
    }
}

// function sendFiles(ids, send = false) {
//     var recepientEmail = document.getElementById("trca_upload_send_rec").value;
//     var theme = document.getElementById("trca_upload_send_theme").value;
//     var comment = document.getElementById("trca_comment").value;
//     $("#trca_upload_send_rec").val('');
//     $("#trca_upload_send_theme").val('');
//     $("#trca_comment").val('');
//     var docsIds = ids;
//     function writeMesId(d) {
//         let messId = d.messId;
//     };
//     trustedCA.ajax("newMessage", {recepientEmail, theme, comment, docsIds, send}, (d)=>{writeMesId(d)});
//     if (send == true) {
//         $("#trca_upload_window_first_n_second_step").hide();
//         $('#trca_upload_succesful_send').show();
//     }
// }

function downloadFiles(zipName) {
    let ids = getChecked();
    let methodDownload = $('input[name="download_files"]:checked').val();
    switch (methodDownload) {
        case "all":
            trustedCA.download(ids, zipName, true);
            break;
        case "docs":
            trustedCA.download(ids, zipName);
            break;
    }
    hideModal();
}

function remove() {
    let ids = getChecked();
    trustedCA.remove(ids, false, () => {$(".active_menu").click()});
}

function sign() {
    let ids = getChecked();
    trustedCA.sign(ids, null, () => {$(".active_menu").click()});
}

function getChecked() {
    let ids = new Array;
    $('.trca_edo_item').each(function(){
        if($(this).find(".trca_edo_check_item input").prop("checked")) {
            let id = $(this).attr("file_id");
            if (!id) {
                id = $(this).attr("message_id");
            }
            ids.push(id);
        };
    });
    if (ids.length !== 0) {
        return ids;
    } else {
        trustedCA.showPopupMessage("Для дальнейших действий выберите документы", 'highlight_off', 'negative');
        hideModal();
    }
}

searchArea = document.getElementById("trca_search");

searchArea.addEventListener("keyup", function(){
    let length = this.value.length;
    let searchKey = this.value;
    let typeOfMessage = 'all';
    if (length>2) {
        searchDocument(typeOfMessage, searchKey);
    }
});

function searchDocument(typeOfMessage, searchKey) {
    // trustedCA.ajax("searchMessage", {typeOfMessage, searchKey})
    $.ajax({
        url: AJAX_CONTROLLER + '?command=searchMessage',
        type: 'post',
        data: {typeOfMessage: typeOfMessage, searchKey: searchKey},
        success: function (d) {
            createtableMessages(d.messages);
            infoItemInitialization();
            chechActionInitialization();
        }
    })
};

$('.trca_edo_info_close').click( function() {
    $(this).parent().hide();
});

// let labelSearchArea = document.getElementById("trca_label_search");
//
// labelSearchArea.addEventListener("keyup", function(){
//     let length = this.value.length;
//     let searchKey = this.value;
//     let typeOfMessage = 'all';
//     if (length>2) {
//         showLabelWindow(searchKey);
//     }
// });

function infoItemInitialization() {
    $(".trca_edo_item_properties").click(function() {
        let file_id = $(this).parent().attr("file_id");
        if (file_id) {
            $.ajax({
                url: AJAX_CONTROLLER + '?command=getInfoDoc',
                type: 'post',
                data: {id: file_id},
                success: function (d) {
                    if (d.data.message == true) {
                        $("#info_file_owner").text(d.data.docowner);
                        $("#info_file_size").text(d.data.docsize);
                        $(".trca_edo_info_message_email").text(d.data.messageAuthor);
                        $(".trca_edo_info_message_topic").text(d.data.messageTheme);
                        $(".trca_edo_info_message_text").text(d.data.messageContent);
                        $("#trca_edo_info_title_message").show();
                    } else {
                        $("#info_file_owner").text(d.data.docowner);
                        $("#info_file_size").text(d.data.docsize);
                        $("#trca_edo_info_title_message").hide();
                    }
                    $(".trca_edo_info").show();
                }

            });
        } else {
            let message_id = $(this).parent().attr("message_id");
            $.ajax({
                url: AJAX_CONTROLLER + '?command=getMessageInfo',
                type: 'post',
                data: {id: message_id},
                success: function (d) {
                    if (d.data.message == true) {
                        $("#info_file_owner").text(d.data.docowner);
                        $("#info_file_size").text(d.data.docsize);
                        $(".trca_edo_info_message_email").text(d.data.messageAuthor);
                        $(".trca_edo_info_message_topic").text(d.data.messageTheme);
                        $(".trca_edo_info_message_text").text(d.data.messageContent);
                        $("#trca_edo_info_title_message").show();
                    } else {
                        $("#info_file_owner").text(d.data.docowner);
                        $("#info_file_size").text(d.data.docsize);
                        $("#trca_edo_info_title_message").hide();
                    }
                    $(".trca_edo_info").show();
                }
            });
        }
    });
}
infoItemInitialization();

function chechActionInitialization(){
    $(".trca_header_check input").removeClass("show");
    $(".trca_edo_check_item input").click( function() {
        let check = false;
        $('.trca_edo_check_item input').each(function(){
            if($(this).prop("checked")) {
                check = true;
            };
        });

        if (check) {
            $(".trca_edo_check_item input").addClass("show");
            $(".trca_header_check input").addClass("show");
            $(".trca_header_button").addClass("op_5");
            $(this).parent().parent().addClass("trca_edo_item_cheched");
        } else {
            $(".trca_edo_check_item input").removeClass("show");
            $(".trca_header_check input").removeClass("show");
            $(".trca_header_button").removeClass("op_5");
            $(this).parent().parent().removeClass("trca_edo_item_cheched");
        }
    });


    $(".trca_header_check input").click( function() {
        if ($(this).prop("checked")) {
            $(".trca_edo_check_item input").prop( 'checked', true);
            $(".trca_edo_item").addClass("trca_edo_item_cheched");
        } else {
            $(".trca_edo_check_item input").prop( 'checked', false);
            $(".trca_edo_check_item input").removeClass("show");
            $(".trca_header_check input").removeClass("show");
            $(".trca_edo_item").removeClass("trca_edo_item_cheched");
        }
    });
}
chechActionInitialization();

// shared - boolean(0 or 1)
// page - integer(0 => 1)
function getDocList(shared, page) {
    let count = 2;
    let data = {shared: shared, page: page, count: count}
    $(".trca_edo_info").hide();
    $.ajax({
        url: AJAX_CONTROLLER + '?command=getDocList',
        type: 'post',
        data: data,
        success: function (d) {
            let buttons = document.querySelectorAll(".trca_header_button");
            buttons.forEach((element) => {
                element.remove();
            });
            if (shared == 0) {
                $('.trca_edo_download').addClass("active_menu");
                $('.trca_edo_available').removeClass("active_menu");
                createHeaderButton("trca_button_send", "<?= Loc::getMessage("TR_CA_DOCS_COMP_SEND_FILE") ?>", "sendFilesForm()");
                createHeaderButton("trca_button_download", "<?= Loc::getMessage("TR_CA_DOCS_COMP_DOWNLOAD_FILE") ?>", "uploadFile()");
                createHeaderButton("trca_button_remove", "<?= Loc::getMessage("TR_CA_DOCS_COMP_REMOVE_FILE") ?>", "remove()");
            } else {
                $('.trca_edo_available').addClass("active_menu");
                $('.trca_edo_download').removeClass("active_menu");
                createHeaderButton("trca_button_download", "<?= Loc::getMessage("TR_CA_DOCS_COMP_DOWNLOAD_FILE") ?>", "uploadFile()");
                createHeaderButton("trca_button_sign", "<?= Loc::getMessage("TR_CA_DOCS_COMP_SIGN") ?>", "sign()");
            }
            createtableDocs(d.data);
            infoItemInitialization();
            chechActionInitialization();
        }
    });
}
//type - incoming,drafts,outgoing
//page
//count

function setLabelToMessages(messIds) {
    let labelsId = getCheckedLabels();
    if (labelsId.lenth != 0) {
        labelsId.forEach((labelId) => {
            messIds.forEach((messageId) => {
                let data = {labelId: labelId, messageId: messageId};
                $.ajax({
                    url: AJAX_CONTROLLER + '?command=setLabelToMessage',
                    type: 'post',
                    data: data,
                })
            })
        })
    }
}

function getCheckedLabels() {
    let labelIds = new Array;
    $('input[id^="label_"]').each(function() {
        if ($(this).prop("checked")) {
            let idStr = $(this).attr("id");
            let id = idStr.replace("label_", "");
            labelIds.push(id);
        }
    })
    return labelIds;
}


function showLabelWindow() {
    console.log("s");
    let messIds = getChecked();
    if (messIds.length != 0) {
        $("#trca_label_window").show();
        $("#trca_label_window_list").html("");
        getLabelListForLabelWindow(messIds, null);
        $("#trca_label_assign").click(function() {
            setLabelToMessages(messIds);
        })
        let labelSearchArea = document.getElementById("trca_label_search");
        labelSearchArea.value = "";
        labelSearchArea.addEventListener("keyup", function(){
            let searchKey = this.value;
            $("#trca_label_window_list").html("");
            getLabelListForLabelWindow(messIds, searchKey);
        })
        jQuery(function($){
            $(document).mouseup(function (e){ // событие клика по веб-документу
                var div = $("#trca_label_window"); // тут указываем ID элемента
                if (!div.is(e.target) // если клик был не по нашему блоку
                    && div.has(e.target).length === 0) { // и не по его дочерним элементам
                    console.log(1);
                    div.hide(); // скрываем его
                }
            });
        });
    }
}

function showCreateLabelWindow(labelName = null) {
    $("#trca_create_label_modal").show();
    $("#trca_label_text").val("");
    if(labelName) {
        $("#trca_label_text").val(labelName);
    }
}

function hideCreateLabelWindow() {
    $("#trca_label_text").val("");
    $("#trca_create_label_modal").hide();
}

function getLabelListForLabelWindow(messIds, searchKey) {
    let labelsSearchParams = {messIds: messIds, searchKey: searchKey};
    let labelList = document.getElementById("trca_label_window_list");
    $("#trca_label_window_list").show();
    $("#trca_label_assign").show();
    $("#trca_label_edit").show();
    $("#trca_label_new").show();
    $("#trca_label_footer_create").remove();
    $.ajax({
        url: AJAX_CONTROLLER + '?command=getInfoForLabelWindow',
        type: 'post',
        data: labelsSearchParams,
        success: function (d) {
            if (d.labels.length == 0) {
                $("#trca_label_window_list").hide();
                $("#trca_label_assign").hide();
                $("#trca_label_edit").hide();
                $("#trca_label_new").hide();
                let createLabelFooterButton = document.createElement('div');
                createLabelFooterButton.id = 'trca_label_footer_create';
                createLabelFooterButton.className = "trca_label_window_footer_button trca_label_create";
                createLabelFooterButton.innerText = '<?= Loc::getMessage("TR_CA_DOCS_COMP_CREATE"); ?> "' + searchKey + '"';
                let footer = document.getElementById("trca_label_window_footer");
                footer.appendChild(createLabelFooterButton);
                createLabelFooterButton.onclick = function() {
                    showCreateLabelWindow(searchKey);
                }
            }else {
                d.labels.forEach((label) => {
                    let labelElement = `
                    <div class="trca_label_window_list_item">
                        <input type="checkbox" id="label_${label.id}">
                        <label for="label_${label.id}">${label.text}<label>
                    </div>`
                    let checkbox = document.getElementById("label_" + label.id);
                    labelList.insertAdjacentHTML("beforeend", labelElement);
                    // $('#label_' + label.id).prop("indeterminate", true);
                    switch (label.checkbox) {
                        case 'unchecked':
                            break;
                        case 'checked':
                            break;
                        case 'indeterminate':
                            break;
                    }
                });
            }
        }
    })
}

function setThreeStateOfCheckbox(checkbox) {

}

function getMessageList(type, page) {
    let count = 10;
    let data = {typeOfMessage: type, page: page, count: count}
    $(".trca_edo_info").hide();
    $.ajax({
        url: AJAX_CONTROLLER + '?command=getMessageList',
        type: 'post',
        data: data,
        success: function (d) {
            let buttons = document.querySelectorAll(".trca_header_button");
            buttons.forEach((element) => {
                element.remove();
            });
            switch (type) {
                case 'incoming':
                    createHeaderButton("trca_button_send", "<?= Loc::getMessage("TR_CA_DOCS_COMP_SEND_FILE") ?>", "sendFilesForm()");
                    createHeaderButton("trca_button_label", "<?= Loc::getMessage("TR_CA_DOCS_COMP_LABEL") ?>", "showLabelWindow()");
                    break;
                case 'outgoing':
                    createHeaderButton("trca_button_label", "<?= Loc::getMessage("TR_CA_DOCS_COMP_LABEL") ?>", "showLabelWindow()");
                    createHeaderButton("trca_button_recall", "<?= Loc::getMessage("TR_CA_DOCS_COMP_RECALL") ?>", "sendFilesForm()");
                    break;
                case 'drafts':
                    createHeaderButton("trca_button_send", "<?= Loc::getMessage("TR_CA_DOCS_COMP_SEND_FILE") ?>", "sendFilesForm()");
                    createHeaderButton("trca_button_label", "<?= Loc::getMessage("TR_CA_DOCS_COMP_LABEL") ?>", "showLabelWindow()");
                    createHeaderButton("trca_button_remove", "<?= Loc::getMessage("TR_CA_DOCS_COMP_REMOVE_FILE") ?>", "sendFilesForm()");
                    break;
            }
            createtableMessages(d.messages);
            infoItemInitialization();
            chechActionInitialization();
        }
    });
}

function createtableDocs(docs) {
    let table = document.getElementById("trca_edo_items_table");
    table.innerHTML = "";

    docs.forEach(doc => {
        if (doc.owner == true) {
            doc.owner = "<?= Loc::getMessage("TR_CA_DOCS_COMP_OWNER_I") ?>";
        }
        let element = {};
        element.file_id = doc.id;
        element.fisrt = doc.name;
        element.second = '';
        element.third = "<?= Loc::getMessage("TR_CA_DOCS_COMP_OWNER") ?>" + doc.owner;
        element.fourth = '';
        element.dateCreated = doc.dateCreated;
        let itemTable = createItemTable(element);
        table.appendChild(itemTable);
    });
}

function createtableMessages(messages) {
    console.log(messages);
    let table = document.getElementById("trca_edo_items_table");
    table.innerHTML = "";

    messages.forEach(message => {
        let element = {};
        element.message_id = message.id;
        element.docs = message.docs;
        element.fisrt = message.sender;
        element.second = message.theme;
        if (message.labels) {
            element.labels = message.labels;
        }
        element.third = message.comment;;
        element.dateCreated = message.time;
        let itemTable = createItemTable(element);
        table.appendChild(itemTable);
    });
}


function createItemTable(element) {
    let itemTable = document.createElement("div");
    itemTable.className= "trca_edo_item";
    if (element.file_id)
        itemTable.setAttribute("file_id", element.file_id);
    if (element.message_id)
        itemTable.setAttribute("message_id", element.message_id);

    if ( element.docs) {
        element.fourth  = ``;
        element.docs.forEach(doc =>{
            element.fourth += `
            <div class="trca_edo_item_first_col_row_file ${doc.name.substr(doc.name.lastIndexOf(".") + 1)}" file_id="${doc.id}">${doc.name}</div>`;
        });
    }
    let itemTableContent= `
    <div class="trca_edo_check_item">
        <input type="checkbox">
    </div>
    <div class="trca_edo_item_properties">
        <div class="trca_edo_item_first_col">
            <div class="trca_edo_item_first_col_row first   c_black f_s_16">${element.fisrt}</div>
            <div class="trca_edo_item_first_col_row second  c_black">${element.second}</div>
            <div class="trca_edo_item_first_col_row third   c_gray">${element.third}</div>
            <div class="trca_edo_item_first_col_row fourth  c_gray ">${element.fourth}</div>
        </div>
        <div class="trca_edo_item_time">
            ${element.dateCreated}
        </div>
    </div>`;
    itemTable.innerHTML = itemTableContent;
    if (element.labels) {
        element.labels.forEach(label => {
            let labelDiv = document.createElement("div");
            labelDiv.className = "trca_label " + label.style;
            labelDiv.innerText = label.text;
            itemTable.appendChild(labelDiv);
        })
    }
    return itemTable;
}

function createHeaderButton(style, title, action) {
    let button = document.createElement("div");
    button.classList = "trca_header_button ";
    button.className += style;
    button.setAttribute("title", title);
    button.setAttribute("onclick", action);
    button.textContent = title;

    let header = document.getElementById("trca_edo_header_menu_buttons");
    header.appendChild(button);
}

</script>