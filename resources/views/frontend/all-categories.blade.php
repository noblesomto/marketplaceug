@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')

<!-- Desktop Categories Grid (hidden on mobile) -->
<section class="max-w-7xl mx-auto px-4 py-8 hidden lg:block">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Browse Categories</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($categories as $category)
                <div class="group">
                    <a href="{{ url('/category/' . $category->category_slug) }}" class="block">
                        <h3 class="font-semibold text-lg text-gray-800 group-hover:text-primary transition-colors mb-3">
                            {{ $category->category }}
                        </h3>
                    </a>

                    <ul class="space-y-2">
                        @foreach ($category->subCategories as $subCategory)
                            <li>
                                <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}"
                                   class="text-gray-600 hover:text-primary text-sm transition-colors flex items-center">
                                    <span class="w-1.5 h-1.5 bg-gray-300 rounded-full mr-2"></span>
                                    {{ $subCategory->sub_category }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Mobile Categories List (hidden on desktop) -->
<div class="bg-white lg:hidden">
    <div class="max-w-md mx-auto px-4 pb-20">
        <!-- All Categories Header -->
        <a href="/all-categories" class="block group">
            <div class="flex justify-between items-center py-4 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="bg-secondary_dark  bg-opacity-50 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-dark_green" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </div>
                    <span class="font-medium text-gray-800 transition-colors">All Categories</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>

        <!-- Individual Categories -->
        @foreach ($categories as $category)
            <a href="{{ url('/category/' . $category->category_slug) }}" class="block group">
                <div class="flex justify-between items-center py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="bg-secondary_dark  bg-opacity-50 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                            <img class="h-5 w-5" src="{{ asset('frontend/images/icons/' . $category->icon) }}" alt="{{ $category->category }}">
                        </div>
                        <span class="text-gray-800 group-hover:text-primary transition-colors">
                            {{ $category->category }}
                        </span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        @endforeach

    </div>
</div>

@include('frontend.layouts.footer')
