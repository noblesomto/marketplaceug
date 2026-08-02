// Remove loading overlay on location-dependent fields
[...document.querySelectorAll('.input-location-dependant')].forEach(el =>
    el.classList.toggle('d-none')
);

const toggleLGA = async (target) => {
    const state = target.value;
    const lgaSelect = document.getElementById('lga') || document.querySelector('.select-lga');

    if (!lgaSelect) return;

    // Clear and show loading state
    lgaSelect.innerHTML = '<option value="" disabled selected>Loading Districts...</option>';
    lgaSelect.disabled = true;

    if (!state) {
        lgaSelect.innerHTML = '<option value="" disabled selected>Select District...</option>';
        lgaSelect.disabled = false;
        return;
    }

    try {
        const response = await fetch(`/api/locations/states/${encodeURIComponent(state)}/lgas`);
        const data = await response.json();

        lgaSelect.innerHTML = '<option value="" disabled selected>Select District...</option>';

        if (data.success && data.data.lgas.length) {
            data.data.lgas.forEach(lga => {
                const opt = document.createElement('option');
                opt.value = lga;
                opt.textContent = lga;
                lgaSelect.appendChild(opt);
            });
        }
    } catch {
        lgaSelect.innerHTML = '<option value="" disabled selected>Select District...</option>';
    } finally {
        lgaSelect.disabled = false;
    }
};
