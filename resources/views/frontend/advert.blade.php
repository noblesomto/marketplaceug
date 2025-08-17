@include('frontend.layouts.header-adverts')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="w-full lg:w-4/6 mx-auto mb-20">
  <div class=" my-5 hidden lg:block">
   @include('frontend.components.advert.banner-advert')
  </div>


  <div class="grid grid-cols-6 gap-3">
        
        <div class="col-span-6 lg:col-span-4">
            <div class="bg-white p-2">@include('frontend.components.advert.slider')</div>
            <div>@include('frontend.components.advert.ad-body')</div>
        </div>
        <div class="col-span-6 lg:col-span-2">
          <div>@include('frontend.components.advert.sidebar')</div>
        </div>
  </div>

  <div class="max-w-4xl">
    <div>@include('frontend.components.advert.similar-ad')</div>
  </div>
</section>





@include('frontend.layouts.footer')


