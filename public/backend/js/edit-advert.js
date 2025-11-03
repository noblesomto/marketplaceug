document.addEventListener('DOMContentLoaded', function() {
    // ==================== Form Selection Logic ====================
    const categorySelect = document.getElementById('category');
    const subcategorySelect = document.getElementById('subcategory');
    const brandSelect = document.getElementById('brand');
    const modelSelect = document.getElementById('model');
    const divCar = document.getElementById('divCar');
    const divPhone = document.getElementById('divPhone');
    const divModel = document.getElementById('divModel');
    const shipmentDiv = document.getElementById('shipment');
    const shipping = document.getElementById('shipping');
    const itemCondition = document.getElementById("itemCondition");
    const buyDirect = document.getElementById("buyDirect");
    const price = document.getElementById("price");
    var salary = document.getElementById("salary");
    var expectedSalary = document.getElementById("expectedSalary");
    var services = document.getElementById("services");
    var quantity = document.getElementById('quantity');
    
    // Initialize all sections as hidden
    hideElement(salary);
    hideElement(expectedSalary);
    hideElement(services);

    // Store original values from data attributes
    const originalValues = {
        category: categorySelect.dataset.selected,
        subcategory: subcategorySelect.dataset.selected,
        brand: brandSelect.dataset.selected,
        model: modelSelect ? modelSelect.dataset.selected : null
    };

    // Initialize the form
    initializeForm();

    // Event Handlers
    categorySelect.addEventListener('change', handleCategoryChange);
    subcategorySelect.addEventListener('change', handleSubcategoryChange);
    if (brandSelect) brandSelect.addEventListener('change', handleBrandChange);

    // Helper functions for hide/show
    function hideElement(element) {
        if (element) element.classList.add('d-none');
    }

    function showElement(element) {
        if (element) element.classList.remove('d-none');
    }

    // Functions
    function initializeForm() {
        const catId = categorySelect.value;
        const subcatId = subcategorySelect.value;

        toggleSections(subcatId, catId);

        // If a category was pre-selected, trigger the dependent changes
        if (catId) categorySelect.dispatchEvent(new Event('change'));
        if (subcatId) subcategorySelect.dispatchEvent(new Event('change'));
    }

    function handleCategoryChange() {
        const categoryId = this.value;
        if (!categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            toggleSections('');
            return;
        }
        toggleSections(null, categoryId);

        fetch(`/fetch-subcat/${categoryId}`)
            .then(response => response.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                data.forEach(subcat => {
                    const option = document.createElement('option');
                    option.value = subcat.id;
                    option.text = subcat.sub_category;
                    if (subcat.id == originalValues.subcategory && categoryId == originalValues.category) {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });

                if (subcategorySelect.value) {
                    subcategorySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function handleSubcategoryChange() {
        const subcategoryId = this.value;
        if (!subcategoryId) {
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            toggleSections('');
            return;
        }

        toggleSections(subcategoryId, null);

        fetch(`/fetch-brand/${subcategoryId}`)
            .then(response => response.json())
            .then(data => {
                brandSelect.innerHTML = '<option value="">Select Brand</option>';
                data.forEach(brand => {
                    const option = document.createElement('option');
                    option.value = brand.id;
                    option.text = brand.brand;
                    if (brand.id == originalValues.brand && subcategoryId == originalValues.subcategory) {
                        option.selected = true;
                    }
                    brandSelect.appendChild(option);
                });

                if (brandSelect.value && (subcategoryId == 2 || subcategoryId == 6)) {
                    brandSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function handleBrandChange() {
        const brandId = this.value;
        const subcategoryId = subcategorySelect.value;

        if (!brandId || !(subcategoryId == 2 || subcategoryId == 6)) {
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            return;
        }

        fetch(`/fetch-model/${brandId}`)
            .then(response => response.json())
            .then(data => {
                if (!modelSelect) return;

                modelSelect.innerHTML = '<option value="">Select Model</option>';
                data.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.id;
                    option.text = model.model;

                    if (modelSelect.dataset.selected &&
                        brandId == originalValues.brand &&
                        subcategoryId == originalValues.subcategory &&
                        model.id == originalValues.model) {
                        option.selected = true;
                    }

                    modelSelect.appendChild(option);
                });

                modelSelect.dataset.selected = '';
            })
            .catch(error => console.error('Error:', error));
    }

    function toggleSections(subcategoryId, categoryId) {
        // Reset all sections first
        hideElement(services);
        showElement(buyDirect);
        showElement(shipping);
        showElement(shipmentDiv);
        showElement(itemCondition);
        showElement(price);
        hideElement(salary);
        hideElement(expectedSalary);
        if (quantity) showElement(quantity);

        // Category Services (11)
        if (categoryId === "11") {
            showElement(services);
            hideElement(buyDirect);
            hideElement(shipping);
            hideElement(shipmentDiv);
            hideElement(itemCondition);
            if (quantity) hideElement(quantity);
        }

        // Category Jobs (3)
        if (categoryId === "3") {
            showElement(salary);
            hideElement(price);
            hideElement(shipmentDiv);
            hideElement(shipping);
            hideElement(itemCondition);
            hideElement(buyDirect);
            hideElement(expectedSalary);
            if (quantity) hideElement(quantity);
        }

        // Category CV (18)
        if (categoryId === "18") {
            showElement(expectedSalary);
            hideElement(price);
            hideElement(salary);
            hideElement(shipmentDiv);
            hideElement(shipping);
            hideElement(itemCondition);
            hideElement(buyDirect);
            if (quantity) hideElement(quantity);
        }

        // Car section (subcategory 2)
        if (subcategoryId == 2) {
            showElement(divCar);
            hideElement(divPhone);
            showElement(divModel);
            hideElement(shipping);
            hideElement(shipmentDiv);
            hideElement(itemCondition);
            hideElement(buyDirect);
            if (modelSelect) modelSelect.setAttribute('required', 'required');
        }
        // Phone section (subcategory 6)
        else if (subcategoryId == 6) {
            hideElement(divCar);
            showElement(divPhone);
            showElement(divModel);
            hideElement(shipmentDiv);
            hideElement(itemCondition);
        }
        // Other sections
        else {
            hideElement(divCar);
            hideElement(divPhone);
            hideElement(divModel);
        }
    }

    // ==================== Shipment Toggle ====================
    function toggleDiv() {
        const selectedOption = document.querySelector('input[name="shipment"]:checked').value;
        const shipping = document.getElementById("shipping");

        if (selectedOption === "Ship") {
            showElement(shipping);
        } else {
            hideElement(shipping);
        }
    }

    // Add event listeners to shipment radio buttons
    const shipmentRadios = document.querySelectorAll('input[name="shipment"]');
    shipmentRadios.forEach(radio => {
        radio.addEventListener('change', toggleDiv);
    });

    // ==================== Character Count for Title ====================
    const titleInput = document.getElementById('ad_title');
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            const charCount = this.value.length;
            const maxLength = 75;
            const charCountElement = document.getElementById('char-count');

            // Update character count
            charCountElement.textContent = charCount;

            // Style the input field
            if (charCount >= maxLength) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');

                // Make character counter red
                charCountElement.parentElement.classList.remove('text-muted');
                charCountElement.parentElement.classList.add('text-danger', 'fw-bold');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');

                // Reset character counter to muted
                charCountElement.parentElement.classList.remove('text-danger', 'fw-bold');
                charCountElement.parentElement.classList.add('text-muted');
            }
        });
    }

    // ==================== Character Count for Description ====================
 // Initialize TinyMCE
  function initializeTinyMCE() {
    if (typeof tinymce === 'undefined') {
      console.error('TinyMCE not loaded');
      showFallbackEditor();
      return;
    }

    try {
      tinymce.init({
        selector: '#tinymce-editor',
        plugins: [
          'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
          'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
          'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic underline | ' +
          'alignleft aligncenter alignright alignjustify | ' +
          'bullist numlist outdent indent | link image | ' +
          'removeformat | help',
        menubar: 'file edit view insert format tools table help',
        height: 500,
        promotion: false,
        branding: false,
        image_advtab: true,
        automatic_uploads: true,
        
        file_picker_types: 'image',
 
        setup: function (editor) {
          editor.on('init', function () {
            document.getElementById('editor-loading').classList.add('d-none');
            document.getElementById('tinymce-editor').classList.remove('d-none');
          });

          editor.on('error', function (e) {
            console.error('TinyMCE error:', e);
          });
        }
      });
    } catch (error) {
      console.error('TinyMCE initialization error:', error);
      showFallbackEditor();
    }
  }

  function showFallbackEditor() {
    const editorLoading = document.getElementById('editor-loading');
    const textarea = document.getElementById('tinymce-editor');

    editorLoading.innerHTML =
      '<div class="alert alert-warning">' +
      '<h6>Editor Loading Failed</h6>' +
      '<p>Using basic text editor instead. For full features, please refresh the page.</p>' +
      '</div>';

    textarea.classList.remove('d-none');
    textarea.classList.add('form-control');
    textarea.style.height = '300px';
  }

  setTimeout(initializeTinyMCE, 100);
});