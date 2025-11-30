@include('dashboard.layouts.header')
@include('dashboard.layouts.back-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6  mx-auto  text-sm">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        My Wishlist
        @include('frontend.components.flash-message')
    </div>

    <div>
        @include('frontend.components.advert.advert-list', ['ads' => $favoriteAds])

    </div>

    
</section>


@include('dashboard.layouts.footer')
