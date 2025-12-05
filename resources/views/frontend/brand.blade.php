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
            <div id="advert-results" class="pb-20">
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



@include('frontend.components.advert.modal-brand-locations')

@include('frontend.layouts.footer')


