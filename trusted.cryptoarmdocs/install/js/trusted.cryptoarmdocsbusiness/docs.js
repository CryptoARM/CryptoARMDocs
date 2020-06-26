if (!trustedCA) {
    var trustedCA = {};
}

// ===============================
// === Get js library messages ===
// ===============================
trustedCA.initVar = function () {
    AJAX_CONTROLLER = window.location.protocol + '//' + window.location.host + BX.message('TR_CA_DOCS_AJAX_CONTROLLER');
    NO_CLIENT = BX.message('TR_CA_DOCS_ALERT_NO_CLIENT');
    HTTP_WARNING = BX.message('TR_CA_DOCS_ALERT_HTTP_WARNING');
    REMOVE_ACTION_CONFIRM = BX.message('TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM');
    REMOVE_ACTION_CONFIRM_MANY = BX.message('TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM_MANY');
    REMOVE_FORM_ACTION_CONFIRM = BX.message('TR_CA_DOCS_ALERT_REMOVE_FORM_ACTION_CONFIRM');
    REMOVE_FORM_ACTION_CONFIRM_MANY = BX.message('TR_CA_DOCS_ALERT_REMOVE_FORM_ACTION_CONFIRM_MANY');
    LOST_DOC_REMOVE_CONFIRM_PRE = BX.message('TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE');
    LOST_DOC_REMOVE_CONFIRM_POST = BX.message('TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_POST');
    IN_WF = BX.message('TR_CA_DOCS_ALERT_IN_WF');
    LOST_DOC = BX.message('TR_CA_DOCS_ALERT_LOST_DOC');
    ERROR_NO_AUTH = BX.message('TR_CA_DOCS_ERROR_NO_AUTH');
    ERROR_NO_IDS = BX.message('TR_CA_DOCS_ERROR_NO_IDS');
    ERROR_FILE_NOT_FOUND = BX.message('TR_CA_DOCS_ERROR_FILE_NOT_FOUND');
    ERROR_DOC_NOT_FOUND = BX.message('TR_CA_DOCS_ERROR_DOC_NOT_FOUND');
    ERROR_DOC_BLOCKED = BX.message('TR_CA_DOCS_ERROR_DOC_BLOCKED');
    ERROR_DOC_ROLE_SIGNED = BX.message('TR_CA_DOCS_ERROR_DOC_ROLE_SIGNED');
    ERROR_DOC_UNSIGNED = BX.message('TR_CA_DOCS_ERROR_DOC_UNSIGNED');
    ERROR_DOC_NO_ACCESS = BX.message('TR_CA_DOCS_ERROR_DOC_NO_ACCESS');
    ERROR_DOC_WRONG_SIGN_TYPE = BX.message('TR_CA_DOCS_ERROR_DOC_WRONG_SIGN_TYPE');
    SEND_MAIL_SUCCESS = BX.message('TR_CA_DOCS_ACT_SEND_MAIL_SUCCESS');
    SEND_MAIL_FAILURE = BX.message('TR_CA_DOCS_ACT_SEND_MAIL_FAILURE');
    SEND_MAIL_TO_PROMPT = BX.message('TR_CA_DOCS_ACT_SEND_MAIL_TO_PROMPT');
    SHARE_SUCCESS_1 = BX.message('TR_CA_DOCS_ACT_SHARE_SUCCESS_1');
    SHARE_SUCCESS_2 = BX.message('TR_CA_DOCS_ACT_SHARE_SUCCESS_2');
    HAVE_ACCESS = BX.message('TR_CA_DOCS_ACT_SHARE_HAVE_ACCESS');
    SHARE_IS_OWNER = BX.message('TR_CA_DOCS_ACT_SHARE_IS_OWNER');
    REQUIRE_SUCCESS_1 = BX.message('TR_CA_DOCS_ACT_REQUIRE_SUCCESS_1');
    REQUIRE_SUCCESS_2 = BX.message('TR_CA_DOCS_ACT_REQUIRE_SUCCESS_2');
    SHARE_NO_USER_1 = BX.message('TR_CA_DOCS_ACT_SHARE_NO_USER_1');
    SHARE_NO_USER_2 = BX.message('TR_CA_DOCS_ACT_SHARE_NO_USER_2');
    DOWNLOAD_FILE_1 = BX.message("TR_CA_DOCS_ACT_DOWNLOAD_FILE_1");
    DOWNLOAD_FILE_2 = BX.message("TR_CA_DOCS_ACT_DOWNLOAD_FILE_2");
    DOWNLOAD_FILE_ZERO_SIZE = BX.message("TR_CA_DOCS_ACT_DOWNLOAD_FILE_ZERO_SIZE");
    DOWNLOAD_FILE_ERROR_NAME = BX.message("TR_CA_DOCS_ACT_ERROR_NAME");
    MODAL_MESSAGE_1 = BX.message('TR_CA_DOCS_MODAL_MESSAGE_1');
    MODAL_MESSAGE_2 = BX.message('TR_CA_DOCS_MODAL_MESSAGE_2');
    MODAL_MESSAGE_MANY_1 = BX.message('TR_CA_DOCS_MODAL_MESSAGE_MANY_1');
    MODAL_MESSAGE_MANY_2 = BX.message('TR_CA_DOCS_MODAL_MESSAGE_MANY_2');
    MODAL_CANCEL = BX.message('TR_CA_DOCS_MODAL_CANCEL');
    ACT_SHARE = BX.message('TR_CA_DOCS_ACT_SHARE');
    UNSHARE_CONFIRM = BX.message('TR_CA_DOCS_UNSHARE_CONFIRM');
    SIGN_TYPE = BX.message('TR_CA_DOCS_SIGN_TYPE');
    UNSHARE_FROM_MODAL_CONFIRM = BX.message('TR_CA_DOCS_UNSHARE_FROM_MODAL_CONFIRM');
    NO_ACCESS_FILE = BX.message('TR_CA_DOCS_NO_ACCESS_FILE');
    CLOSE_WINDOW = BX.message('TR_CA_DOCS_CLOSE_INFO_WINDOW');
    VERIFY_DOC = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_VERIFY');
    SHARE_DOC = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_SHARE');
    SIGN_DOC = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_SIGN');
    DOWNLOAD_DOC = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_DOWNLOAD');
    DOWNLOAD_PROTOCOL = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_PROTOCOL');
    MODAL_INFO_NOT_SHARED = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_NOT_SHARED');
    MODAL_INFO_STATUS_READ = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_STATUS_READ');
    MODAL_INFO_STATUS_MUST_TO_SIGN = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_STATUS_MUST_TO_SIGN');
    MODAL_INFO_STATUS_SIGNED = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_STATUS_SIGNED');
    MODAL_INFO_STATUS_UNSIGNED = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_STATUS_UNSIGNED');
    MODAL_INFO_STATUS_UNSHARE = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_UNSHARE');

};

