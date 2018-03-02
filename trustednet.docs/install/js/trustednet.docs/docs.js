var socket = io('https://localhost:4040');

socket.on('files signed', function (data) {
	console.log('файл подписан', data);
});

socket.on('file saved', function () {
	console.log("file saved");
});

socket.on('signature verified', function (data) {
	console.log('информация о подписи', data);
});

socket.on('error', function (data) {
    alert('Нет');
});

function sign(docs, extra = {}) {
    if (docs.length > 0) {
        req = {};
        req.jsonrpc = '2.0';
        req.method = 'sign';
        req.params = {};
        req.params.token = '';
        req.params.files = docs;
        req.params.extra = extra;
        req.params.uploader = TN_DOCS_AJAX_CONTROLLER + '?command=upload';
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
            alert(TN_ALERT_NO_CLIENT);
        }
    }
}

function block(ids, cb = null) {
    $.ajax({
        url: TN_DOCS_AJAX_CONTROLLER + '?command=block',
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
        url: TN_DOCS_AJAX_CONTROLLER + '?command=unblock',
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

function remove(ids, message = TN_ALERT_REMOVE_ACTION_CONFIRM) {
    var conf = confirm(message);
    if (conf == true) {
        $.ajax({
            url: TN_DOCS_AJAX_CONTROLLER + '?command=remove',
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

function downloadOrAlert(id, del = false) {
    $.ajax({
        url: TN_DOCS_AJAX_CONTROLLER + '?command=download',
        type: 'post',
        data: {id: id},
        success: function(d) {
            console.log(d);
            if (d.success === true) {
                window.location.href = TN_DOCS_AJAX_CONTROLLER + '?command=content&id=' + id;
            } else {
                if (del) {
                    var removeMessage = TN_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE;
                    removeMessage += '\n' + d.filename + '\n';
                    removeMessage += TN_ALERT_LOST_DOC_REMOVE_CONFIRM_POST;
                    remove({id}, removeMessage);
                } else {
                    var alertMessage = TN_ALERT_LOST_DOC;
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
        url: TN_DOCS_AJAX_CONTROLLER + '?command=view',
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

