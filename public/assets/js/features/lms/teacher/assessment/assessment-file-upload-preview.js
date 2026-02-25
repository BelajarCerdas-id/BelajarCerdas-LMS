const container = document.getElementById('container-assessment-file')
const fileInput = document.getElementById('assessment_value_file');
const filePreview = document.getElementById('file-preview');
const fileName = document.getElementById('file-name');
const fileSize = document.getElementById('file-size');
const removeBtn = document.getElementById('remove-file');

fileInput.addEventListener('change', function () {
    if (this.files.length > 0) {
        const file = this.files[0];

        fileName.textContent = truncateText(file.name, 20);
        fileSize.textContent = formatFileSize(file.size);

        filePreview.classList.remove('hidden');
        container.classList.add('hidden');
    }

});

removeBtn.addEventListener('click', function () {
    fileInput.value = '';
    filePreview.classList.add('hidden');
    fileName.textContent = '';
    fileSize.textContent = '';
    container.classList.remove('hidden');
});

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
}

function truncateText(text, maxLength) {
    return text.length > maxLength ? text.substring(0, maxLength) + "..." : text;
}