// Modal window
trustedCA.modalWindowSign = `
    <div class="trca-modal-overlay" id="trca-modal-overlay"></div>
    <div class="trca-modal-window" id="trca-modal-window">
        <div class="trca-modal-header" id="trca-modal-header"></div>
        <div class="trca-modal-content">
            <div class="trca-modal-content-icon" id=class="trca-modal-content-icon">
                <i class="material-icons" style="font-size: 41px;">error_outline</i>
            </div>
            <div class="trca-modal-content-message" id="trca-modal-content-message"></div>
        </div>
        <div class="trca-modal-spinner" id="trca-modal-spinner"></div>
        <div class="trca-modal-footer" id="trca-modal-footer">
            <div class="trca-modal-close" id="trca-modal-close"></div>
        </div>
    </div>
`;
trustedCA.modalDiv = document.createElement("div");

trustedCA.modalWindowInfo = `
    <div class="trca-modal-overlay" id="trca-modal-overlay"></div>
    <div class="trca-modal-info-window" id="trca-modal-info-window">
        <div class="trca-modal-header" id="trca-modal-info-header"></div>
        <div class="trca-modal-info-content" id="trca-modal-info-content">
        <div class="trca-modal-info-content-left" id = "trca-modal-info-content-left">
            <div class="trca-modal-info-close" id="trca-modal-info-close-left"></div>
        </div>
        <div class="trca-modal-info-content-right">
            <div class="trca-modal-info-button" id="trca-modal-info-button-verify">
                <i class="material-icons">help</i>
                <div class="trca-modal-info-button-message" id="trca-modal-info-button-message-verify"></div>
            </div>
            <div class="trca-modal-info-button" id="trca-modal-info-button-sign">
                <i class="material-icons">edit</i>
                <div class="trca-modal-info-button-message" id="trca-modal-info-button-message-sign"></div>
            </div>
            <div class="trca-modal-info-button" id="trca-modal-info-button-download">
                <i class="material-icons">file_download</i>
                <div class="trca-modal-info-button-message" id="trca-modal-info-button-message-download"></div>
            </div>
            <div class="trca-modal-info-button" id="trca-modal-info-button-protocol">
                <i class="material-icons">info</i>
                <div class="trca-modal-info-button-message" id="trca-modal-info-button-message-protocol"></div>
            </div>
            <div class="trca-modal-info-button" id="trca-modal-info-button-share" style="display: none;">
                <i class="material-icons">supervisor_account</i>
                <div class="trca-modal-info-button-message" id="trca-modal-info-button-message-share"></div>
            </div>
            <div class="trca-modal-info-close" id="trca-modal-info-close"></div>
        </div>
    </div>
`;
trustedCA.modalInfoDiv = document.createElement("div");

