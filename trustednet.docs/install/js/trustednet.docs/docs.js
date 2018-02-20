function sign(ids, extra = null, cb = null) {
    $.ajax({
        url: TRUSTED_URI_COMPONENT_SIGN_AJAX + '?command=sign',
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
                var removeMessage = TRUSTEDNETSIGNER_LOST_DOC_REMOVE_CONFIRM_PRE;
                removeMessage += docsFileNotFoundFilenames;
                removeMessage += '\n' + TRUSTEDNETSIGNER_LOST_DOC_REMOVE_CONFIRM_POST;
                remove(docsFileNotFoundIds, removeMessage)
            } else {
                var alertMessage = TRUSTEDNETSIGNER_LOST_DOC_ALERT;
                alertMessage += docsFileNotFoundFilenames;
                alert(alertMessage);
            }
        }
        if (res.docsNotFound) {
            message = TRUSTEDNETSIGNER_DOC_NOT_FOUND;
            res.docsNotFound.forEach(function (elem) {
                message += elem + '\n';
            });
            alert(message);
        }
        if (res.docsBlocked) {
            message = TRUSTEDNETSIGNER_DOC_BLOCKED;
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
        url: TRUSTED_URI_COMPONENT_SIGN_AJAX + '?command=unblock',
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

function remove(ids, message = TRUSTEDNETSIGNER_REMOVE_ACTION_CONFIRM) {
    var conf = confirm(message);
    if (conf == true) {
        $.ajax({
            url: TRUSTED_URI_COMPONENT_SIGN_AJAX + '?command=remove',
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
        url: TRUSTED_URI_COMPONENT_SIGN_AJAX + '?command=download',
        type: 'post',
        data: {id: id},
        success: function(d) {
            console.log(d);
            if (d.success === true) {
                window.location.href = TRUSTED_URI_COMPONENT_SIGN_AJAX + '?command=content&id=' + id;
            } else {
                if (del) {
                    var removeMessage = TRUSTEDNETSIGNER_LOST_DOC_REMOVE_CONFIRM_PRE;
                    removeMessage += '\n' + d.filename + '\n';
                    removeMessage += TRUSTEDNETSIGNER_LOST_DOC_REMOVE_CONFIRM_POST;
                    remove({id}, removeMessage);
                } else {
                    var alertMessage = TRUSTEDNETSIGNER_LOST_DOC_ALERT;
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
        url: TRUSTED_URI_COMPONENT_SIGN_AJAX + '?command=view',
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

