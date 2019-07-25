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