// Fixes errors after authorization
if (BX.message('TR_CA_DOCS_AJAX_CONTROLLER')) {
    trustedCA.initVar();
} else {
    setTimeout(function () {
        trustedCA.initVar()
    }, 100);
}

// =========================
// === Module js library ===
// =========================

trustedCA.ajax = function (command, data, onSuccess = null, onFailure = null) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=' + command,
        type: 'post',
        data: data,
        // Ajax request succeeds
        success: function (d) {
            // Report any errors
            trustedCA.show_messages(d);
            // Command execution succeeds
            if (d.success) {
                if (typeof onSuccess === 'function') {
                    onSuccess(d);
                } else {
                    console.log(d.message);
                }
                // Command execution fails
            } else {
                if (typeof onFailure === 'function') {
                    onFailure(d);
                } else {
                    console.log(d.message);
                }
            }
        },
        // Ajax request fails
        error: function (e) {
            console.error(e);
            try {
                var d = JSON.parse(e.responseText);
                if (d.success === false) {
                    console.log(d);
                }
            } catch (e) {
                console.error(e);
            }
        }
    });
};


trustedCA.sign = function (ids, extra = null, onSuccess = null, onFailure = null) {
    if (location.protocol === 'http:') {
        alert(HTTP_WARNING);
        return;
    }
    if (extra === null) {
        extra = {};
    }
    if (typeof extra['signType'] !== "undefined") {
        extra.signType = 0;
    }
    $.ajax({
        url: AJAX_CONTROLLER + '?command=createTransaction',
        type: 'post',
        data: {id: ids, method: "sign"},
        success: function (d) {
            if (d.success) {
                let url = "cryptoarm://sign/" + AJAX_CONTROLLER  + '?command=JSON&accessToken=' + d.uuid;
                window.location = url;
                ids = [];
                try {
                    docs = JSON.parse(d.docsOk);
                } catch (e) {
                    console.log(e);
                    return;
                }
                docs.forEach(function (elem) {
                    ids.push(elem.id);
                });
                ids = [];
                docs.forEach(function (elem) {
                    ids.push(elem.id);
                });
                console.log(ids);
                trustedCA.showModalWindow(ids);
                let interval = setInterval(() => trustedCA.blockCheck([d.uuid], interval, onSuccess), 5000);
            } else {
                trustedCA.show_messages(d);
            }
        },
        error: function (e) {
            console.error(e);
            try {
                var d = JSON.parse(e.responseText);
                if (d.success === false) {
                    console.log(d);
                }
            } catch (e) {
                console.error(e);
            }
        }
    });
};

