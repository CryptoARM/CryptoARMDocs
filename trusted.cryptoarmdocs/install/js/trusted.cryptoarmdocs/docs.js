var AJAX_CONTROLLER = BX.message('TR_CA_DOCS_AJAX_CONTROLLER');
var NO_CLIENT = BX.message('TR_CA_DOCS_ALERT_NO_CLIENT');
var HTTP_WARNING = BX.message('TR_CA_DOCS_ALERT_HTTP_WARNING');
var REMOVE_ACTION_CONFIRM = BX.message('TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM');
var LOST_DOC_REMOVE_CONFIRM_PRE = BX.message('TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE');
var LOST_DOC_REMOVE_CONFIRM_POST = BX.message('TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_POST');
var LOST_DOC = BX.message('TR_CA_DOCS_ALERT_LOST_DOC');
var ERROR_FILE_NOT_FOUND = BX.message('TR_CA_DOCS_ERROR_FILE_NOT_FOUND');
var ERROR_DOC_NOT_FOUND = BX.message('TR_CA_DOCS_ERROR_DOC_NOT_FOUND');
var ERROR_DOC_BLOCKED = BX.message('TR_CA_DOCS_ERROR_DOC_BLOCKED');
var ERROR_DOC_ROLE_SIGNED = BX.message('TR_CA_DOCS_ERROR_DOC_ROLE_SIGNED');
var ERROR_DOC_NO_ACCESS = BX.message('TR_CA_DOCS_ERROR_DOC_NO_ACCESS');

if (location.protocol === 'https:') {
    var socket = io('https://localhost:4040');
}

socket.on('connect', function () {
    console.log('Event: connect');
});

socket.on('disconnect', function (data) {
    console.log('Event: disconnect, reason: ', data);
});

socket.on('verified', function (data) {
    console.log('Event: verified', data);
});

socket.on('signed', function (data) {
    console.log('Event: signed, data: ', data);
});

socket.on('uploaded', function (data) {
    console.log('Event: uploaded, data: ', data);
    location.reload()
});

socket.on('cancelled', function (data) {
    console.log('Event: cancelled', data);
});

function sign(ids, extra = null) {
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
                docs = JSON.parse(d.docsToSign);
                docs.forEach(function (elem) {
                    filenameArr.push(elem.name);
                    idArr.push(elem.id);
                });
                let url = "cryptoarmgost://sign/?ids=" + idArr + "&extra=" + (extra === null ? "null" : extra.role)
                    + "&url=" + JSON.parse(d.docsToSign)[0].url + "&filename=" + filenameArr + "&href="
                    + window.location.href + "&uploadurl=" + AJAX_CONTROLLER + "&command=upload&license=" + d.license + "&browser=";
                if (/CriOS/i.test(navigator.userAgent)) {
                    window.location = url + "chrome";
                } else {
                    window.location = url + "default";
                }
                ids = [];
                docs.forEach(function (elem) {
                    ids.push(elem.id);
                });
                block(ids);
                setTimeout(() => location.reload(), 1000);
                // mobile CryptoArm support END
            } else {
                if (d.success) {
                    docs = JSON.parse(d.docsToSign);
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
                    block(ids, function () {
                        location.reload();
                    });
                } else {
                    console.log(d);
                }
                show_messages(d);
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
}

function show_messages(response) {
    if (response.docsFileNotFound) {
        message = ERROR_FILE_NOT_FOUND;
        response.docsFileNotFound.forEach(function (elem) {
            message += '\n' + elem.id + ': ' + elem.filename;
        });
        alert(message);
    }
    if (response.docsNotFound) {
        message = ERROR_DOC_NOT_FOUND;
        response.docsNotFound.forEach(function (elem) {
            message += '\n' + elem;
        });
        alert(message);
    }
    if (response.docsBlocked) {
        message = ERROR_DOC_BLOCKED;
        response.docsBlocked.forEach(function (elem) {
            message += '\n' + elem.id + ': ' + elem.filename;
        });
        alert(message);
    }
    if (response.docsRoleSigned) {
        message = ERROR_DOC_ROLE_SIGNED;
        response.docsRoleSigned.forEach(function (elem) {
            message += '\n' + elem.id + ': ' + elem.filename;
        });
        alert(message);
    }
    if (response.docsNoAccess) {
        message = ERROR_DOC_NO_ACCESS;
        response.docsNoAccess.forEach(function (elem) {
            message += '\n' + elem;
        });
        alert(message);
    }
}

