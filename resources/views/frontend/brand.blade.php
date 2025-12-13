@include('frontend.layouts.header-brand')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="w-full lg:max-w-[95rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-3">
      <div class="col-span-2 hidden lg:block">
        @include('frontend.components.advert.side-advert')
      </div>
      <div class="col-span-12 lg:col-span-8">
        <div class="grid grid-cols-12 gap-3">
           <div class="col-span-3 hidden lg:block space-y-4">
            <div><h4 class="font-semibold">Filter</h4></div>
            <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Locations</h4>
                <button id="locationButton" class="text-dark_green">Select Location</button>
            </div>
            <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Price</h4>
                @include('frontend.components.advert.price-filter-brand')
            </div>

            <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Buy Dircetly</h4>
                @include('frontend.components.filter.buydirect-brand')
            </div>

            <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Verified Sellers </h4>
                @include('frontend.components.advert.sellers-brand')
            </div>

           </div>
           <div class="col-span-12 lg:col-span-9">
              <div class=" my-5 hidden lg:block">
                 @include('frontend.components.advert.banner-advert')
              </div>
              <div class="block lg:hidden">
                    @include('frontend.components.mobile.filter-brand')
              </div>
            <div id="ads-container" class="">
                @include('frontend.components.advert.advert-list', ['ads' => $ads])
            </div>
            @if($ads->isEmpty())
                <div class="flex flex-col h-screen items-center bg-white p-10">
                    <span>
                        <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="No Adverts Currently"/>
                    </span>
                    <span>No Item here yet...</span>
                </div>
            @endif


            @if(isset($hasMore) && $hasMore)
                <div class="mt-3 mb-4 px-2 flex justify-center pb-20">
                    <button id="load-more-btn"
                            class="bg-secondary_dark hover:bg-secondary_dark text-white font-semibold py-3 px-8 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full">
                        <span id="load-more-text">Show More</span>
                        <span id="load-more-spinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>
            @endif

           </div>
        </div>
      </div>
      <div class="col-span-2  hidden lg:block">
        @include('frontend.components.advert.side-advert')
      </div>
  </div>
</section>
<div class="pb-5"></div>


@include('frontend.components.advert.modal-brand-locations')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 2; // Start from page 2 since page 1 is already loaded
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('ads-container');

    // Get current filters from URL or other source
    const filters = {
        @if(isset($cat))
        category: {{ $cat->id }},
        @endif
        @if(isset($subcat))
        sub_category: {{ $subcat->id }},
        @endif
        @if(isset($brand))
        brand: {{ $brand->id }},
        @endif
    };

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // Disable button and show spinner
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');

            // Build query string
            const params = new URLSearchParams({
                page: currentPage,
                ...filters
            });

            fetch(`{{ route('adverts.loadMore') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    // Append new ads
                    adsContainer.insertAdjacentHTML('beforeend', data.html);

                    // Increment page
                    currentPage++;

                    // Hide button if no more ads
                    if (!data.hasMore) {
                        loadMoreBtn.style.display = 'none';
                    }

                    // Re-enable button
                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error loading more ads:', error);
                    alert('Failed to load more ads. Please try again.');

                    // Re-enable button
                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                });
        });
    }
});
</script>
@include('frontend.layouts.footer')


