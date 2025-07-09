
<div class="flex justify-start items-center bg-white h-16 py-2 border-solid border-b-8 border-b-primary block lg:hidden">
  <div class="mr-1 -ml-2 md:ml-0 md:mr-4">
    @include('frontend.components.mobile.mobile-side')
  </div>
  <div class="mx-1 md:mx-4">
    <a href="/"><img class="w-6 md:w-10" src="{{ asset('frontend/images/mobile-logo.png') }}"></a>
  </div>

  <div class="h-8 border-l-2 border-l-gray-400 pl-3 md:pl-10 ml-2 md:ml-5 pt-2">
      @include('frontend.components.mobile.mobile-search')
  </div>
</div>


