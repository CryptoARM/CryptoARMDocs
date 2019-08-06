INPUT_FILE = BX.message('TR_CA_DOCS_COMP_FORM_INPUT_FILE');

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

function showUploadFile (id, numInput, multiple = '') {
    let idInputElem = id + '_' + numInput;
    let fileName = $('#input_file_' + idInputElem + multiple)[0].files[0].name;
    $('#trca-sf-upload-file-name-' + idInputElem).text(fileName);
    $('#trca-sf-upload-file-button-' + idInputElem).show();
    $('#trca-sf-upload-input-' + idInputElem).hide();

    fileName = String.fromCharCode(171) + fileName + String.fromCharCode(187);
    $('#trca-sf-upload-file-name-' + idInputElem).prop('title', fileName);
}

function hideUploadFile (id) {
    let idInputElem = id + "_0";
    $('#input_file_' + idInputElem).val(null);
    $('#trca-sf-upload-file-button-' + idInputElem).hide();
    $('#trca-sf-upload-input-' + idInputElem).show();
}

function createInputDiv (id, numOfInputs, multiple) {
    let idInputElem = id + "_" + numOfInputs;
    let inputDiv = document.createElement("div");
    inputDiv.className = "trca-sf-upload-input";
    inputDiv.id = "trca-sf-upload-input-" + idInputElem;

    let inputDivText = `
        <input type="file" id="input_file_${idInputElem + multiple}"
            name="input_file_${idInputElem + multiple}"
            onchange="addInputTypeFileField(${id}, ${numOfInputs}, '${multiple}')">
        </input>
        ${INPUT_FILE}`;
    inputDiv.innerHTML = inputDivText;

    return inputDiv;
}

function createUploadFileDiv (id, numOfInputs) {
    let idInputElem = id + '_' + numOfInputs;
    let fileDiv = document.createElement("div");
    fileDiv.className = "trca-sf-upload-file-button";
    fileDiv.id = "trca-sf-upload-file-button-" + idInputElem;

    let fileDivText = `
        <div class="trca-sf-upload-file">
            <div class="trca-sf-upload-file-icon">
                <i class="material-icons">
                    insert_drive_file
                </i>
            </div>
            <div class ="trca-sf-upload-file-name" id="trca-sf-upload-file-name-${idInputElem}"></div>
            <div class ="trca-sf-upload-file-remove" id="trca-sf-upload-file-remove-${idInputElem}"
                 onclick="removeUploadFile(${id}, ${numOfInputs})">
                <i class="material-icons">
                    close
                </i>
            </div>
        </div>`;
    fileDiv.innerHTML = fileDivText;

    return fileDiv;
}

function changeDelete (id, numOfInputs, multiple) {
    let parentIncrement = numOfInputs - 1;
    let changeDelete = "input_file_" + id + "_" + parentIncrement + multiple;
    document.getElementById(changeDelete).removeAttribute("onchange");
}

function getNumberLastInputFile (id) {
    let inputsInDiv = $('#trca-sf-upload-button-' + id);
    numOfInputs = inputsInDiv.find("input").length;

    return numOfInputs;
}

function addInputTypeFileField(id, numInput, multiple) {
    showUploadFile(id, numInput, multiple);
    let numOfInputs  = getNumberLastInputFile(id);

    let parent = document.getElementById("trca-sf-upload-button-" + id);
    let inputFileDiv = document.createElement("div");
    inputFileDiv.className = "trca-sf-upload-button-input";
    inputFileDiv.id = "trca-sf-upload-button-input-" + id + "_" + numOfInputs;
    parent.appendChild(inputFileDiv);

    inputFileDiv.appendChild(createInputDiv(id, numOfInputs, multiple));
    inputFileDiv.appendChild(createUploadFileDiv(id, numOfInputs));

    changeDelete(id, numOfInputs, multiple);
}

function removeUploadFile(id, numInput) {
    document.getElementById("trca-sf-upload-button-input-" + id + '_' + numInput).remove();
    let numOfInputs  = getNumberLastInputFile(id);

    for (numInput; numInput < numOfInputs; numInput++){
        let nextElement = numInput + 1;
        let idInputElem = id + '_' + numInput;
        let idNextInputElem = id + '_' + nextElement;
        let inputFile = document.getElementById("input_file_" + idNextInputElem + "_Y");
        inputFile.id = "input_file_" + idInputElem + "_Y";
        $('#input_file_' + idInputElem + '_Y').attr('name',"input_file_" + idInputElem + "_Y");

        if (typeof inputFile.onchange === 'function'){
            $('#input_file_' + idInputElem + '_Y').attr('onchange','addInputTypeFileField(' + id + ',' + numInput +",'_Y')");
        }

        document.getElementById("trca-sf-upload-button-input-" + idNextInputElem).id = "trca-sf-upload-button-input-" + idInputElem;
        document.getElementById("trca-sf-upload-input-" + idNextInputElem).id = "trca-sf-upload-input-" + idInputElem;
        document.getElementById("trca-sf-upload-file-button-" + idNextInputElem).id = "trca-sf-upload-file-button-" + idInputElem;
        document.getElementById("trca-sf-upload-file-name-" + idNextInputElem).id = "trca-sf-upload-file-name-" + idInputElem;
        document.getElementById("trca-sf-upload-file-remove-" + idNextInputElem).id = "trca-sf-upload-file-remove-" + idInputElem;
        $('#trca-sf-upload-file-remove-' + idInputElem).attr('onclick','removeUploadFile(' + id + ',' + numInput + ')');
    }
}