trustedCA.blockCheck = function (tokens, interval, onSuccess) {
    let onFailure = (e) => {
        clearInterval(interval);
        interval = 0;
        document.body.removeChild(trustedCA.modalDiv)
        trustedCA.reloadDoc();
        if (typeof onSuccess === 'function') {
            onSuccess();
        }
    }
    trustedCA.ajax('blockCheck', {blockTokens: tokens}, () => {
    }, onFailure);
};

trustedCA.showModalWindow = function (ids) {
    trustedCA.modalDiv.className = "trca-modal";
    trustedCA.modalDiv.innerHTML = trustedCA.modalWindowSign;
    document.body.appendChild(trustedCA.modalDiv);
    trustedCA.reloadDoc();
    $('#trca-modal-close').attr('onclick', "trustedCA.unblock([" + ids + "], () => {$('#trca-modal-window').hide(); $('#trca-modal-overlay').hide()})");
    if (ids.length === 1) {
        $('#trca-modal-header').text(MODAL_MESSAGE_1);
        $('#trca-modal-content-message').text(MODAL_MESSAGE_2 + String.fromCharCode(171) + MODAL_CANCEL + String.fromCharCode(187) + '.');
    } else {
        $('#trca-modal-header').text(MODAL_MESSAGE_MANY_1);
        $('#trca-modal-content-message').text(MODAL_MESSAGE_MANY_2 + String.fromCharCode(171) + MODAL_CANCEL + String.fromCharCode(187) + '.');
    }
    $('#trca-modal-close').text(MODAL_CANCEL);
}

trustedCA.getInfoForModalWindow = (id) => {
     return new Promise((resolve, reject) => {
        $.ajax({
            url: AJAX_CONTROLLER + '?command=getInfoForModalWindow',
            type: 'post',
            data: {id: id},
            success: function (d) {
                if (d.success) {
                    resolve(d);
                } else {
                    console.log("Something wrong");
                    reject(d);
                }
            },
            error: function (e) {
                console.error(e);
                try {
                    var d = JSON.parse(e.responseText);
                    if (d.success === false) {
                        console.log(d);
                    }
                } catch (e) {
                    console.error(e);
                }
            }
        });
        setTimeout(() => resolve('timeout'), 2000);
    })
}

