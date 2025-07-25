<div class="bg-gray-100 flex items-center justify-center py-6">
    <div class="relative w-full max-w-screen-lg overflow-hidden rounded-lg shadow-md group">
        <!-- Slider Container -->
        <div id="slider" class="flex transition-transform duration-500 ease-out">
            @foreach ($ad->images as $img)
            <div class="flex-none w-full {{ $img->is_portrait ? 'aspect-[3/4]' : 'aspect-video' }}">
                <img src="{{ asset('uploads/images/' . $img->image) }}"
                    alt="Image"
                    loading="lazy"
                    class="w-full h-full max-h-[60vh] {{ $img->is_portrait ? 'object-contain' : 'object-cover' }} rounded-lg bg-white cursor-pointer"
                    onclick="openLightbox({{ $loop->index }})">
            </div>
            @endforeach
        </div>

        <!-- Navigation Arrows -->
        <button id="prev"
            class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-200 z-10 hidden group-hover:block">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </button>

        <button id="next"
            class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-200 z-10 hidden group-hover:block">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>

        <div class="absolute top-2 right-2 space-y-2">
                    @if($ad->owner->verified=='yes')
                        <div class="bg-green-50 opacity-6 flex space-x-2 py-1 px-2 rounded">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                                  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                                  <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                </svg>
                            </span>
                            <span class="text-xs">Verified</span>
                        </div>
                    @endif
                    @if($ad->views >= 200)
                    <div class="bg-white opacity-6 flex space-x-2 py-1 px-2">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                                <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                            </svg>
                        </span>
                        <span class="text-xs">Popular</span>
                    </div>
                    @endif
                </div>

        <!-- Slide Indicators -->
        <div id="indicators" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
            @foreach ($ad->images as $index => $img)
            <button data-index="{{ $index }}"
                class="w-3 h-3 rounded-full bg-white border border-gray-400 opacity-70 hover:opacity-100 focus:outline-none"></button>
            @endforeach
        </div>
    </div>
</div>

@php
    $imageUrls = $ad->images->map(fn($img) => asset('uploads/images/' . $img->image))->toArray();
@endphp

<script>
    const imageList = @json($imageUrls);
</script>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 hidden">
    <div class="relative w-full h-full flex items-center justify-center overflow-hidden">
        <img id="lightbox-image"
             src=""
             alt="Fullscreen Image"
             class="max-h-full max-w-full object-contain transition-transform duration-300"
             style="transform: scale(1);">

        <!-- Close -->
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-3xl font-bold hover:text-gray-300 z-10">&times;</button>

        <!-- Prev -->
        <button id="lightbox-prev"
            class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white bg-black/30 hover:bg-black/50 p-2 rounded-full z-10">
            &#10094;
        </button>

        <!-- Next -->
        <button id="lightbox-next"
            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white bg-black/30 hover:bg-black/50 p-2 rounded-full z-10">
            &#10095;
        </button>

        <!-- Zoom -->
        <div class="absolute bottom-6 right-6 flex space-x-2 z-10">
            <button onclick="zoomIn()" class="bg-white text-black px-2 py-1 rounded shadow">+</button>
            <button onclick="zoomOut()" class="bg-white text-black px-2 py-1 rounded shadow">−</button>
            <button onclick="resetZoom()" class="bg-white text-black px-2 py-1 rounded shadow">Reset</button>
        </div>
    </div>
</div>


<script>
    const slider = document.getElementById('slider');
    const indicators = document.querySelectorAll('#indicators button');
    const slides = slider.children.length;
    let index = 0;

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
        btn.addEventListener('click', () => {
            showSlide(parseInt(btn.dataset.index));
        });
    });

    // Swipe support
    let startX = 0;
    slider.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
    });
    slider.addEventListener('touchend', (e) => {
        const endX = e.changedTouches[0].clientX;
        if (startX - endX > 50) index = (index + 1) % slides;
        else if (endX - startX > 50) index = (index - 1 + slides) % slides;
        showSlide(index);
    });

    showSlide(index);
</script>

<script>
    let currentIndex = 0;
    let zoomLevel = 1;
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');

    function openLightbox(i) {
        currentIndex = i;
        zoomLevel = 1;
        updateLightbox();
        lightbox.classList.remove('hidden');
    }

    function closeLightbox() {
        lightbox.classList.add('hidden');
        lightboxImage.src = '';
    }

    function updateLightbox() {
        lightboxImage.src = imageList[currentIndex];
        lightboxImage.style.transform = `scale(${zoomLevel})`;
    }

    function showNext() {
        currentIndex = (currentIndex + 1) % imageList.length;
        zoomLevel = 1;
        updateLightbox();
    }

    function showPrev() {
        currentIndex = (currentIndex - 1 + imageList.length) % imageList.length;
        zoomLevel = 1;
        updateLightbox();
    }

    function zoomIn() {
        zoomLevel += 0.2;
        lightboxImage.style.transform = `scale(${zoomLevel})`;
    }

    function zoomOut() {
        zoomLevel = Math.max(0.2, zoomLevel - 0.2);
        lightboxImage.style.transform = `scale(${zoomLevel})`;
    }

    function resetZoom() {
        zoomLevel = 1;
        lightboxImage.style.transform = `scale(1)`;
    }

    document.getElementById('lightbox-next').addEventListener('click', showNext);
    document.getElementById('lightbox-prev').addEventListener('click', showPrev);

    // Close on backdrop
    lightbox.addEventListener('click', e => {
        if (e.target === lightbox) closeLightbox();
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (lightbox.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
    });

    // Swipe support (mobile)
    lightbox.addEventListener('touchstart', e => startX = e.touches[0].clientX);
    lightbox.addEventListener('touchend', e => {
        const endX = e.changedTouches[0].clientX;
        if (endX - startX > 50) showPrev();
        else if (startX - endX > 50) showNext();
    });
</script>
