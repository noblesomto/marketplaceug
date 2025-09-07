
// ==================== Image Upload and Management ====================
const existingPreview = document.getElementById("preview");
const orderInput = document.getElementById("existing_image_order");
const fileInput = document.getElementById("imageUpload");
const form = document.getElementById("advertForm");

if (existingPreview) {
    new Sortable(existingPreview, {
        animation: 150,
        handle: '.image-container',
        onEnd: updateOrderInput
    });

    existingPreview.addEventListener("click", function (e) {
        if (e.target.classList.contains("delete-image")) {
            const imageId = e.target.dataset.id;
            const container = e.target.closest(".image-container");

            if (imageId && form) {
                const deletedInput = document.createElement('input');
                deletedInput.type = 'hidden';
                deletedInput.name = 'deleted_images[]';
                deletedInput.value = imageId;
                form.appendChild(deletedInput);
            }

            container.remove();

            const hiddenInput = document.querySelector(`input[name="existing_images[]"][value="${imageId}"]`);
            if (hiddenInput) hiddenInput.remove();

            updateOrderInput();
        }
    });
}

fileInput?.addEventListener("change", function () {
    Array.from(fileInput.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const newImage = document.createElement("div");
            newImage.classList.add("relative", "group", "cursor-move", "image-container");
            newImage.innerHTML = `
                <img src="${e.target.result}" class="w-full h-auto rounded-lg shadow">
                <button type="button" class="absolute top-0 right-0 w-6 h-6 text-red-500 bg-white rounded-full hover:bg-red-100 delete-image flex items-center justify-center">&times;</button>
            `;
            existingPreview.appendChild(newImage);
        };
        reader.readAsDataURL(file);
    });
});

function updateOrderInput() {
    if (!orderInput) return;
    const ids = Array.from(existingPreview.querySelectorAll(".image-container[data-id]"))
        .map(el => el.dataset.id);
    orderInput.value = ids.join(',');
}

// Initial population
updateOrderInput();

// ==================== LGA Initialization ====================
    // Get advert data from global variable (passed from Blade)
    if (window.advertData && window.advertData.state) {
        const stateSelect = document.getElementById('state');
        if (stateSelect) {
            stateSelect.value = window.advertData.state;
            toggleLGA(stateSelect);

            setTimeout(() => {
                const lgaSelect = document.getElementById('lga');
                if (lgaSelect && window.advertData.lga) {
                    lgaSelect.value = window.advertData.lga;
                }
            }, 100);
        }
    }

