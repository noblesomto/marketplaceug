@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6  mx-auto  text-sm ">


    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green flex justify-between items-center rounded-lg">
        <span>My Dashboard</span>
        <div class="flex items-center space-x-4">

            <a title="Logout" href="/user/logout" ><i class="bi bi-box-arrow-right text-lg lg:text-2xl"></i></a>
        </div>
    </div>

    <div class="w-full space-y-2 px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
        <a href="/user/my-ads"
            class="flex justify-start items-center  w-full py-2 space-x-2 text-lg  border-b border-b-gray-300 ">
            <span class="mr-2">
                <i class="bi bi-badge-ad "></i>
            </span>
            <div class="flex flex-col">
                <span class="font-semibold">My Adverts</span>
                <span class="text-sm">Manage Adverts</span>
            </div>
        </a>

        <a href="/user/payment"
            class="flex justify-start items-center  w-full py-2 space-x-2 text-lg  border-b border-b-gray-300">
            <span class="mr-2">
                <i class="bi bi-box2 "></i>
            </span>
            <div class="flex flex-col">
                <span class="font-semibold">Orders</span>
                <span class="text-sm">Items you have ordered</span>
            </div>
        </a>

        <a href="/user/feedbacks"
            class="flex justify-start items-center  w-full py-2 space-x-2 text-lg  border-b border-b-gray-300">
            <span class="mr-2">
                <i class="bi bi-chat-right-dots"></i>
            </span>
            <div class="flex flex-col">
                <span class="font-semibold">Reviews</span>
                <span class="text-sm">View and get more Reviews</span>
            </div>
        </a>
        <a href="/user/settings"
            class="flex justify-start items-center  w-full py-2 space-x-2 text-lg  border-b border-b-gray-300">
            <span class="mr-2">
                <i class="bi bi-gear"></i>
            </span>
           <div class="flex flex-col">
                <span class="font-semibold">Account Settings</span>
                <span class="text-sm">Verify phone number and address</span>
            </div>
        </a>

        <a href="/user/about-account"
            class="flex justify-start items-center  w-full py-2 space-x-2 text-lg  border-b border-b-gray-300">
            <span class="mr-2">
                <i class="bi bi-person-gear"></i>
            </span>
            <div class="flex flex-col">
                <span class="font-semibold">About Account</span>
                <span class="text-sm">More about my account</span>
            </div>
        </a>
        </div>
    </div>


</section>

<section class="w-full md:w-3/6  mx-auto py-3 text-sm">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2 rounded-lg">
        my Recent Listings
        @include('frontend.components.flash-message')
    </div>
    <div id="ads-container">
        @include('frontend.components.advert.advert-list', ['ads' => $ads])
    </div>
    @if(isset($hasMore) && $hasMore)
        <div class="mt-3 mb-4 px-2 flex justify-center">
            <button id="load-more-btn"
                    class="bg-dark_green hover:bg-secondary_dark text-white font-semibold py-3 px-8 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full">
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

</section>
<div class="pb-20"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 2; // Start from page 2 since page 1 is already loaded
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('ads-container');

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // Disable button and show spinner
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');

            fetch(`{{ route('user.myads.loadMore') }}?page=${currentPage}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

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
@include('dashboard.layouts.footer')