trustedCA.showInfoModalWindow = function (ids, docname, sharedstatus, currentuseraccess) {
    id = ids[0];
    trustedCA.modalInfoDiv.className = "trca-modal";
    trustedCA.modalInfoDiv.innerHTML = trustedCA.modalWindowInfo;
    document.body.appendChild(trustedCA.modalInfoDiv);
    if (sharedstatus.length === 0) {
        trustedCA.modalInfoRowDiv = document.createElement("div");
        trustedCA.modalInfoRowDiv.innerHTML = `
        <div class="trca-modal-info-name" id="trca-modal-info-name" style="width: 100%"></div>
        `;
        trustedCA.modalInfoRowDiv.className = "trca-modal-info-row";
        var div = document.getElementById("trca-modal-info-content-left");
        div.insertBefore(trustedCA.modalInfoRowDiv, div.childNodes[0]);
        $("#trca-modal-info-name").text(MODAL_INFO_NOT_SHARED);
    } else {
        $.each(sharedstatus, function (index, value) {
            if (value.access_level === 'READ') {
                var textStatus = MODAL_INFO_STATUS_READ;
                var icon = "insert_drive_file";
                var style = "color: rgb(33, 150, 243)";
            } else if (value.mustToSign === true) {
                var textStatus = MODAL_INFO_STATUS_MUST_TO_SIGN;
                var icon = "reply_all";
                var style = "color: rgb(33, 150, 243)";
            } else if (value.mustToSign === false && value.signed === true) {
                var textStatus = MODAL_INFO_STATUS_SIGNED;
                var icon = "done_all";
                var style = "color: green";
            } else if (value.mustToSign === false && value.signed === false) {
                var textStatus = MODAL_INFO_STATUS_UNSIGNED;
                var icon = "insert_drive_file";
                var style = "color: rgb(33, 150, 243)";
            }

            trustedCA.modalInfoRow = `
                <i class="material-icons" style="${style}">${icon}</i>
                <div class="trca-modal-info-name" id="trca-modal-info-name-${index}"></div>
                <div class="trca-modal-info-status" id="trca-modal-info-status-${index}"></div>
                <div class="trca-modal-info-button-unshare" id="trca-modal-info-button-unshare-${index}"  title = "${MODAL_INFO_STATUS_UNSHARE}" style="display: none">
                    <div class="material-icons">close</div>
                </div>
            `
            ;

            trustedCA.modalInfoRowDiv = document.createElement("div");
            trustedCA.modalInfoRowDiv.innerHTML = trustedCA.modalInfoRow;
            trustedCA.modalInfoRowDiv.className = "trca-modal-info-row";
            trustedCA.modalInfoRowDiv.id = "trca-modal-info-row-" + index + "";
            var div = document.getElementById("trca-modal-info-content-left");
            div.insertBefore(trustedCA.modalInfoRowDiv, div.childNodes[0]);
            $("#trca-modal-info-name-" + index + "").text(value.name);
            $("#trca-modal-info-status-" + index + "").text(textStatus);
            if (currentuseraccess === 'OWNER') {
                document.getElementById("trca-modal-info-button-unshare-" + index + "").style.display = "flex";
                $("#trca-modal-info-button-unshare-" + index + "").attr('onclick', "trustedCA.unshare([" + ids + "], (" + value.id + "), false, () => trustedCA.removeModalRow(" + index + "))");
            }
        })
    }

    var width = window.matchMedia("(max-width: 750px)");

    function replaceCloseButton(width) {
        style = width.matches ? "margin-top: 65%;" : "display: none;";
        document.getElementById("trca-modal-info-close-left").style = style;
    }

    replaceCloseButton(width);
    width.addListener(replaceCloseButton);

    $('#trca-modal-info-button-verify').attr('onclick', "trustedCA.verify([" + ids + "])");
    $('#trca-modal-info-button-message-verify').text(VERIFY_DOC);
    $('#trca-modal-info-button-download').attr('onclick', "trustedCA.download([" + ids + "], true)");
    $('#trca-modal-info-button-message-download').text(DOWNLOAD_DOC);
    $('#trca-modal-info-button-sign').attr('onclick', "trustedCA.sign([" + ids + "])");
    $('#trca-modal-info-button-message-sign').text(SIGN_DOC);
    $('#trca-modal-info-button-protocol').attr('onclick', "trustedCA.protocol(" + id + ")");
    $('#trca-modal-info-button-message-protocol').text(DOWNLOAD_PROTOCOL);
    if (currentuseraccess === 'OWNER') {
        document.getElementById("trca-modal-info-button-share").style.display = "flex";
        $('#trca-modal-info-button-share').attr('onclick', "trustedCA.promptAndShare([" + ids + "], 'SHARE_SIGN', true)");
        $('#trca-modal-info-button-message-share').text(SHARE_DOC);
    }
    $('.trca-modal-info-close').attr('onclick', "{$('#trca-modal-info-window').hide(); $('#trca-modal-overlay').hide()}");
    $('#trca-modal-info-header').text(docname);
    $('#trca-modal-info-close').text(CLOSE_WINDOW);
    $('#trca-modal-info-close-left').text(CLOSE_WINDOW);
    setTimeout(() => {
            if ($('#trca-modal-info-window').is(":visible")) {
                trustedCA.getInfoForModalWindow(id).then(
                    docInfo => {
                        if (!docInfo.success) {
                            console.log("Something wrong");
                            return false;
                        }
                        docname = docInfo.data.docname;
                        sharedstatus = docInfo.data.sharedstatus;
                        currentuseraccess = docInfo.data.currentuseraccess;
                        $('#trca-modal-info-window').hide();
                        $('#trca-modal-overlay').hide();
                        trustedCA.showInfoModalWindow(ids, docname, sharedstatus, currentuseraccess);
                    }
                )
            }
        },
        5000
    );
}

trustedCA.removeModalRow = function (index) {
    document.getElementById("trca-modal-info-row-" + index + "").remove();
    trustedCA.reloadDoc();
}

