@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="w-full md:w-5/6 mx-auto mt-3">
  <div class="grid grid-cols-10 gap-3">
      <div class="col-span-2 hidden sm:block">
        @include('frontend.components.advert.side-advert')
      </div>
      <div class="col-span-10 md:col-span-6">
        <div class="grid grid-cols-10 gap-3">
           <div class="col-span-3 hidden sm:block space-y-4">
              <div><h4 class="font-semibold">Categories</h4></div>

              <div class=" bg-white p-2 space-y-2">
                    <div class="mt-4">
                      <a class="text-xs" href="{{ url('/all-categories') }}">All Categories</a>
                  </div>

                  <div class="flex bg-gray-200 p-2 mt-1 mb-2">
                      <span class="font-semibold mr-2">{{ $cat->category }}</span>
                      <span>({{ $count_cat }})</span>
                  </div>
                  @php
                      $catLimit = 15;
                    @endphp
                  @foreach($categories->take($catLimit) as $subCategory)
                      <span class="space-y-1 mt-1">
                          <a class="" href="{{ url('/category/' . $cat->category_slug . '/' . $subCategory->sub_cat_slug) }}">
                          <div class="flex ml-3 mt-2">
                              <span class="mr-1">{{ $subCategory->sub_category }}</span>
                              <span>({{ $subCategory->advert_count }})</span>
                          </div>
                      </a>
                      </span>
                  @endforeach

                  @if($categories->count() > $catLimit)
                  <div class="ml-3 mt-2">
                    <a href="{{ url('/category/'.$cat->category_slug) }}"
                       class="text-dark_green text-sm hover:underline">
                      See all {{ $cat->category }}
                    </a>
                  </div>
                @endif
              </div>
              <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Locations</h4>
                <button id="locationButton" class="text-dark_green">Select Location</button>
              </div>

              <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Price</h4>
                @include('frontend.components.advert.price-filter')
              </div>


            <div class="bg-white p-2 space-y-2">
                <h4 class="font-semibold">Verified Sellers </h4>
                @include('frontend.components.advert.sellers-category')
            </div>


          </div>
           <div class="col-span-10 md:col-span-7">
              <div class=" my-5 hidden lg:block">
                 @include('frontend.components.advert.banner-advert')
              </div>
              <div class="block lg:hidden">
                    @include('frontend.components.mobile.filter-category')
              </div>
            <div id="advert-results">
                @include('frontend.components.advert.advert-list', ['ads' => $ads])
            </div>

           </div>
        </div>
      </div>
      <div class="col-span-2 hidden sm:block">
        @include('frontend.components.advert.side-advert')
      </div>
  </div>
</section>


@include('frontend.components.advert.modal-locations')


@include('frontend.layouts.footer')


