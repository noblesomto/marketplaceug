@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')

<section class="w-full md:w-5/6 mx-auto mt-3">
  <div class="grid grid-cols-10 gap-3">
      <div class="col-span-2  hidden sm:block">
          @include('frontend.components.advert.side-advert')
      </div>
      <div class="col-span-10 md:col-span-6">
        <div class="grid grid-cols-10 gap-3">
           <div class="col-span-3 hidden sm:block bg-white p-2">
             @include('frontend.components.home.categories')
           </div>
           <div class="col-span-10 md:col-span-7">
              <div class=" my-5 hidden lg:block">
                 @include('frontend.components.advert.banner-advert')
              </div>

              <!-- Loading the Ads from Components -->
            <div id="advert-results" class="pb-20">
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





@include('frontend.layouts.footer')