trustedCA.reloadDoc = function () {
    let allElements = document.querySelectorAll('#trca-reload-doc');
    allElements.forEach((element) => {
        if (typeof element.onclick === 'function') {
            element.onclick();
        }
    });
}

trustedCA.verify = function (ids) {
    if (location.protocol === 'http:') {
        alert(HTTP_WARNING);
        return;
    }
    $.ajax({
        url: AJAX_CONTROLLER + '?command=createTransaction',
        type: 'post',
        data: {id: ids, method: "verify"},
        success: function (d) {
            if (d.success) {
                let url = "cryptoarm://verify/" + AJAX_CONTROLLER + '?command=JSON&accessToken=' + d.uuid;
                window.location = url;
            } else {
                trustedCA.show_messages(d);
            }
        },
        error: function (e) {
            console.error(e);
            try {
                var d = JSON.parse(e.responseText);
                if (d.success === false) {
                    console.log(d);
                }
            } catch (e) {
                console.error(e);
            }
        }
    });
};


trustedCA.show_messages = function (response) {
    if (response.WFDocs) {
        message = IN_WF;
        response.docsInWF.forEach(function (elem) {
            message += '\n' + elem.id + ': ' + elem.name;
        });
        alert(message);
    }
    if (response.HaveAccess){
        alert(HAVE_ACCESS);
    }
    if (response.IsOwner) {
        alert(SHARE_IS_OWNER);
    }
    if (response.noIds) {
        alert(ERROR_NO_IDS);
    }
    if (response.noAuth) {
        alert(ERROR_NO_AUTH);
    }
    if (response.noUser) {
        alert(SHARE_NO_USER_1 + response.noUser + SHARE_NO_USER_2);
    }
    if (response.noSendMail) {
        alert(SEND_MAIL_FAILURE);
    }
    if (response.docsFileNotFound && response.docsFileNotFound.length) {
        message = ERROR_FILE_NOT_FOUND;
        response.docsFileNotFound.forEach(function (elem) {
            message += '\n' + elem.id + ': ' + elem.filename;
        });
        alert(message);
    }
    if (response.docsNotFound && response.docsNotFound.length) {
        message = ERROR_DOC_NOT_FOUND;
        response.docsNotFound.forEach(function (elem) {
            message += '\n' + elem;
        });
        alert(message);
    }
    if (response.docsBlocked && response.docsBlocked.length) {
        message = ERROR_DOC_BLOCKED;
        response.docsBlocked.forEach(function (elem) {
            message += '\n' + elem.id + ': ' + elem.filename;
        });
        alert(message);
    }
    if (response.docsRoleSigned && response.docsRoleSigned.length) {
        message = ERROR_DOC_ROLE_SIGNED;
        response.docsRoleSigned.forEach(function (elem) {
            message += '\n' + elem.id + ': ' + elem.filename;
        });
        alert(message);
    }
    if (response.docsUnsigned && response.docsUnsigned.length) {
        message = ERROR_DOC_UNSIGNED;
        response.docsUnsigned.forEach(function (elem) {
            message += '\n' + elem.id + ': ' + elem.filename;
        });
        alert(message);
    }
    if (response.docsNoAccess && response.docsNoAccess.length) {
        message = ERROR_DOC_NO_ACCESS;
        response.docsNoAccess.forEach(function (elem) {
            message += '\n' + elem;
        });
        alert(message);
    }
    if (response.docsWrongSignType && response.docsWrongSignType.length) {
        message = ERROR_DOC_WRONG_SIGN_TYPE;
        response.docsWrongSignType.forEach(function (elem) {
            message += '\n' + elem;
        });
        alert(message);
    }
};


trustedCA.unblock = function (ids, onSuccess = null, onFailure = null) {
    trustedCA.ajax('unblock', {ids}, onSuccess, onFailure);
};


trustedCA.remove = function (ids, force = false, onSuccess = null, onFailure = null) {
    if (ids.length != 1) {
        message = REMOVE_ACTION_CONFIRM_MANY;
    } else {
        message = REMOVE_ACTION_CONFIRM
    }
    if (force ? true : confirm(message)) {
        trustedCA.ajax('remove', {ids}, onSuccess, onFailure);
    }
};


