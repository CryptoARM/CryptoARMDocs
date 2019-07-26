function showFileName(id) {
    let name = $('#trca-sf-download-input-' + id)[0].files[0].name;
    $('#trca-sf-download-file-name-' + id).text(name);
    name = String.fromCharCode(171) + name + String.fromCharCode(187);
    $('#trca-sf-download-file-name-' + id).prop('title', name);
    $('#trca-sf-download-file-hide-' + id).show();
    $('#trca-sf-download-button-' + id).hide();
}

function hideFileName(id) {
    $('#trca-sf-download-input-' + id).val(null);
    $('#trca-sf-download-file-hide-' + id).hide();
    $('#trca-sf-download-button-' + id).show();
}

window.onload = function addAutoResize() {
    document.querySelectorAll('[data-autoresize]').forEach(function (element) {
        element.style.boxSizing = 'border-box';
        var offset = element.offsetHeight - element.clientHeight;
        document.addEventListener('input', function (event) {
            event.target.style.height = 'auto';
            event.target.style.height = event.target.scrollHeight + offset + 'px';
        });
        element.removeAttribute('data-autoresize');
    });
};

function addInputTypeFileField(id) {
    let parentDivId = "#trca-sf-download-button-" + id;
    let parent = document.getElementById("trca-sf-download-button-" + id);
    let inputsInDiv = $(parentDivId);
    let numOfInputs = inputsInDiv.find("input").length;
    let inputFile = document.createElement('input');

    inputFile.setAttribute("type", "file");
    inputFile.setAttribute("id", "input_file_" + id + "_" + numOfInputs + "_Y");
    inputFile.setAttribute("name", "input_file_" + id + "_" + numOfInputs + "_Y");
    inputFile.setAttribute("onclick", "addInputTypeFileField(" + id + ")");
    parent.appendChild(inputFile);

    let parentIncrement = numOfInputs - 1;
    let kek = "input_file_" + id + "_" + parentIncrement + "_Y";

    document.getElementById(kek).removeAttribute("onclick");
}