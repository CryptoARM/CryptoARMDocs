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
?>

<!-- <a id="trca-reload-doc" href="<?//= $_SERVER["REQUEST_URI"] ?>"></a> -->

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
                <div class="trca_edo_labels">
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
function showModal() {
    $("#trca_upload_window_steps").show();
    $("#trca_upload_window_first_n_second_step").show();
}

function uploadFile() {
    $("#trca_upload_window_steps").show();
    $("#trca_upload_window_download").show();
    getChecked();
}

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
                createHeaderButton("trca_button_send", "<?= Loc::getMessage("TR_CA_DOCS_COMP_SEND_FILE") ?>", null);
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
function getMessageList(type, page) {
    let count = 10;
    let data = {typeOfMessage: type, page: page, count: count}
    $(".trca_edo_info").hide();
    $.ajax({
        url: AJAX_CONTROLLER + '?command=getMessageList',
        type: 'post',
        data: data,
        success: function (d) {
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