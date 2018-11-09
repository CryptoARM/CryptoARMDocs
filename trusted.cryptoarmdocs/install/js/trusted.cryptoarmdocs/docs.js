var AJAX_CONTROLLER = BX.message('TR_CA_DOCS_AJAX_CONTROLLER');
var NO_CLIENT = BX.message('TR_CA_DOCS_ALERT_NO_CLIENT');
var HTTP_WARNING = BX.message('TR_CA_DOCS_ALERT_HTTP_WARNING');
var REMOVE_ACTION_CONFIRM = BX.message('TR_CA_DOCS_ALERT_REMOVE_ACTION_CONFIRM');
var LOST_DOC_REMOVE_CONFIRM_PRE = BX.message('TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE');
var LOST_DOC_REMOVE_CONFIRM_POST = BX.message('TR_CA_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_POST');
var LOST_DOC= BX.message('TR_CA_DOCS_ALERT_LOST_DOC');
var ERROR_FILE_NOT_FOUND = BX.message('TR_CA_DOCS_ERROR_FILE_NOT_FOUND');
var ERROR_DOC_NOT_FOUND = BX.message('TR_CA_DOCS_ERROR_DOC_NOT_FOUND');
var ERROR_DOC_BLOCKED = BX.message('TR_CA_DOCS_ERROR_DOC_BLOCKED');
var ERROR_DOC_ROLE_SIGNED = BX.message('TR_CA_DOCS_ERROR_DOC_ROLE_SIGNED');

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
    $.ajax({
        url: AJAX_CONTROLLER + '?command=sign',
        type: 'post',
        data: {id: ids, extra: extra},
        success: function (d) {
            // mobile CryptoArm support START
            if (/iphone/i.test(navigator.userAgent)) {
                let filenameArr = [];
                let idArr = [];
                docs = JSON.parse(d.docsToSign);
                docs.forEach(function (elem) {
                    filenameArr.push(elem.name);
                    idArr.push(elem.id);
                });
                let url = "cryptoarmgost://sign/?ids=" + idArr + "&extra=" + extra.role + "&url="
                    + JSON.parse(d.docsToSign)[0].url + "&filename=" + filenameArr + "&href="
                    + window.location.href + "&uploadurl=" + AJAX_CONTROLLER + "&command=upload&browser=";
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
                    req.params.token = '';
                    req.params.files = docs;
                    req.params.extra = extra;
                    req.params.uploader = AJAX_CONTROLLER + '?command=upload';
                    if (socket.connected) {
                        socket.emit('sign', req);
                        ids = [];
                        docs.forEach(function (elem) {
                            ids.push(elem.id);
                        });
                        block(ids, function () {
                            location.reload();
                        });
                    } else {
                        alert(NO_CLIENT);
                    }
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
    // TODO: simplify
    if (force) {
        var conf = true;
    } else {
        var conf = confirm(message);
    }
    if (conf == true) {
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
            if (d.docsFileNotFound || d.docsNotFound) {
                show_messages(d);
            }
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
