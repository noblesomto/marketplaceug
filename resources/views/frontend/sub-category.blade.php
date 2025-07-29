@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="w-full lg:w-5/6 mx-auto mt-3">
  <div class="grid grid-cols-10 gap-3">
      <div class="col-span-2 hidden lg:block">
        @include('frontend.components.advert.side-advert')
      </div>
      <div class="col-span-10 lg:col-span-6">
        <div class="grid grid-cols-10 gap-3">
           <div class="col-span-3 hidden lg:block space-y-4 pb-20">
            <div><h4 class="font-semibold">Categories</h4></div>

            <div class="mt-4">
              <a class="text-xs" href="/all-categories">All Categories</a>
            </div>

            <div class=" bg-white p-2 space-y-2">
                <!-- Category Sidebar -->
                <div class="flex bg-gray-200 p-2 mt-1 mb-2">
                  <span class="font-semibold mr-2">{{ $subcat->sub_category }}</span>
                  <span>({{ $count_subcat }})</span>
                </div>
                @php
                  $brandLimit = 15;
                @endphp

                @if($subcat->sub_category == "Cars" || $subcat->sub_category == "Mobile Phones")
                  @foreach($brands->take($brandLimit) as $brand)
                    <a href="{{ url('/category/' . $subcat->category->category_slug . '/' . $subcat->sub_cat_slug . '/' . $brand->brand_slug) }}">
                      <div class="flex ml-3 mt-1">
                        <span class="mr-1">{{ $brand->brand }}</span>
                        <span>({{ $brand->advert_count }})</span>
                      </div>
                    </a>
                  @endforeach
                @else
                  @foreach($brands->take($brandLimit) as $brand)
                    <a href="{{ url('/category/' . $subcat->category->category_slug . '/' . $subcat->sub_cat_slug . '/' . $brand->brand_slug) }}">
                        <div class="flex ml-3 mt-1">
                          <span class="mr-1">{{ $brand->brand }}</span>
                          <span>({{ $brand->advert_count }})</span>
                        </div>
                    </a>
                  @endforeach
                @endif

                @if($brands->count() > $brandLimit)
                  <div class="ml-3 mt-2">
                    <a href="{{ url('/category/' . $subcat->category->category_slug . '/' . $subcat->sub_cat_slug . '/all-'.$subcat->sub_cat_slug) }}"
                       class="text-dark_green text-sm hover:underline">
                      See all {{ $subcat->sub_category }}
                    </a>
                  </div>
                @endif
            </div>

            <div class="bg-white p-2 space-y-2 mt-4">
                <h4 class="font-semibold">Locations</h4>
                <button id="locationButton" class="text-dark_green">Select Location</button>
              </div>
            <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Price</h4>
                @include('frontend.components.advert.price-filter')
              </div>

            <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Verified Sellers </h4>
                @include('frontend.components.advert.sellers-subcategory')
            </div>

           </div>
           <div class="col-span-10 lg:col-span-7">
              <div class=" my-5 hidden lg:block">
                 @include('frontend.components.advert.banner-advert')
              </div>
              <div class="block lg:hidden">
                    @include('frontend.components.mobile.filter-subcategory')
              </div>
            <div id="advert-results">
                @include('frontend.components.advert.advert-list', ['ads' => $ads])
            </div>

           </div>
        </div>
      </div>
      <div class="col-span-2  hidden lg:block">
        @include('frontend.components.advert.side-advert')
      </div>
  </div>
</section>



@include('frontend.components.advert.modal-subcat-locations')

@include('frontend.layouts.footer')


