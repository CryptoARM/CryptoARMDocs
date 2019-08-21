trustedBPcheckFile = function(file, maxSize) {
    onFailure = () => {
        $("#trca-BP-uploadFile-input").val(null);
        $("#trca-BP-uploadFile-sign").attr("class","ui-btn");
        $("#trca-BP-uploadFile-sign").attr("onclick", "");
        $("#trca-BP-uploadFile-addFile")[0].lastChild.nodeValue = BX.message('BPAA_ACT_ADD_FILE');
    };
    onSuccess = () => {trustedCA.checkAccessFile(file, trustedBPremakeSign(file), onFailure)};
    trustedCA.checkFileSize(file, maxSize, onSuccess, onFailure );
};

trustedBPremakeSign = function(file) {
    $("#trca-BP-uploadFile-sign").attr("class","ui-btn ui-btn-success");
    $("#trca-BP-uploadFile-sign").attr("onclick", "trustedBPSign()");
    $("#trca-BP-uploadFile-addFile")[0].lastChild.nodeValue = file.name;
};

trustedBPSign = function() {
    if (!$("#trca-BP-uploadFile").val()) {
        trustedBPUpload();
    } else {
        $("#trca-BP-uploadFile-sign").click();
    }
};

trustedBPUpload = function () {
    let $input = $("#trca-BP-uploadFile-input");
    let fd = new FormData;

    fd.append('file', $input.prop('files')[0]);

    $.ajax({
        url: window.location.protocol + '//' + window.location.host + '/bitrix/activities/custom/trustedcaupload/uploadDocs.php',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (data) {
            if (data) {
                docId = [data];
                onSuccess = () => { $('#trca-BP-uploadFile').click() };
                trustedCA.sign(docId, null, onSuccess);
                $("#trca-BP-uploadFile").val(data);
                $("#trca-BP-uploadFile-sign").attr("onclick", " trustedCA.sign([" + docId + "], null, " + onSuccess + ");");
            }
        }
    });
};