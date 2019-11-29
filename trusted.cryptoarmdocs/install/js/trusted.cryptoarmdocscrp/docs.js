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
    LOST_DOC = BX.message('TR_CA_DOCS_ALERT_LOST_DOC');
    ERROR_NO_AUTH = BX.message('TR_CA_DOCS_ERROR_NO_AUTH');
    ERROR_NO_IDS = BX.message('TR_CA_DOCS_ERROR_NO_IDS');
    ERROR_FILE_NOT_FOUND = BX.message('TR_CA_DOCS_ERROR_FILE_NOT_FOUND');
    ERROR_DOC_NOT_FOUND = BX.message('TR_CA_DOCS_ERROR_DOC_NOT_FOUND');
    ERROR_DOC_BLOCKED = BX.message('TR_CA_DOCS_ERROR_DOC_BLOCKED');
    ERROR_DOC_ROLE_SIGNED = BX.message('TR_CA_DOCS_ERROR_DOC_ROLE_SIGNED');
    ERROR_DOC_UNSIGNED = BX.message('TR_CA_DOCS_ERROR_DOC_UNSIGNED');
    ERROR_DOC_NO_ACCESS = BX.message('TR_CA_DOCS_ERROR_DOC_NO_ACCESS');
    SEND_MAIL_SUCCESS = BX.message('TR_CA_DOCS_ACT_SEND_MAIL_SUCCESS');
    SEND_MAIL_FAILURE = BX.message('TR_CA_DOCS_ACT_SEND_MAIL_FAILURE');
    SEND_MAIL_TO_PROMPT = BX.message('TR_CA_DOCS_ACT_SEND_MAIL_TO_PROMPT');
    SHARE_SUCCESS_1 = BX.message('TR_CA_DOCS_ACT_SHARE_SUCCESS_1');
    SHARE_SUCCESS_2 = BX.message('TR_CA_DOCS_ACT_SHARE_SUCCESS_2');
    REQUIRE_SUCCESS_1 = BX.message('TR_CA_DOCS_ACT_REQUIRE_SUCCESS_1');
    REQUIRE_SUCCESS_2 = BX.message('TR_CA_DOCS_ACT_REQUIRE_SUCCESS_2');
    SHARE_NO_USER_1 = BX.message('TR_CA_DOCS_ACT_SHARE_NO_USER_1');
    SHARE_NO_USER_2 = BX.message('TR_CA_DOCS_ACT_SHARE_NO_USER_2');
    DOWNLOAD_FILE_1 = BX.message("TR_CA_DOCS_ACT_DOWNLOAD_FILE_1");
    DOWNLOAD_FILE_2 = BX.message("TR_CA_DOCS_ACT_DOWNLOAD_FILE_2");
    DOWNLOAD_FILE_ZERO_SIZE = BX.message("TR_CA_DOCS_ACT_DOWNLOAD_FILE_ZERO_SIZE");
    MODAL_MESSAGE_1 = BX.message('TR_CA_DOCS_MODAL_MESSAGE_1');
    MODAL_MESSAGE_2 = BX.message('TR_CA_DOCS_MODAL_MESSAGE_2');
    MODAL_MESSAGE_MANY_1 = BX.message('TR_CA_DOCS_MODAL_MESSAGE_MANY_1');
    MODAL_MESSAGE_MANY_2 = BX.message('TR_CA_DOCS_MODAL_MESSAGE_MANY_2');
    MODAL_CANCEL = BX.message('TR_CA_DOCS_MODAL_CANCEL');
    ACT_SHARE = BX.message('TR_CA_DOCS_ACT_SHARE');
    UNSHARE_CONFIRM = BX.message('TR_CA_DOCS_UNSHARE_CONFIRM');
    NO_ACCESS_FILE = BX.message('TR_CA_DOCS_NO_ACCESS_FILE');
    CLOSE_WINDOW = BX.message('TR_CA_DOCS_CLOSE_INFO_WINDOW');
    SHARE_DOC = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_SHARE');
    SIGN_DOC = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_SIGN');
    DOWNLOAD_DOC = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_DOWNLOAD');
    DOWNLOAD_PROTOCOL = BX.message('TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_PROTOCOL');
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
        <div class="trca-modal-header" id="trca-modal-header"></div>
        <div class="trca-modal-info-content">
            <div class="trca-modal-info-content-left">
                <div class="trca-modal-content-message" id="trca-modal-content-message"></div>
            </div>
            <div class="trca-modal-info-content-right">
                <div class="trca-modal-info-content-button" id="trca-modal-info-content-button-share">
                    <i class="material-icons">share</i>
                    <div class="trca-modal-content-info-message" id="trca-modal-content-info-message-share"></div>
                </div>
                <div class="trca-modal-info-content-button" id="trca-modal-info-content-button-download">
                    <i class="material-icons">save_alt</i>
                    <div class="trca-modal-content-info-message" id="trca-modal-content-info-message-download"></div>
                </div>
                <div class="trca-modal-info-content-button" id="trca-modal-info-content-button-sign">
                    <i class="material-icons">edit</i>
                    <div class="trca-modal-content-info-message" id="trca-modal-content-info-message-sign"></div>
                </div>
                <div class="trca-modal-info-content-button" id="trca-modal-info-content-button-protocol">
                    <i class="material-icons">description</i>
                    <div class="trca-modal-content-info-message" id="trca-modal-content-info-message-protocol"></div>
                </div>
                <div class="trca-modal-info-close" id="trca-modal-info-close"></div>
            </div>
    </div>
