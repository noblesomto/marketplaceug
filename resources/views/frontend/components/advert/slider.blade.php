@php
    $mediaItems = $ad->getMedia('images');
    $imageUrls = $mediaItems->map(fn($media) => $media->getUrl('optimized'))->toArray();
@endphp

<div class="bg-gray-100 flex items-center justify-center py-6">
    <div class="relative w-full max-w-screen-lg overflow-hidden rounded-lg shadow-md group">
        <!-- Slider Container -->
        <div id="slider" class="flex transition-transform duration-500 ease-out">
            @foreach($mediaItems as $index => $media)
                <div class="flex-none w-full">
                    <img src="{{ $media->getUrl('large') }}"
                        alt="Image"
                        loading="lazy"
                        class="w-full h-full max-h-[60vh] object-cover rounded-lg bg-white cursor-pointer"
                        onclick="openLightbox({{ $index }})">
                </div>
            @endforeach
        </div>

        <!-- Navigation Arrows -->
        <button id="prev" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-200 z-10 hidden group-hover:block">‹</button>
        <button id="next" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-200 z-10 hidden group-hover:block">›</button>

        <!-- Indicators -->
        <div id="indicators" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
            @foreach ($mediaItems as $index => $media)
                <button data-index="{{ $index }}"
                    class="w-3 h-3 rounded-full bg-white border border-gray-400 opacity-70 hover:opacity-100 focus:outline-none"></button>
            @endforeach
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 hidden">
    <div class="relative w-full h-full flex items-center justify-center overflow-hidden">
        <img id="lightbox-image"
             src=""
             alt="Fullscreen Image"
             class="max-h-[90vh] max-w-[90vw] object-contain transition-transform duration-300"
             style="transform: scale(1);">

        <!-- Controls -->
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-3xl font-bold hover:text-gray-300 z-10">✕</button>
        <button id="lightbox-prev" class="absolute left-4 top-1/2 -translate-y-1/2 text-white bg-black/30 hover:bg-black/50 p-2 rounded-full z-10">‹</button>
        <button id="lightbox-next" class="absolute right-4 top-1/2 -translate-y-1/2 text-white bg-black/30 hover:bg-black/50 p-2 rounded-full z-10">›</button>

        <!-- Zoom Controls -->
        <div class="absolute bottom-6 right-6 flex space-x-2 z-10">
            <button onclick="zoomIn()" class="bg-white text-black px-2 py-1 rounded shadow">+</button>
            <button onclick="zoomOut()" class="bg-white text-black px-2 py-1 rounded shadow">−</button>
            <button onclick="resetZoom()" class="bg-white text-black px-2 py-1 rounded shadow">Reset</button>
        </div>

        <!-- Counter -->
        <div class="absolute bottom-6 left-6 text-white text-sm z-10">
            <span id="image-counter">1 / {{ count($mediaItems) }}</span>
        </div>
    </div>
</div>

<script>
    const imageList = @json($imageUrls); // 🔥 single source
    const slider = document.getElementById('slider');
    const indicators = document.querySelectorAll('#indicators button');
    const slides = imageList.length;

    let index = 0; // slider index
    let currentIndex = 0; // lightbox index
    let zoomLevel = 1;

    function showSlide(i) {
        index = i;
        slider.style.transform = `translateX(-${index * 100}%)`;
        indicators.forEach((btn, idx) => {
            btn.classList.toggle('bg-gray-800', idx === index);
            btn.classList.toggle('opacity-100', idx === index);
            btn.classList.toggle('opacity-70', idx !== index);
        });
    }

    document.getElementById('next').addEventListener('click', () => {
        index = (index + 1) % slides;
        showSlide(index);
    });

    document.getElementById('prev').addEventListener('click', () => {
        index = (index - 1 + slides) % slides;
        showSlide(index);
    });

    indicators.forEach(btn => {
        btn.addEventListener('click', () => showSlide(parseInt(btn.dataset.index)));
    });

    // Swipe support for slider
    let startX = 0;
    slider.addEventListener('touchstart', e => startX = e.touches[0].clientX);
    slider.addEventListener('touchend', e => {
        const endX = e.changedTouches[0].clientX;
        if (startX - endX > 50) index = (index + 1) % slides;
        else if (endX - startX > 50) index = (index - 1 + slides) % slides;
        showSlide(index);
    });

    showSlide(index);

    // === Lightbox ===
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');

    function openLightbox(i) {
        currentIndex = i;
        zoomLevel = 1;
        updateLightbox();
        lightbox.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.add('hidden');
        lightboxImage.src = '';
        document.body.style.overflow = '';
    }

    function updateLightbox() {
        lightboxImage.src = imageList[currentIndex];
        lightboxImage.style.transform = `scale(${zoomLevel})`;
        document.getElementById('image-counter').textContent =
            `${currentIndex + 1} / ${imageList.length}`;
        showSlide(currentIndex); // 🔥 keep slider in sync
    }

    function showNext() {
        currentIndex = (currentIndex + 1) % imageList.length;
        resetZoom();
        updateLightbox();
    }

    function showPrev() {
        currentIndex = (currentIndex - 1 + imageList.length) % imageList.length;
        resetZoom();
        updateLightbox();
    }

    function zoomIn() { zoomLevel = Math.min(zoomLevel + 0.2, 3); updateLightbox(); }
    function zoomOut() { zoomLevel = Math.max(0.5, zoomLevel - 0.2); updateLightbox(); }
    function resetZoom() { zoomLevel = 1; lightboxImage.style.transform = 'scale(1)'; }

    document.getElementById('lightbox-next').addEventListener('click', showNext);
    document.getElementById('lightbox-prev').addEventListener('click', showPrev);

    // Backdrop click closes
    lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });

    // Keyboard shortcuts
    document.addEventListener('keydown', e => {
        if (lightbox.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
        if (e.key === '+' || e.key === '=') zoomIn();
        if (e.key === '-') zoomOut();
        if (e.key === '0') resetZoom();
    });
</script>

<style>
    #lightbox-image {
        cursor: grab;
    }
    #lightbox-image:active {
        cursor: grabbing;
    }
    #lightbox {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    #lightbox button {
        transition: all 0.2s ease;
    }
    body.lightbox-open {
        overflow: hidden;
    }
</style>
