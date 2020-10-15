function initLangForUpload() {
    UPLOAD_STEP_TWO = BX.message("TR_CA_DOCS_COMP_UPLOAD_STEP_TWO");
    UPLOAD_SEND_RECEPIENT = BX.message("TR_CA_DOCS_COMP_UPLOAD_SEND_RECEPIENT");
    SEND_RECEPIENT_1 = BX.message("TR_CA_DOCS_COMP_UPLOAD_SEND_RECEPIENT_1");
    SEND_THEME = BX.message("TR_CA_DOCS_COMP_UPLOAD_SEND_THEME");
    SEND_COMMENT = BX.message("TR_CA_DOCS_COMP_UPLOAD_SEND_COMMENT");
    SIGN_BEFORE = BX.message("TR_CA_DOCS_COMP_UPLOAD_SIGN_BEFORE");
    UPLOAD_CANCEL = BX.message("TR_CA_DOCS_COMP_UPLOAD_CANCEL");
    UPLOAD_SAVE = BX.message("TR_CA_DOCS_COMP_UPLOAD_SAVE");
    UPLOAD_SEND = BX.message("TR_CA_DOCS_COMP_UPLOAD_SEND");
    B = BX.message("TR_CA_DOCS_COMP_UPLOAD_B");
    KB = BX.message("TR_CA_DOCS_COMP_UPLOAD_KB");
    MB = BX.message("TR_CA_DOCS_COMP_UPLOAD_MB");
    GB = BX.message("TR_CA_DOCS_COMP_UPLOAD_GB");
}

function initForUpload() {
    initLangForUpload();
    console.log(UPLOAD_CANCEL);
    let dropArea = document.getElementById("trca_drop_area");

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    dropArea.addEventListener('drop',handleDrop, false);

    function highlight() {
        dropArea.classList.add('highlight');
    }

    function unhighlight() {
        dropArea.classList.remove('highlight');
    }

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
}

function handleDrop(e) {
    let dt = e.dataTransfer;
    let files = dt.files;
    handleFiles(files);
}

function cancelUpload() {
    $(".trca_doc_list_remove").each(function(){
        $(this).click();
    })
    hideModal();
}

function getProgressCircle(i) {
    return `<div class="circle-out" id=circle_`+i+`>
                <div class="progress" id="progress_`+i+`"></div>
                <div class="circle-in"> </div>
            </div> `;
}

function getFileSize(size) {
    if (size < 1024) {
        return size + B;
    } else {
        let sizeString = Math.floor(size / 1024);
        if (sizeString < 1024) {
            return sizeString + KB;
        } else {
            sizeString = Math.floor(sizeString / 1024);
            if (sizeString < 1024) {
                return sizeString + MB;
            } else {
                sizeString = Math.floor(sizeString / 1024)
                return sizeString + GB;
            }
        }
    }
}

function uploadFiles() {
    $("#trca_upload_window_first_n_second_step").hide();
    $("#trca_upload_success").show();
}

function addTemporaryListItem(file, i, xhr) {
    var docarea = document.getElementById('trca_upload_file_list');

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

function addAndUpload(file, docarea, i, files) {
    var name = 'USER';
    var xhr;
    function getXHR(request) {
        xhr = request;
    }
    var value = document.getElementById("trca_data").getAttribute("userid");
    $("#trca_upload_window_header_upload_more").show();
    $("#trca_upload_window_first_step").hide();
    $("#trca_upload_window_second_step").show();
    $("#trca_upload_second_step_footer").show();

    function getUploadedDocId(item) {
        addFileInList(file, docarea, item, i);
    }

    function fileOnLoad(loaded, total, i) {
        var progress = loaded/total * 180;
        var progressEl = document.querySelector('#progress_'+i);
        progressEl.style.transform = 'rotate('+progress+'deg)';
    }

    var props  = new Map([
        [name, value],
    ])
    trustedCA.uploadFile(file, props, (item)=>{getUploadedDocId(item)}, null, true, (loaded, total)=>{fileOnLoad(loaded, total, i)}, (request)=>{getXHR(request)});
    addTemporaryListItem(file, i, xhr, files);
}

function handleFiles(files) {
    // console.log(files);
    //maxsize = "<?//= Docs\Utils::maxUploadFileSize() ?>//"
    maxsize = document.getElementById("trca_data").getAttribute("maxsize");
    var docarea = document.getElementById('trca_upload_file_list');
    for (let i = 0; i < files.length; i++) {
        file = files[i];
        trustedCA.checkFileSize(file, maxsize, () => {
            trustedCA.checkName(file, () => {
                trustedCA.checkAccessFile(file, addAndUpload(file, docarea, i, files))
            })
        });
    };
}

function addFileInList(file, docarea, currDocId, i) {
    // filesToUpload.push(file);

    var docDiv = document.createElement('div');
    docDiv.id = "trca_doc_solid_" + currDocId;
    docDiv.className = "trca_doc_list_item";
    docDiv.setAttribute("doc_id", currDocId);
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
        $.ajax({
            url: AJAX_CONTROLLER + '?command=remove',
            type: 'post',
            data: {ids}
        })
    }
    docDiv.appendChild(docRemove);
}

