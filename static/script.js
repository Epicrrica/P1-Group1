document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('product_images');
    const previewGrid = document.getElementById('image-preview-grid');
    const emptyState = document.getElementById('image-preview-empty');
    const countLabel = document.getElementById('image-preview-count');
    const clearButton = document.getElementById('clear-image-selection');

    if (!input || !previewGrid || !emptyState || !countLabel || !clearButton) {
        return;
    }

    const selectedFiles = [];

    function syncInputFiles() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(function (file) {
            dataTransfer.items.add(file);
        });
        input.files = dataTransfer.files;
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';

        if (selectedFiles.length === 0) {
            previewGrid.classList.add('d-none');
            emptyState.classList.remove('d-none');
            clearButton.classList.add('d-none');
            countLabel.textContent = '0 images selected';
            return;
        }

        emptyState.classList.add('d-none');
        previewGrid.classList.remove('d-none');
        clearButton.classList.remove('d-none');
        countLabel.textContent = selectedFiles.length + (selectedFiles.length === 1 ? ' image selected' : ' images selected');

        selectedFiles.forEach(function (file, index) {
            const col = document.createElement('div');
            col.className = 'col-sm-6 col-lg-4';

            const card = document.createElement('div');
            card.className = 'card shadow-sm h-100 border-0 image-preview-card';

            const img = document.createElement('img');
            img.className = 'card-img-top image-preview-thumb';
            img.alt = file.name;

            const body = document.createElement('div');
            body.className = 'card-body p-3';

            const title = document.createElement('p');
            title.className = 'fw-semibold small mb-1 text-truncate';
            title.textContent = file.name;

            const metaRow = document.createElement('div');
            metaRow.className = 'd-flex justify-content-between align-items-center gap-2';

            const meta = document.createElement('p');
            meta.className = 'text-muted small mb-0';
            meta.textContent = 'Image ' + (index + 1);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-sm btn-outline-danger';
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', function () {
                selectedFiles.splice(index, 1);
                syncInputFiles();
                renderPreviews();
            });

            metaRow.appendChild(meta);
            metaRow.appendChild(removeButton);
            body.appendChild(title);
            body.appendChild(metaRow);
            card.appendChild(img);
            card.appendChild(body);
            col.appendChild(card);
            previewGrid.appendChild(col);

            const reader = new FileReader();
            reader.onload = function (loadEvent) {
                img.src = loadEvent.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    input.addEventListener('change', function (event) {
        const newFiles = Array.from(event.target.files || []);

        newFiles.forEach(function (file) {
            if (!file.type.startsWith('image/')) {
                return;
            }

            const duplicate = selectedFiles.some(function (selectedFile) {
                return selectedFile.name === file.name &&
                    selectedFile.size === file.size &&
                    selectedFile.lastModified === file.lastModified;
            });

            if (!duplicate) {
                selectedFiles.push(file);
            }
        });

        syncInputFiles();
        renderPreviews();
    });

    clearButton.addEventListener('click', function () {
        selectedFiles.splice(0, selectedFiles.length);
        input.value = '';
        syncInputFiles();
        renderPreviews();
    });

    renderPreviews();
});
