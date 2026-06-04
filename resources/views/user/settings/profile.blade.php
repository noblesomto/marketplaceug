@include('user.layouts.header')
@include('user.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('user.layouts.search')

<style>
/* ── Dashboard action cards ────────────────────────────────── */
.dash-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 20px 12px 16px;
    background: #fff;
    border-radius: 12px;
    border: 1.5px solid #e5e7eb;
    transition: all 0.2s ease;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}
.dash-card:hover {
    border-color: #326916;
    box-shadow: 0 6px 18px rgba(50, 105, 22, 0.12);
    transform: translateY(-3px);
    color: inherit;
    text-decoration: none;
}
.dash-card-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: #f0faf0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    font-size: 1.5rem;
    color: #326916;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.dash-card:hover .dash-card-icon {
    background: #326916;
    color: #fff;
}
.dash-card-title {
    font-weight: 600;
    font-size: 0.88rem;
    color: #1a1a2e;
    line-height: 1.3;
}
.dash-card-sub {
    font-size: 0.72rem;
    color: #9ca3af;
    margin-top: 3px;
    line-height: 1.4;
}

/* ── User profile strip (desktop only) ─────────────────────── */
.user-profile-strip {
    display: none;
    align-items: center;
    gap: 16px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 16px 20px;
    margin-bottom: 16px;
}
@media (min-width: 1024px) {
    .user-profile-strip { display: flex; }
}
.user-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: #e8f5e2;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: 700;
    color: #326916;
    flex-shrink: 0;
}
</style>

<section class="w-full md:max-w-2xl lg:max-w-4xl mx-auto px-3 mb-4 text-sm">

    {{-- User profile strip: visible on desktop only --}}
    <div class="user-profile-strip">
        <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="flex-1 min-w-0">
            <div class="font-bold text-gray-800 text-base truncate">{{ $user->name }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Member since {{ $user->created_at->format('M Y') }}</div>
        </div>
        <a href="/user/boosted"
           class="flex items-center gap-1.5 text-xs bg-dark_green text-white px-3 py-2 rounded-lg font-medium whitespace-nowrap">
            <i class="bi bi-rocket-takeoff"></i> Boosted Ads
        </a>
        <a href="/user/logout" title="Logout"
           class="flex items-center gap-1.5 text-sm text-gray-500 border border-gray-200 rounded-lg px-3 py-2 whitespace-nowrap"
           style="transition: color 0.15s">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>

    {{-- Dashboard header --}}
    <div class="bg-white rounded-t-xl border-b-2 border-gray-200 px-4 py-3 flex justify-between items-center">
        <span class="font-bold text-dark_green flex items-center gap-2 text-base">
            <i class="bi bi-grid-3x3-gap-fill"></i> My Dashboard
        </span>
        <div class="flex items-center gap-2 lg:hidden">
            <a href="/user/boosted"
               class="text-xs bg-dark_green text-white px-3 py-1.5 rounded-lg font-medium">
                Boosted Ads
            </a>
            <a title="Logout" href="/user/logout">
                <i class="bi bi-box-arrow-right text-xl text-gray-500"></i>
            </a>
        </div>
    </div>

    @include('public.components.flash-message')

    {{-- Dashboard action cards grid --}}
    <div class="bg-gray-50 rounded-b-xl border border-gray-200 border-t-0 p-4">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 lg:gap-4">

            <a href="/user/my-ads" class="dash-card">
                <div class="dash-card-icon"><i class="bi bi-badge-ad"></i></div>
                <div class="dash-card-title">My Adverts</div>
                <div class="dash-card-sub">Manage Adverts</div>
            </a>

            <a href="/user/payment" class="dash-card">
                <div class="dash-card-icon"><i class="bi bi-box2"></i></div>
                <div class="dash-card-title">Orders</div>
                <div class="dash-card-sub">Items you have ordered</div>
            </a>

            <a href="/seller/{{ \Illuminate\Support\Str::slug($user->name) }}/{{ $user->user_id }}" class="dash-card">
                <div class="dash-card-icon"><i class="bi bi-shop"></i></div>
                <div class="dash-card-title">My Marketplace</div>
                <div class="dash-card-sub">View and share store front</div>
            </a>

            <a href="/user/feedbacks" class="dash-card">
                <div class="dash-card-icon"><i class="bi bi-chat-right-dots"></i></div>
                <div class="dash-card-title">Reviews</div>
                <div class="dash-card-sub">View and get more Reviews</div>
            </a>

            <a href="/user/settings" class="dash-card">
                <div class="dash-card-icon"><i class="bi bi-gear"></i></div>
                <div class="dash-card-title">Account Settings</div>
                <div class="dash-card-sub">Verify phone and address</div>
            </a>

            <a href="/user/about-account" class="dash-card">
                <div class="dash-card-icon"><i class="bi bi-person-gear"></i></div>
                <div class="dash-card-title">About Account</div>
                <div class="dash-card-sub">More about my account</div>
            </a>

        </div>
    </div>

</section>

<section class="w-full md:max-w-2xl lg:max-w-4xl mx-auto px-3 py-3 text-sm">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2 rounded-lg">
        My Recent Listings
    </div>
    <div id="ads-container">
        @include('public.components.advert.advert-list', ['ads' => $ads])
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
@include('user.layouts.footer')
