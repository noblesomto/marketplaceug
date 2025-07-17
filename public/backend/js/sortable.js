document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("imageUpload");
    const preview = document.getElementById("preview");
    const errorBox = document.getElementById("image-error");
    const form = input.closest("form");

    let fileList = [];

    // File input change
    input.addEventListener("change", () => {
        fileList = Array.from(input.files);
        updateFileInput();
        renderPreview();
        errorBox.classList.add("hidden");
    });

    // Initialize Sortable
    new Sortable(preview, {
        animation: 150,
        onEnd: () => {
            const newOrder = [];
            preview.querySelectorAll(".preview-item").forEach(div => {
                const index = parseInt(div.dataset.index);
                newOrder.push(fileList[index]);
            });
            fileList = newOrder;
            updateFileInput();
            renderPreview();
        }
    });

    // Validate on submit
    form?.addEventListener("submit", function (e) {
        if (!fileList.length) {
            e.preventDefault();
            errorBox.textContent = "Please select at least one image before submitting.";
            errorBox.classList.remove("hidden");
            errorBox.scrollIntoView({ behavior: "smooth" });
        }
    });

    // Helper: Reassign file input with updated fileList
    function updateFileInput() {
        const dt = new DataTransfer();
        fileList.forEach(file => dt.items.add(file));
        input.files = dt.files;

        // Also update the image_order hidden input
        const orderInput = document.getElementById("image_order");
        const orderArray = fileList.map((file, index) => index); // Optional: use filenames instead
        orderInput.value = orderArray.join(",");
    }


    // Helper: Preview and delete
    function renderPreview() {
        preview.innerHTML = "";
        fileList.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const wrapper = document.createElement("div");
                wrapper.className = "relative preview-item group";
                wrapper.dataset.index = index;
                wrapper.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-auto object-cover rounded shadow">
                    <button type="button" class="absolute top-1 right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition delete-btn" title="Remove" data-index="${index}">✖</button>
                `;
                preview.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });

        // Attach delete handler after rendering
        setTimeout(() => {
            preview.querySelectorAll(".delete-btn").forEach(btn => {
                btn.addEventListener("click", function () {
                    const index = parseInt(this.dataset.index);
                    fileList.splice(index, 1);
                    updateFileInput();
                    renderPreview(); // fresh preview
                });
            });
        }, 50); // short delay to ensure images load
    }
});
