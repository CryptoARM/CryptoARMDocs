function onUploadDocument(res) {
    let inputIndexFileId = "input_file_id_" + res.inputIndexFileId;
    let inputIndexFileIdObj = document.getElementById(inputIndexFileId);

    inputIndexFileIdObj.innerHTML = res.fileId; //doc id
}

function play() {
    let audio = document.getElementById("audioMonetka");
    audio.volume = 0.4;
    audio.play();
}