function verify(ids) {
    if (location.protocol === 'http:') {
        alert(HTTP_WARNING);
        return;
    }
    $.ajax({
        url: AJAX_CONTROLLER + '?command=verify',
        type: 'post',
        data: {id: ids},
        success: function (d) {
            // mobile CryptoArm support START
            if (/iphone/i.test(navigator.userAgent)) {
                let url = "cryptoarmgost://verify/?url=" + JSON.parse(d.docs)[0].url + "&command=verify";
                if (/CriOS/i.test(navigator.userAgent || '')) {
                    window.location = url + "&browser=chrome";
                } else {
                    window.location = url + "&browser=default";
                }
            // mobile CryptoArm support END
            } else {
                if (d.success) {
                    docs = JSON.parse(d.docs);
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
}

function block(ids, cb = null) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=block',
        type: 'post',
        data: {id: ids},
        success: function (d) {
            if (d.success === false) {
                console.log(d);
            }
            if (cb) {
                cb(d);
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
}

function unblock(ids) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=unblock',
        type: 'post',
        data: {id: ids},
        success: function (d) {
            if (d.success === false) {
                console.log(d);
                alert(d.message);
            } else {
                location.reload();
            }
        },
        error: function (e) {
            console.error(e);
            try {
                var d = JSON.parse(e.responseText);
                if (d.success === false) {
                    alert(d.message);
                }
            } catch (e) {
                console.error(e);
            }
        }
    });
}

function remove(ids, force = false, message = REMOVE_ACTION_CONFIRM) {
    if (force ? true : confirm(message)) {
        $.ajax({
            url: AJAX_CONTROLLER + '?command=remove',
            type: 'post',
            data: {id: ids},
            success: function (d) {
                if (d.success === false) {
                    console.log(d);
                    alert(d.message);
                } else {
                    location.reload();
                }
            },
            error: function (e) {
                console.error(e);
                try {
                    var d = JSON.parse(e.responseText);
                    if (d.success === false) {
                        alert(d.message);
                    }
                } catch (e) {
                    console.error(e);
                }
            }
        });
    }
}

function download(ids, del = false, archiveName = null) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=download',
        type: 'post',
        data: {ids: ids, archiveName: archiveName},
        success: function(d) {
            console.log(d);
            show_messages(d);
            if (d.success === true) {
                if (ids.length === 1) {
                    window.location.href = AJAX_CONTROLLER + '?command=content&id=' + ids[0];
                } else {
                    window.location.href = AJAX_CONTROLLER + '?command=content&file=' + d.content;
                }
            } else {
                if (del) {
                    var removeMessage = LOST_DOC_REMOVE_CONFIRM_PRE;
                    removeMessage += '\n' + d.filename + '\n';
                    removeMessage += LOST_DOC_REMOVE_CONFIRM_POST;
                    if (confirm(removeMessage)) {
                        remove({ids}, removeMessage);
                    }
                }
            }
        },
        error: function(e) {
            console.log(e);
        }
    });
}

function view(id) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=view',
        type: 'post',
        data: {id: id},
        success: function (d) {
            console.log(d);
        },
        error: function (e) {
            console.log(e);
        }
    });
}

function sendEmail(docsList, event, arEventFields, message_id) {
    BX.ajax({
            url: AJAX_CONTROLLER + '?command=sendEmail',
            data: {docsList: docsList, event: event, arEventFields: arEventFields, message_id: message_id},
            method: 'post',
            onsuccess: function (d) {
                d = JSON.parse(d);
                if (d.success) {
                    alert(BX.message('TR_CA_DOCS_ACT_SEND_MAIL_SUCCESS'));
                } else {
                    console.log(d);
                    alert(BX.message('TR_CA_DOCS_ACT_SEND_MAIL_FAILURE'));
                }
            },
        onfailure: function (e) {
            console.log(e);
        }
    }
    );
}

function validateEmail(email) {
    let re = /\S+@\S+\.\S+/;
    return re.test(email);
}

function promptEmail(message) {
    do {
        var emailAddress = prompt(message, '');
        var validatedEmail = validateEmail(emailAddress);
    } while (emailAddress && validatedEmail !== true);
    return emailAddress;
}

function promptAndSendEmail(docsList, event, arEventFields, message_id) {
    let email = promptEmail(BX.message('TR_CA_DOCS_ACT_SEND_MAIL_TO_PROMPT'));
    arEventFields["EMAIL"] = email;
    if (email) {
        sendEmail(docsList, event, arEventFields, message_id);
    }
}

function share(ids, email, level = 'SHARE_READ') {
    BX.ajax({
        url: AJAX_CONTROLLER + '?command=share',
        data: {ids: ids, email: email, level: level},
        method: 'post',
        onsuccess: function (d) {
            d = JSON.parse(d);
            if (d.success) {
                alert(BX.message('TR_CA_DOCS_ACT_SHARE_SUCCESS_1') + email + BX.message('TR_CA_DOCS_ACT_SHARE_SUCCESS_2'));
            } else {
                console.log(d.message);
                if (d.message == 'User not found') {
                    alert(BX.message('TR_CA_DOCS_ACT_SHARE_NO_USER_1') + email + BX.message('TR_CA_DOCS_ACT_SHARE_NO_USER_2'));
                }
            }
        },
        onfailure: function (e) {
            console.log(e);
        }
    }
    );
}

function promptAndShare(ids, level = 'SHARE_READ') {
    let email = promptEmail(BX.message('TR_CA_DOCS_ACT_SHARE'));
    if (email) {
        share(ids, email, level);
    }
}

