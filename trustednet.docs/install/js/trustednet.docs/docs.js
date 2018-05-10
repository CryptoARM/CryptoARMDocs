var AJAX_CONTROLLER = BX.message('TN_DOCS_AJAX_CONTROLLER');
var NO_CLIENT = BX.message('TN_DOCS_ALERT_NO_CLIENT');
var REMOVE_ACTION_CONFIRM = BX.message('TN_DOCS_ALERT_REMOVE_ACTION_CONFIRM');
var LOST_DOC_REMOVE_CONFIRM_PRE = BX.message('TN_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE');
var LOST_DOC_REMOVE_CONFIRM_POST = BX.message('TN_DOCS_ALERT_LOST_DOC_REMOVE_CONFIRM_POST');
var LOST_DOC= BX.message('TN_DOCS_ALERT_LOST_DOC');

var socket = io('https://localhost:4040');

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
    $.ajax({
        url: AJAX_CONTROLLER + '?command=sign',
        type: 'post',
        data: {id: ids, extra: extra},
        success: function (d) {
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
                    docs.forEach(function(elem) {
                        ids.push(elem.id);
                    });
                    block(ids, function(){
                        location.reload();
                    });
                } else {
                    alert(NO_CLIENT);
                }
            } else {
                console.log(d);
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

function verify(ids) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=verify',
        type: 'post',
        data: {id: ids},
        success: function (d) {
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

function download(id, del = false) {
    $.ajax({
        url: AJAX_CONTROLLER + '?command=download',
        type: 'post',
        data: {id: id},
        success: function(d) {
            console.log(d);
            if (d.success === true) {
                window.location.href = AJAX_CONTROLLER + '?command=content&id=' + id;
            } else {
                if (del) {
                    var removeMessage = LOST_DOC_REMOVE_CONFIRM_PRE;
                    removeMessage += '\n' + d.filename + '\n';
                    removeMessage += LOST_DOC_REMOVE_CONFIRM_POST;
                    remove({id}, removeMessage);
                } else {
                    var alertMessage = LOST_DOC;
                    alertMessage += '\n' + d.filename;
                    alert(alertMessage);
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