function getDocsIds() {
    var ids = [];
    $('[id^="trca_doc_solid"]').each(function () {
        var id = this.getAttribute('doc_id');
        ids.push(id);
    })
    return ids;
}


function removeFromList(divid, file) {
    $('#' + divid).remove();
    if ($(".trca_doc_list_item").length == 0) {
        toFirstStep()
    };
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
                ${UPLOAD_STEP_TWO}
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
        <label for="trca_upload_send_rec">${UPLOAD_SEND_RECEPIENT}</label>
        <input id="trca_upload_send_rec" placeholder=${SEND_RECEPIENT_1}">
    </div>
    <div class="trca_upload_window_send_form_field">
        <label for="trca_upload_send_theme">${SEND_THEME}</label>
        <input id=trca_upload_send_theme>
    </div>
    <div class="trca_upload_window_send_form_comment_field">
        <textarea id="trca_comment" placeholder="${SEND_COMMENT}" style="resize: none" ></textarea>
    </div>
    <div class="trca_upload_window_send_form_require_sign">
        <input type="checkbox" id="trca_upload_window_send_form_require_sign" class="trca_require_checkbox">
        <label for="trca_upload_window_send_form_require_sign">${SIGN_BEFORE}</label>
    </div>`;
    sendForm.insertAdjacentHTML('beforeend', sendFormContent);
    let sendFormFooter = `
    <div class="trca_upload_window_footer" id="trca_upload_third_step_footer" style="justify-content: space-between">
        <div class="trca_upload_window_footer_docs_actions" style="width: 37%">
            <div class="trca_upload_window_footer_cancel" onclick="showSaveDraftPopup()">
                <span class="trca_upload_window_footer_cancel_text">
                    ${UPLOAD_CANCEL}
                </span>
            </div>
            <div class="trca_upload_window_footer_save_in_docs" onclick="uploadFiles()">
                ${UPLOAD_SAVE}
            </div>
        </div>
            <div class="trca_upload_window_footer_send_button" onclick=send(true) id="trca_send_button">
                ${UPLOAD_SEND}
            </div>
    </div>`;
    uploadWindow.insertAdjacentHTML('beforeend', sendFormFooter);
}

function send(send = false, ids = null) {
    var recepientEmail = $("#trca_upload_send_rec").val();
    var theme = $("#trca_upload_send_theme").val();
    var comment = $("#trca_comment").val();
    let docsIds;
    if (ids != null) {
        docsIds = ids;
    } else {
        docsIds = getDocsIds();
    }
    let messId;
    $.ajax({
        url: AJAX_CONTROLLER + '?command=newMessage',
        type: 'post',
        data: {recepientEmail, theme, comment, docsIds, send},
        success: function (d) {
            messId = d.messId;
            if (send == true) {
                $("#trca_upload_window_first_n_second_step").hide();
                $('#trca_upload_succesful_send').show();
                $('#trca_upload_cancel_send').click(function() {
                    cancelSend(messId);
                })
            }
        }
    })
}

function cancelSend(messId) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=sendCancel',
        type: 'post',
        data: {messId},
    })
    hideModal();
}

function showSaveDraftPopup() {
    $("#trca_upload_window_first_n_second_step").hide();
    $("#trca_upload_save_draft").show();

}

function hideModal() {
    toFirstStep();
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