trustedCA.download = function (ids, filename) {
    // revoke method in future
    let onSuccess = (d) => {
            window.location.href = AJAX_CONTROLLER + '?command=download&ids=' + JSON.stringify(ids) + '&force=true';
    };
    let onFailure = (d) => {
            window.location.href = AJAX_CONTROLLER + '?command=download&ids=' + JSON.stringify(ids) + '&force=true';
    };

    trustedCA.ajax('download', {ids, filename}, onSuccess, onFailure);
};


trustedCA.sendEmail = function (ids, event, arEventFields, messageId) {
    let onSuccess = (d) => {
        alert(SEND_MAIL_SUCCESS);
    };
    trustedCA.ajax('sendEmail', {ids, event, arEventFields, messageId}, onSuccess);
};


trustedCA.protocol = function (id) {
    trustedCA.ajax(
        'check',
        {ids: [id], level: 'SHARE_READ', allowBlocked: true},
        () => {
            window.location.replace(AJAX_CONTROLLER + '?command=protocol&id=' + id)
        }
    );
}


trustedCA.promptEmail = function (message) {
    function validateEmail(email) {
        let re = /\S+@\S+\.\S+/;
        return re.test(email);
    }

    function checkEmails(emailArr) {
        for (var i = 0; i < emailArr.length; i++) {
            emailArr[i] = emailArr[i].trim();
            if (!validateEmail(emailArr[i])) {
                return false;
            }
        }
        return true;
    }
    do {
        var emailAddress = prompt(message, '');
        var emailArr = emailAddress.split(/,|;/);
        var validatedEmail = checkEmails(emailArr);
    } while (emailAddress && validatedEmail !== true);
    return emailArr;
};


trustedCA.promptAndSendEmail = function (ids, event, arEventFields, message_id) {
    let email = trustedCA.promptEmail(SEND_MAIL_TO_PROMPT);
    for (var i = 0; i < email.length; i++) {
        arEventFields.EMAIL = email[i];
        if (email[i]) {
            trustedCA.sendEmail(ids, event, arEventFields, message_id);
        }
    }
};


trustedCA.share = function (ids, email, level = 'SHARE_READ') {
    let onSuccess = (d) => {
        alert(SHARE_SUCCESS_1 + email + SHARE_SUCCESS_2);
        trustedCA.reloadDoc();
    };
    trustedCA.ajax('share', {ids, email, level}, onSuccess);
};


trustedCA.requireToSign = function (ids, email) {
    let onSuccess = (d) => {
        alert(REQUIRE_SUCCESS_1 + email + REQUIRE_SUCCESS_2);
        trustedCA.reloadDoc();
    };
    let onFailure = (d) => {
        alert(SEND_MAIL_FAILURE);
    };
    trustedCA.ajax('requireToSign', {ids, email}, onSuccess, onFailure);
};


trustedCA.promptAndShare = function (ids, level = 'SHARE_READ', isModalInfo = false) {
    let email = trustedCA.promptEmail(ACT_SHARE);
    if (email) {
        trustedCA.share(ids, email, level);
    }
    if (isModalInfo) {
        if ($('#trca-modal-info-name').length) {
            trustedCA.getInfoForModalWindow(id).then(
                docInfo => {
                    if (!docInfo.success) {
                        console.log("Something wrong");
                        return false;
                    }
                    let docname = docInfo.data.docname;
                    let sharedstatus = docInfo.data.sharedstatus;
                    let currentuseraccess = docInfo.data.currentuseraccess;
                    $('#trca-modal-info-window').hide();
                    $('#trca-modal-overlay').hide();
                    trustedCA.showInfoModalWindow(ids, docname, sharedstatus, currentuseraccess);
                }
            )
        }
    }
};


trustedCA.promptAndRequireToSign = function (ids) {
    let email = trustedCA.promptEmail(ACT_SHARE);
    if (email) {
        trustedCA.requireToSign(ids, email);
    }
};


