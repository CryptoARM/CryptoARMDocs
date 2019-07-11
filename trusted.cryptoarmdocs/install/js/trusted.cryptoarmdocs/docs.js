if (!trustedCA) {
    var trustedCA = {};
}

// ===============================
// === Get js library messages ===
// ===============================
trustedCA.initVar = function(){
    AJAX_CONTROLLER = window.location.protocol + '//' + window.location.host + BX.message('TR_CA_DOCS_AJAX_CONTROLLER');
    NO_CLIENT = BX.message('TR_CA_DOCS_ALERT_NO_CLIENT');
    HTTP_WARNING = BX.message('TR_CA_DOCS_ALERT_HTTP_WARNING');
    REMOVE_ACTION_CONFIRM = BX.message('TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM');
    REMOVE_ACTION_CONFIRM_MANY = BX.message('TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM_MANY');
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
    SHARE_NO_USER_1 = BX.message('TR_CA_DOCS_ACT_SHARE_NO_USER_1');
    SHARE_NO_USER_2 = BX.message('TR_CA_DOCS_ACT_SHARE_NO_USER_2');
    DOWNLOAD_FILE_1 = BX.message("TR_CA_DOCS_ACT_DOWNLOAD_FILE_1");
    DOWNLOAD_FILE_2 = BX.message("TR_CA_DOCS_ACT_DOWNLOAD_FILE_2");
    ACT_SHARE = BX.message('TR_CA_DOCS_ACT_SHARE');
};

// Fixes errors after authorization
if (BX.message('TR_CA_DOCS_AJAX_CONTROLLER')) {
    trustedCA.initVar();
} else {
    setTimeout(function() {trustedCA.initVar()}, 100);
}

// ====================================================
// === Establish socket connection, assign handlers ===
// ====================================================
trustedCA.socketInit = function(){
    if (location.protocol === 'https:') {
        socket = io('https://localhost:4040');
        socket.on('connect', () => { console.log('Event: connect'); });
        socket.on('disconnect', data => { console.log('Event: disconnect, reason: ', data); });
        socket.on('verified', data => { console.log('Event: verified', data); });
        socket.on('signed', data => { console.log('Event: signed, data: ', data); });
        socket.on('uploaded', data => {
            console.log('Event: uploaded, data: ', data);
            // Check to see if page defined it's own handler
            if (typeof trustedCAUploadHandler === 'function') {
                trustedCAUploadHandler(data);
            } else {
                console.log('upload detected, handler not defined');
            }
        });
        socket.on('cancelled', data => {
            console.log('Event: cancelled', data);
            if (typeof trustedCACancelHandler === 'function') {
                trustedCA.unblock([data.id], (data) => trustedCACancelHandler(data));
            } else {
                trustedCA.unblock([data.id], () => console.log('cancel detected, handler not defined'));
            }
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
    // Fixes random socket disconnects
    trustedCA.socketInit();
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
                let url = "cryptoarmgost://sign/?ids=" + idArr + "&extra=" + (extra === null ? "null" : extra.role) +
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
                    if (typeof onSuccess === 'function') {
                        onSuccess(d);
                    }
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
};


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
                let url = "cryptoarmgost://verify/?url=" + JSON.parse(d.docs)[0].url + "&command=verify";
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
    if (response.noSendMail){
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
    let onSuccess = (d) => { alert(SEND_MAIL_SUCCESS); };
    trustedCA.ajax('sendEmail', {ids, event, arEventFields, messageId}, onSuccess);
};


trustedCA.protocol = function (id) {
    trustedCA.ajax(
        'check',
        {ids: [id], level: 'SHARE_READ', allowBlocked: true},
        () => { window.location.replace(AJAX_CONTROLLER + '?command=protocol&id=' + id) }
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
    let onSuccess = (d) => { alert(SHARE_SUCCESS_1 + email + SHARE_SUCCESS_2); };
    trustedCA.ajax('share', {ids, email, level}, onSuccess);
};


trustedCA.promptAndShare = function (ids, level = 'SHARE_READ') {
    let email = trustedCA.promptEmail(ACT_SHARE);
    if (email) {
        trustedCA.share(ids, email, level);
    }
};


trustedCA.reloadGrid = function (gridId) {
    var reloadParams = { apply_filter: 'Y', clear_nav: 'Y' };
    var gridObject = BX.Main.gridManager.getById(gridId);

    if (gridObject.hasOwnProperty('instance')){
        gridObject.instance.reloadTable('POST', reloadParams);
    }
};


trustedCA.checkFileSize = function (file, maxSize, onSuccess = null, onFailure = null){
    if (file.size/1024/1024  >= maxSize){
        alert(DOWNLOAD_FILE_1 + maxSize + DOWNLOAD_FILE_2);
        if (typeof onFailure === 'function') {
            onFailure();
        }
    } else {
        if (typeof onSuccess === 'function') {
            onSuccess();
        }
    }
};

