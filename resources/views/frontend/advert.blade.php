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
            <div class="hidden lg:block">
                <div class="my-2 flex justify-start gap-4 font-semibold ml-1 ">
                <span class="flex items-center space-x-2">
                    <a href="/">Home</a>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
                <span class="flex items-center space-x-2">
                    <a href="{{ url('/category/'.$cat->category_slug) }}">{{ $cat->category }}</a>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
                <span class="flex items-center space-x-2">
                    <a href="{{ url('/category/' . $cat->category_slug . '/' . $sub_cat->sub_cat_slug) }}">{{ $sub_cat->sub_category }}</a>
                </span>
            </div>
            </div>
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


