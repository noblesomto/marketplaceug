document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("imageUpload");
    const preview = document.getElementById("preview");
    const errorBox = document.getElementById("image-error");
    const form = input.closest("form");
    let fileList = [];
    let sortableInstance = null;

    // File input change
    input.addEventListener("change", () => {
        fileList = Array.from(input.files);
        updateFileInput();
        renderPreview();
        errorBox.classList.add("hidden");
    });

    // Initialize Sortable - moved to function to reinitialize when needed
    function initializeSortable() {
        if (sortableInstance) {
            sortableInstance.destroy();
        }

        sortableInstance = new Sortable(preview, {
            animation: 150,
            handle: '.preview-item', // More specific handle
            ghostClass: 'sortable-ghost',
            onEnd: (evt) => {
                // Get the new order based on current DOM structure
                const newOrder = [];
                preview.querySelectorAll(".preview-item").forEach(div => {
                    const index = parseInt(div.dataset.originalIndex);
                    newOrder.push(fileList[index]);
                });
                fileList = newOrder;
                updateFileInput();

                // Update dataset indices without full re-render
                updateDatasetIndices();
            }
        });
    }

    // Validate on submit
    form?.addEventListener("submit", function (e) {
        if (!fileList.length && typeof selectedCategoryId !== 'undefined' && selectedCategoryId != 3) {
            e.preventDefault();
            errorBox.textContent = "Please select at least one image before submitting.";
            errorBox.classList.remove("hidden");
            errorBox.scrollIntoView({ behavior: "smooth" });
        }
    });

    // Helper: Update dataset indices without full re-render
    function updateDatasetIndices() {
        preview.querySelectorAll(".preview-item").forEach((div, index) => {
            div.dataset.originalIndex = index;
            const deleteBtn = div.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.dataset.index = index;
            }
        });
    }

    // Helper: Reassign file input with updated fileList
    function updateFileInput() {
        const dt = new DataTransfer();
        fileList.forEach(file => dt.items.add(file));
        input.files = dt.files;

        // Update the image_order hidden input
        const orderInput = document.getElementById("image_order");
        if (orderInput) {
            const orderArray = fileList.map((file, index) => index);
            orderInput.value = orderArray.join(",");
        }
    }

    // Helper: Create single preview item
    function createPreviewItem(file, index) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const wrapper = document.createElement("div");
                wrapper.className = "relative preview-item group";
                wrapper.dataset.originalIndex = index;
                wrapper.innerHTML = `
                    <img src="${e.target.result}"
                         class="w-full h-auto object-cover rounded shadow cursor-move"
                         draggable="false">
                    <button type="button"
                            class="absolute top-1 right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition delete-btn"
                            title="Remove"
                            data-index="${index}">✖</button>
                `;

                // Attach delete handler immediately
                const deleteBtn = wrapper.querySelector('.delete-btn');
                deleteBtn.addEventListener("click", function (e) {
                    e.stopPropagation(); // Prevent any drag events
                    const index = parseInt(this.dataset.index);
                    deleteFile(index);
                });

                resolve(wrapper);
            };
            reader.readAsDataURL(file);
        });
    }

    // Helper: Delete file
    function deleteFile(index) {
        fileList.splice(index, 1);
        updateFileInput();
        renderPreview();
    }

    // Helper: Preview and delete
    async function renderPreview() {
        preview.innerHTML = "";

        // Create all preview items
        const previewPromises = fileList.map((file, index) =>
            createPreviewItem(file, index)
        );

        try {
            const previewItems = await Promise.all(previewPromises);
            previewItems.forEach(item => preview.appendChild(item));

            // Initialize sortable after all items are rendered
            if (fileList.length > 0) {
                initializeSortable();
            }
        } catch (error) {
            console.error('Error rendering preview:', error);
        }
    }
});

// Add CSS for better drag experience
const style = document.createElement('style');
style.textContent = `
    .sortable-ghost {
        opacity: 0.5;
        background: #f0f0f0;
    }

    .preview-item {
        cursor: move;
    }

    .preview-item:hover {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }

    .delete-btn {
        cursor: pointer;
        z-index: 10;
    }

    .delete-btn:hover {
        background-color: #dc2626 !important;
        transform: scale(1.1);
    }
`;
document.head.appendChild(style);
