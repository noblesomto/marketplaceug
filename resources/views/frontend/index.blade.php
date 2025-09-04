@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="w-full lg:w-4/6 mx-auto bg-white md:bg-body pb-20">
  <div class="block lg:hidden bg-white pt-3 ml-2">
    <div>
        <span class="text-base"><strong>Marketplace Naija</strong> - Buy. Sell. Secure Deals.</span>
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

 <div class="block lg:hidden mt-6 pb-20">
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


