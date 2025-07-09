@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="max-w-5xl mx-auto mt-10 hidden lg:block">
  <div class="grid grid-cols-3 gap-5">
      @foreach ($categories as $category)
          <div class="flex flex-col">
              <div>
                <a href="/category/{{ $category->id }}/{{ $category->category_slug }}"><h2 class="font-semibold text-base">{{ $category->category }}</h2></a>
              </div>
              <div>
                  @foreach ($category->subCategories as $subCategory)
                        <li class="ml-3 text-sm"><a href="/subcat/{{ $subCategory->id }}/{{ $subCategory->sub_cat_slug }}">{{ $subCategory->sub_category }}</a> </li> <!-- Subcategory name -->
                    @endforeach
              </div>
          </div>
      @endforeach
  </div>
</section>

<div class="bg-white block lg:hidden pb-20">
      <a href="/all-categories">
      <div class="flex justify-between items-center mt-6 mx-2 border-b border-b-gray-300 py-2">
        <div class="flex items-center">
            <div class="bg-primary w-10 h-10 rounded-full flex items-center justify-center mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            </div>
            <div>
                All Categories
            </div>
        </div>
        <div>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        </div>
  </div>
  </a>

  @foreach ($categories as $category)
      <a href="/m-category/{{ $category->id }}/{{ $category->category_slug }}">
          <div class="flex justify-between items-center mx-2 border-b border-b-gray-300 py-2">
              <div class="flex items-center">
                  <div class="bg-primary w-10 h-10 rounded-full flex items-center justify-center mr-2">
                      <img class="w-6" src="{{ asset('frontend/images/icons/' . $category->icon) }}">
                  </div>
                  <div>
                      {{ $category->category }}
                  </div>
              </div>
              <div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
              </svg>
              </div>
        </div>
      </a>
  @endforeach
  </div>

@include('frontend.layouts.footer')