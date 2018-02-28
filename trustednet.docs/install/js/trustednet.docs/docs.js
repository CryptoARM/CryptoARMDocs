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
    req = {};
    req.jsonrpc = '2.0';
    req.method = 'sign';
    req.params = {};
    req.params.token = '';
    req.params.files = docs;
    req.params.extra = extra;
    req.params.uploader = TN_DOCS_AJAX_CONTROLLER + '?command=upload';
    if (socket.connected) {
        console.log(req);
        socket.emit('sign', req);
    } else {
        alert(TN_ALERT_NO_CLIENT);
    }
}

////configure
//var jrpc = new simple_jsonrpc();
//jrpc.toStream = function(_msg){
//    var xhr = new XMLHttpRequest();
//    xhr.onreadystatechange = function() {
//        if (this.readyState != 4) return;
//        try {
//            JSON.parse(this.responseText);
//            jrpc.messageHandler(this.responseText);
//        }
//        catch (e){
//            console.error(e);
//        }
//    };
//    xhr.open("POST", "https://localhost:8088/", true);
//    xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
//    xhr.send(_msg);
//};
//
////calls
//jrpc.call('add', [2, 3]).then(function (result) {
//    document.getElementsByClassName('adm-title')[0].innerHTML += 'add(2, 3) result: ' + result + '<br>';
//});
//jrpc.call('mul', {y: 3, x: 2}).then(function (result) {
//    document.getElementsByClassName('adm-title')[0].innerHTML += 'mul(2, 3) result: ' + result + '<br>';
//});
//jrpc.call('view.getTitle').then(function (result) {
//    document.getElementsByClassName('title')[0].innerHTML = result;
//});

function sign_old(ids, extra = null, cb = null) {
    $.ajax({
        url: TN_DOCS_AJAX_CONTROLLER + '?command=sign',
        type: 'post',
        data: {id: ids, extra: extra},
        success: function (d) {
            console.log(d);
            if (cb){
                cb(d);
            }
            //window.drawStatus ? drawStatus() : console.log("drawStatus not implemented");
            //window.getStatus ? getStatus() : console.log("getStatus not implemented");
            return d;
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

function signOrAlert(ids, extra = null, del = false) {
    var res = sign(ids, extra, function (res) {
        if (res.docsFileNotFound) {
            var docsFileNotFoundFilenames = '';
            res.docsFileNotFound.forEach(function (elem) {
                docsFileNotFoundFilenames += '\n' + elem.id + ': ' + elem.filename;
            });
            if (del) {
                var docsFileNotFoundIds = [];
                res.docsFileNotFound.forEach(function (elem) {
                    docsFileNotFoundIds.push(elem.id);
                });
                var removeMessage = TN_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE;
                removeMessage += docsFileNotFoundFilenames;
                removeMessage += '\n' + TN_ALERT_LOST_DOC_REMOVE_CONFIRM_POST;
                remove(docsFileNotFoundIds, removeMessage)
            } else {
                var alertMessage = TN_ALERT_LOST_DOC;
                alertMessage += docsFileNotFoundFilenames;
                alert(alertMessage);
            }
        }
        if (res.docsNotFound) {
            message = TN_ALERT_DOC_NOT_FOUND;
            res.docsNotFound.forEach(function (elem) {
                message += elem + '\n';
            });
            alert(message);
        }
        if (res.docsBlocked) {
            message = TN_ALERT_DOC_BLOCKED;
            res.docsBlocked.forEach(function (elem) {
                message += '\n' + elem.id + ': ' + elem.filename;
            });
            alert(message);
        }
        if (res.docsSigned) {
            message = TRUSTEDNETSIGNER_DOC_SIGN_NOT_NEEDED;
            res.docsSigned.forEach(function (elem) {
                message += '\n' + elem.id + ': ' + elem.filename;
            });
            alert(message);
        }
        if (!res.success) {
            if (res.message) {
                alert(res.message);
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

