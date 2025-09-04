@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')

@php
    $count = getUserNotificationCount();
@endphp

<section class="w-full lg:w-4/6 mx-auto bg-white md:bg-body pb-20">
  <div class="block lg:hidden bg-white pt-3 ml-2 flex justify-between">
    <div>
        <span class="text-base"><strong>Marketplace Naija</strong> - Buy. Sell. Secure Deals.</span>
    </div>
    <div>
        <a href="/user/notifications">
            <div class="flex flex-col items-center mx-2 relative">
                <!-- Notification badge - hidden by default if count is 0 -->
                <div class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs" >
                    {{ $count }}
                </div>
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
            </div>
        </a>
    </div>
  </div>

  <div class="my-5">
   @include('frontend.components.advert.banner-advert')
</div>


  <div class="lg:hidden bg-white shadow-sm border-t border-gray-100">
  <div class="flex justify-between items-center py-2 px-1">
    <!-- Vehicles -->
    <a href="/category/vehicles" class="flex-1 flex flex-col items-center group text-center px-1">
      <div class="bg-primary w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
        <img class="w-6 h-6" src="{{ asset('frontend/images/icons/car-100.png') }}" alt="Vehicles">
      </div>
      <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
        Vehicles
      </span>
    </a>

    <!-- Phones & Tablets -->
    <a href="/category/mobile-phones-and-tablets" class="flex-1 flex flex-col items-center group text-center px-1">
      <div class="bg-primary w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
        <img class="w-6 h-6" src="{{ asset('frontend/images/icons/mobile-100.png') }}" alt="Phones & Tablets">
      </div>
      <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
        Phones &amp; Tablets
      </span>
    </a>

    <!-- Real Estate -->
    <a href="/category/real-estate" class="flex-1 flex flex-col items-center group text-center px-1">
      <div class="bg-primary w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
        <img class="w-6 h-6" src="{{ asset('frontend/images/icons/house-100.png') }}" alt="Real Estate">
      </div>
      <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
        Real Estate
      </span>
    </a>

    <!-- Fashion & Beauty -->
    <a href="/category/fashion" class="flex-1 flex flex-col items-center group text-center px-1">
      <div class="bg-primary w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
        <img class="w-6 h-6" src="{{ asset('frontend/images/icons/fashion-100.png') }}" alt="Fashion & Beauty">
      </div>
      <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
        Fashion &amp; Beauty
      </span>
    </a>

    <!-- All Categories -->
    <a href="/all-categories" class="flex-1 flex flex-col items-center group text-center px-1">
      <div class="bg-primary w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
        <img class="w-6 h-6" src="{{ asset('frontend/images/icons/list-100.png') }}" alt="All Categories">
      </div>
      <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
        All Categories
      </span>
    </a>
  </div>
</div>


  <div class="block lg:hidden mt-4">
    @include('frontend.components.mobile.mobile-gallery')
  </div>

  <div class="grid grid-cols-8 gap-3 ">
        <div class="col-span-2 hidden lg:block">@include('frontend.components.home.categories')</div>
        <div class="col-span-8 md:col-span-6 hidden lg:block">
            <div class="bg-white p-2">@include('frontend.components.home.gallery')</div>
            <div>@include('frontend.components.home.homeads')</div>
        </div>
  </div>

 <div class="block lg:hidden mt-6">
    <h4 class="font-semibold text-lg px-4 mb-3 text-gray-800">Recent Listings</h4>
    <div id="listings-container-mobile" class="grid grid-cols-2 gap-3 px-4">
        @foreach ($listings as $row)
            @include('frontend.components.advert.advert-card-mobile', ['row' => $row])
        @endforeach
    </div>
    <div id="loading-mobile" class="text-center py-4 hidden">Loading...</div>

    <!-- The sentinel (invisible div at bottom) -->
    <div id="load-more-trigger-mobile" class="h-1"></div>
</div>
  
</section>


<script src="{{ asset('frontend/js/scroll.js') }}"></script>
@include('frontend.layouts.footer')