trustedCA.reloadGrid = function (gridId) {
    var reloadParams = {apply_filter: 'Y', clear_nav: 'Y'};
    var gridObject = BX.Main.gridManager.getById(gridId);

    if (gridObject.hasOwnProperty('instance')) {
        gridObject.instance.reloadTable('POST', reloadParams);
    }
};
trustedCA.checkName = function (file, onSuccess = null, onFailure = null){
    var new_name = file.name.replace(/[^\dA-Za-zА-Яа-яЁё\.\ \,\-\_\(\)]/,'');
    if (new_name !== file.name){
        trustedCA.showPopupMessage(DOWNLOAD_FILE_ERROR_NAME, 'highlight_off', 'negative');
        if (typeof onFailure === 'function') {
            onFailure();
        }
    }else{
            if (typeof onSuccess === 'function') {
            onSuccess();
        }
    }
};

trustedCA.checkFileSize = function (file, maxSize, onSuccess = null, onFailure = null) {
    if (!file.size){
        trustedCA.showPopupMessage(DOWNLOAD_FILE_ZERO_SIZE, 'highlight_off', 'negative');
        if (typeof onFailure === 'function') {
            onFailure();
        }
    } else if (file.size/1024/1024  >= maxSize){
        trustedCA.showPopupMessage(DOWNLOAD_FILE_1 + maxSize + DOWNLOAD_FILE_2, 'highlight_off', 'negative');
        if (typeof onFailure === 'function') {
            onFailure();
        }
    } else {
        if (typeof onSuccess === 'function') {
            onSuccess();
        }
    }
};

trustedCA.checkAccessFile = function (file, onSuccess, onFailure) {
    let fr = new FileReader();
    fr.onloadend = () => {
        if (fr.error) {
            let message = NO_ACCESS_FILE + String.fromCharCode(171) + file.name + String.fromCharCode(187);
            trustedCA.showPopupMessage(message, 'highlight_off', 'negative');
            if (typeof onFailure === 'function') {
                onFailure();
            }
        } else {
            if (typeof onSuccess === 'function') {
                onSuccess();
            }
        }
    };
    fr.readAsDataURL(file);
};

trustedCA.unshare = function (docIds, userId, force = false, onSuccess, onFailure) {
    message =  userId ? UNSHARE_FROM_MODAL_CONFIRM : UNSHARE_CONFIRM;
    if (force ? true : confirm(message)) {
        trustedCA.ajax('unshare', {docIds, userId}, onSuccess, onFailure);
    }
};

trustedCA.removeForm = function (ids, onSuccess = null, onFailure = null) {
    if (ids.length != 1) {
        message = REMOVE_FORM_ACTION_CONFIRM_MANY;
    } else {
        message = REMOVE_FORM_ACTION_CONFIRM
    }
    if (confirm(message)) {
        trustedCA.ajax('removeForm', {ids}, onSuccess, onFailure);
    }
};

trustedCA.showPopupMessage = function (message, icon = 'info_outline', style = '', interval = 5000) {
    let oldPopupMessageDiv = document.querySelectorAll('.trca-popup-window');
    oldPopupMessageDiv.forEach((element) => {
        document.body.removeChild(element)
        clearInterval(intervalPopup);
    });
    trustedCA.popupMessageDiv = document.createElement("div");
    trustedCA.popupMessageDiv.className = "trca-popup-window";
    popupMessage = `
        <div class="trca-popup-content ${style}">
            <div class="trca-popup-icon ${style}">
                <div class="material-icons">
                    ${icon}
                </div>
            </div>
            <div class="trca-popup-message">
               ${message}
            </div>
            <div class="trca-popup-close" onclick="document.body.removeChild(trustedCA.popupMessageDiv); clearInterval(intervalPopup);">
                <div class="material-icons">
                    close
                </div>
            </div>
        </div>
    `;
    trustedCA.popupMessageDiv.innerHTML = popupMessage;
    document.body.appendChild(trustedCA.popupMessageDiv);
    intervalPopup = setTimeout(() => {
        document.body.removeChild(trustedCA.popupMessageDiv)
    }, interval);
};