`;
trustedCA.modalDiv = document.createElement("div");

// Fixes errors after authorization
if (BX.message('TR_CA_DOCS_AJAX_CONTROLLER')) {
    trustedCA.initVar();
} else {
    setTimeout(function () {
        trustedCA.initVar()
    }, 100);
}

// ====================================================
// === Establish socket connection, assign handlers ===
// ====================================================
trustedCA.socketInit = function () {
    if (location.protocol === 'https:') {
        socket = io('https://localhost:4040');
        socket.on('connect', () => {
            console.log('Event: connect');
        });
        socket.on('disconnect', data => {
            console.log('Event: disconnect, reason: ', data);
        });
        socket.on('verified', data => {
            console.log('Event: verified', data);
        });
        socket.on('signed', data => {
            console.log('Event: signed, data: ', data);
        });
        socket.on('uploaded', data => {
            console.log('Event: uploaded, data: ', data);
        });
        socket.on('cancelled', data => {
            console.log('Event: cancelled', data);
            trustedCA.unblock([data.id]);
        });
    }
};
trustedCA.socketInit();

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
    let iOS = /iphone/i.test(navigator.userAgent);
    let android = /android/i.test(navigator.userAgent);
    if (!iOS && !android) {
        if (!socket.connected) {
            alert(NO_CLIENT);
            return;
        }
    }
    $.ajax({
        url: AJAX_CONTROLLER + '?command=sign',
        type: 'post',
        data: {id: ids, extra: extra},
        success: function (d) {
            // mobile CryptoArm support START
            if (iOS || android) {
                let filenameArr = [];
                let idArr = [];
                docs = JSON.parse(d.docsOk);
                docs.forEach(function (elem) {
                    filenameArr.push(elem.name);
                    idArr.push(elem.id);
                });
                extra.token = d.token;
                let url = "cryptoarmgost://sign/?ids=" + idArr + "&extra=" + JSON.stringify(extra) +
                    "&url=" + JSON.parse(d.docsOk)[0].url + "&filename=" + filenameArr + "&href=" +
                    window.location.href + "&uploadurl=" + AJAX_CONTROLLER + "&command=upload&license=" + d.license + "&browser=";
                if (/CriOS/i.test(navigator.userAgent)) {
                    window.location = url + "chrome";
                } else {
                    window.location = url + "default";
                }
                ids = [];
                docs.forEach(function (elem) {
                    ids.push(elem.id);
                });
                trustedCA.block(ids);
                setTimeout(() => location.reload(), 1000);
                // mobile CryptoArm support END
            } else {
                if (d.success) {
                    if (extra === null) {
                        extra = {};
                    }
                    extra.token = d.token;
                    docs = JSON.parse(d.docsOk);
                    req = {};
                    req.jsonrpc = '2.0';
                    req.method = 'sign';
                    req.params = {};
                    req.params.license = d.license;
                    req.params.token = '';
                    req.params.files = docs;
                    req.params.extra = extra;
                    req.params.uploader = AJAX_CONTROLLER + '?command=upload';
                    socket.emit('sign', req);
                    ids = [];
                    docs.forEach(function (elem) {
                        ids.push(elem.id);
                    });
                    trustedCA.showModalWindow(ids);
                    var interval = setInterval(() => trustedCA.blockCheck(d.token, interval, onSuccess), 2000);
                } else {
                    if (typeof onFailure === 'function') {
                        onFailure(d);
                    }
                }
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
    // Fixes random socket disconnects
    trustedCA.socketInit();
};

trustedCA.blockCheck = function (token, interval, onSuccess) {
    let onFailure = (e) => {
        clearInterval(interval);
        interval = 0;
        document.body.removeChild(trustedCA.modalDiv)
        trustedCA.reloadDoc();
        if (typeof onSuccess === 'function') {
            onSuccess();
        }
    }
    trustedCA.ajax('blockCheck', {blockToken: token}, () => {
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

trustedCA.showInfoModalWindow = function (ids, docname) {
    trustedCA.modalDiv.className = "trca-modal";
    trustedCA.modalDiv.innerHTML = trustedCA.modalWindowInfo;
    document.body.appendChild(trustedCA.modalDiv);
    $('#trca-modal-info-content-button-share').attr('onclick', "trustedCA.promptAndShare([" + ids + "], 'SHARE_SIGN')");
    $('#trca-modal-content-info-message-share').text(SHARE_DOC);
    $('#trca-modal-info-content-button-download').attr('onclick', "trustedCA.download([" + ids + "], true)");
    $('#trca-modal-content-info-message-download').text(DOWNLOAD_DOC);
    $('#trca-modal-info-content-button-sign').attr('onclick', "trustedCA.sign([" + ids + "])");
    $('#trca-modal-content-info-message-sign').text(SIGN_DOC);
    $('#trca-modal-info-content-button-protocol').attr('onclick', "trustedCA.promptAndShare([" + ids + "], 'SHARE_SIGN')");
    $('#trca-modal-content-info-message-protocol').text(DOWNLOAD_PROTOCOL);
    $('#trca-modal-info-close').attr('onclick', "{$('#trca-modal-info-window').hide(); $('#trca-modal-overlay').hide()}");
    $('#trca-modal-header').text(docname);
    $('#trca-modal-content-message').text(docname);
    $('#trca-modal-info-close').text(CLOSE_WINDOW);
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
    let iOS = /iphone/i.test(navigator.userAgent);
    let android = /android/i.test(navigator.userAgent);
    if (!iOS && !android) {
        if (!socket.connected) {
            alert(NO_CLIENT);
            return;
        }
    }
    $.ajax({
        url: AJAX_CONTROLLER + '?command=verify',
        type: 'post',
        data: {id: ids},
        success: function (d) {
            // mobile CryptoArm support START
            if (iOS || android) {
                let url = "cryptoarmgost://verify/?url=" + JSON.parse(d.docsOk)[0].url + "&command=verify";
                if (/CriOS/i.test(navigator.userAgent || '')) {
                    window.location = url + "&browser=chrome";
                } else {
                    window.location = url + "&browser=default";
                }
                // mobile CryptoArm support END
            } else {
                if (d.success) {
                    docs = JSON.parse(d.docsOk);
                    req = {};
                    req.jsonrpc = '2.0';
                    req.method = 'verify';
                    req.params = {};
                    req.params.token = '';
                    req.params.files = docs;
                    if (socket.connected) {
                        socket.emit('verify', req);
                    } else {
                        alert(NO_CLIENT);
                    }
                } else {
                    console.log(d);
                }
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
    // Fixes random socket disconnects
    trustedCA.socketInit();
};


trustedCA.show_messages = function (response) {
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
    let onSuccess = (d) => {
        if (d.success === true) {
            if (ids.length === 1) {
                window.location.href = AJAX_CONTROLLER + '?command=content&id=' + ids[0];
            } else {
                window.location.href = AJAX_CONTROLLER + '?command=content&file=' + d.content;
            }
        }
    };
    trustedCA.ajax('download', {ids, filename}, onSuccess);
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

    do {
        var emailAddress = prompt(message, '');
        var validatedEmail = validateEmail(emailAddress);
    } while (emailAddress && validatedEmail !== true);
    return emailAddress;
};


trustedCA.promptAndSendEmail = function (ids, event, arEventFields, message_id) {
    let email = trustedCA.promptEmail(SEND_MAIL_TO_PROMPT);
    arEventFields.EMAIL = email;
    if (email) {
        trustedCA.sendEmail(ids, event, arEventFields, message_id);
    }
};


trustedCA.share = function (ids, email, level = 'SHARE_READ') {
    let onSuccess = (d) => {
        alert(SHARE_SUCCESS_1 + email + SHARE_SUCCESS_2);
    };
    trustedCA.ajax('share', {ids, email, level}, onSuccess);
};


trustedCA.requireToSign = function (ids, email) {
    let onSuccess = (d) => {
        alert(REQUIRE_SUCCESS_1 + email + REQUIRE_SUCCESS_2);
        trustedCA.reloadDoc();
    };
    trustedCA.ajax('requireToSign', {ids, email}, onSuccess);
};


trustedCA.promptAndShare = function (ids, level = 'SHARE_READ') {
    let email = trustedCA.promptEmail(ACT_SHARE);
    if (email) {
        trustedCA.share(ids, email, level);
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

trustedCA.unshare = function (ids, force = false, onSuccess, onFailure) {
    message = UNSHARE_CONFIRM;
    if (force ? true : confirm(message)) {
        trustedCA.ajax('unshare', {ids}, onSuccess, onFailure);
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
