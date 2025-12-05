@include('frontend.layouts.header-location')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')

<section class="w-full max-w-[95rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-2">
      <div class="col-span-2  hidden sm:block">
          @include('frontend.components.advert.side-advert')
      </div>
      <div class="col-span-12 xl:col-span-8">
        <div class="grid grid-cols-12 gap-3">
           <div class="col-span-4 hidden sm:block bg-white p-2">
             @include('frontend.components.home.categories')
           </div>
           <div class="col-span-12 md:col-span-8">
              <div class=" my-5 hidden lg:block">
                 @include('frontend.components.advert.banner-advert')
              </div>

              <!-- Loading the Ads from Components -->
            <div id="advert-results" class="pb-20">
                @include('frontend.components.advert.advert-location', ['ads' => $ads])
            </div>




           </div>
        </div>
      </div>
      <div class="col-span-2 hidden sm:block">
        @include('frontend.components.advert.side-advert')
      </div>
  </div>
</section>





@include('frontend.layouts.footer')


