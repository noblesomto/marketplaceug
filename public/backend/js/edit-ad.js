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
    const itemCondition = document.getElementById("itemCondition");
    const buyDirect = document.getElementById("buyDirect");

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

    // Functions
    function initializeForm() {
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
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

        toggleSections(subcategoryId);

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

    function toggleSections(subcategoryId) {
        // Car section (subcategory 2)
        if (subcategoryId == 2) {
            if (divCar) divCar.classList.remove('hidden');
            if (divPhone) divPhone.classList.add('hidden');
            if (divModel) divModel.classList.remove('hidden');
            if (shipmentDiv) shipmentDiv.classList.add('hidden');
            if (itemCondition) itemCondition.classList.add('hidden');
            if (modelSelect) modelSelect.setAttribute('required', 'required');
            if (buyDirect) buyDirect.classList.add('hidden');
        }
        // Phone section (subcategory 6)
        else if (subcategoryId == 6) {
            if (divCar) divCar.classList.add('hidden');
            if (divPhone) divPhone.classList.remove('hidden');
            if (divModel) divModel.classList.remove('hidden');
            if (shipmentDiv) shipmentDiv.classList.add('hidden');
            if (itemCondition) itemCondition.classList.add('hidden');

        }
        // Other sections
        else {
            if (divCar) divCar.classList.add('hidden');
            if (divPhone) divPhone.classList.add('hidden');
            if (divModel) divModel.classList.add('hidden');
            if (shipmentDiv) shipmentDiv.classList.remove('hidden');

        }
    }



    // ==================== Shipment Toggle ====================
    function toggleDiv() {
        const selectedOption = document.querySelector('input[name="shipment"]:checked').value;
        const shipping = document.getElementById("shipping");

        if (selectedOption === "Ship") {
            shipping.classList.remove("hidden");
        } else {
            shipping.classList.add("hidden");
        }
    }

    // ==================== LGA Initialization ====================
    @if($advert->state)
        const stateSelect = document.getElementById('state');
        toggleLGA(stateSelect);

        setTimeout(() => {
            const lgaSelect = document.getElementById('lga');
            if(lgaSelect) {
                lgaSelect.value = "{{ $advert->lga }}";
            }
        }, 100);
    @endif
});
