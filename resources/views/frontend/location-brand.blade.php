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
            <!-- Main Sidebar Header -->
            <div class="flex items-center gap-2 px-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <h4 class="font-bold text-gray-800 text-lg tracking-tight">Brand Filters</h4>
            </div>

            <!-- Brand Context Card -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-50 bg-gray-50/50">
                    <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider">Current Selection</h4>
                </div>

                <div class="p-3">
                    <!-- Breadcrumb Navigation for SEO -->
                    <nav class="mb-3" aria-label="Breadcrumb">
                        <a href="{{ url('/all-categories') }}" class="inline-flex items-center text-xs font-medium text-dark_green hover:text-emerald-700 transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            All Categories
                        </a>
                    </nav>

                    <!-- Active Brand State -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase font-bold text-dark_green tracking-widest">Brand</span>
                            <span class="font-bold text-emerald-900 text-[15px] leading-tight">{{ $brand->brand }}</span>
                        </div>
                        <span class="text-xs font-bold bg-white text-emerald-700 px-2.5 py-1 rounded-full shadow-sm border border-emerald-100/50">
                            {{ $count_brand }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Location Selection Card -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider mb-3">Location</h4>
                <button id="locationButton" class="w-full flex items-center justify-between px-3 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all focus:ring-2 focus:ring-emerald-100 outline-none group">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="font-medium">Select Location</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300 group-hover:text-emerald-500 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <!-- Price Range Card -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider mb-3">Price Range</h4>
                <div class="filter-component-container">
                    @include('frontend.components.advert.price-filter')
                </div>
            </div>
        </div>
           <div class="col-span-10 md:col-span-7">
              <div class=" my-5 hidden lg:block">
                 @include('frontend.components.advert.banner-advert')
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


@include('frontend.components.advert.modal-brand-locations')


@include('frontend.layouts.footer')


