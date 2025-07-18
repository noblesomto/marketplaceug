@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="w-full lg:w-4/6 mx-auto bg-white md:bg-body pb-20">
  <div class="block lg:hidden bg-white pt-3 ml-2">
    <span class="text-base"><strong>Marketplace NG</strong> - where sellers meet real buyers</span>
  </div>

  <div class="my-5">
    @foreach(getAdverts() as $advert)
      <a href="{{ $advert->url }}" title="{{ $advert->company }}" target="_blank"><img class="object-cover w-full h-36 md:h-64" src="{{ asset('uploads/advertising/'.$advert->image) }}"></a>
    @endforeach
  </div>

  <div class="flex items-center justify-between px-2 block lg:hidden">
      <a class="mx-1" href="/category/vehicles">
        <div class="flex flex-col items-center">
          <div class="bg-primary w-10 h-10 rounded-full flex items-center justify-center mr-2">
            <img class="w-6" src="{{ asset('frontend/images/icons/car-100.png') }}">
          </div>
          <div class="text-xs">
              Vehicles
          </div>
        </div>
      </a>
      <a class="mx-1" href="/category/mobile-phones-and-tablets">
        <div class="flex flex-col items-center">
          <div class="bg-primary w-10 h-10 rounded-full flex items-center justify-center mr-2">
            <img class="w-6" src="{{ asset('frontend/images/icons/mobile-100.png') }}">
          </div>
          <div class="text-xs">
              Phones & Tablets
          </div>
        </div>
      </a>
      <a class="mx-1" href="/category/real-estate">
        <div class="flex flex-col items-center">
          <div class="bg-primary w-10 h-10 rounded-full flex items-center justify-center mr-2">
            <img class="w-6" src="{{ asset('frontend/images/icons/house-100.png') }}">
          </div>
          <div class="text-xs">
              Real Estate
          </div>
        </div>
      </a>
      <a class="mx-1" href="/category/fashion">
        <div class="flex flex-col items-center">
          <div class="bg-primary w-10 h-10 rounded-full flex items-center justify-center mr-2">
            <img class="w-6" src="{{ asset('frontend/images/icons/fashion-100.png') }}">
          </div>
          <div class="text-xs">
              Fashion & Beauty
          </div>
        </div>
      </a>
      <a class="mx-1" href="/all-categories">
        <div class="flex flex-col items-center">
          <div class="bg-primary w-10 h-10 rounded-full flex items-center justify-center mr-2">
            <img class="w-6" src="{{ asset('frontend/images/icons/list-100.png') }}">
          </div>
          <div class="text-xs">
              All Categories
          </div>
        </div>
      </a>
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
    <div class="grid grid-cols-2 gap-3 px-4">
        @foreach ($ads as $row)
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 h-full flex flex-col">
            <a href="{{ url('/advert/' . $row->id . '/' . $row->title_slug) }}" class="block group h-full flex flex-col">
                <!-- Image -->
                <div class="aspect-[4/3] w-full overflow-hidden">
                    <img 
                        src="{{ $row->firstImage ? asset('uploads/images/' . $row->firstImage->image) : asset('frontend/images/no-image.png') }}" 
                        alt="{{ $row->ad_title }}" 
                        class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105"
                        loading="lazy"
                    >
                </div>
                
                <!-- Content -->
                <div class="p-3 flex flex-col flex-grow">
                    <!-- Location & Sold Status -->
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-xs text-gray-500 truncate">{{ $row->state }}</span>
                        @if($row->sold=="Yes")
                        <span class="flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs" title="Sold">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Sold</span>
                        </span>
                        @endif
                    </div>
                    
                    <!-- Title -->
                    <h2 class="text-sm font-semibold text-gray-800 line-clamp-2 leading-tight mb-2">
                        {{ $row->ad_title }}
                    </h2>
                    
                    <!-- Price -->
                    <div class="mt-auto">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-bold text-green-600">
                                ₦{{ number_format($row->price, 0, '.', ',') }}
                            </span>
                            <span class="text-xs text-gray-500 truncate">
                                {{ $row->price_type }}
                            </span>
                        </div>
                        
                        <!-- Buy Direct Badge -->
                        @if($row->buy_direct=="Yes")
                        <div class="flex items-center mt-2 bg-blue-50 rounded-full px-2 py-1 w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-3 h-3 text-blue-600 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            <span class="text-xs text-blue-600">Buy Direct</span>
                        </div>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
  

  

</section>






@include('frontend.layouts.footer')


