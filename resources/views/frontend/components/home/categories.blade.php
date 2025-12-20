<section class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 mb-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="font-bold text-xl text-gray-800">Popular Categories</div>
        <div>
            <a class="text-dark_green font-semibold text-sm hover:underline flex items-center" href="/all-categories">
                Show All
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 ml-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Grid Container -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 pb-10">
        @foreach ($categories as $category)
            <!-- Individual Category Card -->
            <div class="">
                <!-- Category Title & Icon -->
                <a href="{{ url('/category/' . $category->category_slug) }}" class="flex items-center mb-2 group">
                    <div class="bg-white rounded-lg p-2.5 shadow-sm flex items-center justify-center mr-1 group-hover:bg-dark_green group-hover:text-white transition-colors duration-200">

                        <img src="{{ asset('frontend/images/icons/' . $category->icon) }}"
                             alt="{{ $category->category }}"
                             class="w-4 h-4 object-contain group-hover:brightness-0 group-hover:invert transition-all duration-200">
                    </div>
                    <h2 class="font-bold text-gray-900 text-base group-hover:text-dark_green transition-colors">{{ Str::limit($category->category, 20) }}</h2>
                </a>

                @php
                    $subCategories = $category->subCategories;
                    $limit = 5;
                @endphp

                <!-- Subcategories List -->
                <ul id="subcat-{{ $category->id }}" class="space-y-2.5">
                    @foreach ($subCategories->take($limit) as $subCategory)
                        <li class="text-sm text-gray-600 hover:text-dark_green transition-colors pl-1">
                            <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}" class="flex items-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-2"></span>
                                {{ Str::limit($subCategory->sub_category, 20) }}
                            </a>
                        </li>
                    @endforeach

                    <!-- Hidden Items -->
                    @if ($subCategories->count() > $limit)
                        @foreach ($subCategories->slice($limit) as $subCategory)
                            <li class="text-sm text-gray-600 hover:text-dark_green transition-colors hidden extra-{{ $category->id }} pl-1">
                                <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}" class="flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-2"></span>
                                    {{ $subCategory->sub_category }}
                                </a>
                            </li>
                        @endforeach

                        <!-- Toggle Button -->
                        <li class="pt-1 pl-1">
                            <button
                                onclick="toggleExtra('{{ $category->id }}', this)"
                                class="text-xs font-bold text-dark_green hover:text-green-700  tracking-wide focus:outline-none flex items-center"
                            >
                                See all in {{ Str::limit($category->category, 20) }}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 font-semibold">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>

                            </button>
                        </li>
                    @endif
                </ul>
            </div>
        @endforeach
    </div>
</section>

<!-- Banner Section
<section class="mt-8">
    <div class="rounded-xl overflow-hidden shadow-sm">
        <img class="w-full object-cover" src="{{ asset('frontend/images/download-app.png') }}" alt="Download our App">
    </div>
</section>
-->
<script>
    function toggleExtra(categoryId, button) {
        const items = document.querySelectorAll('.extra-' + categoryId);
        // Check if currently hidden based on the first item
        const isHidden = items[0]?.classList.contains('hidden');

        items.forEach(item => item.classList.toggle('hidden'));

        // Update button text and icon rotation
        if (isHidden) {
            button.innerHTML = `Show less <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 ml-1 rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>`;
        } else {
            button.innerHTML = `Show more <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 ml-1"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>`;
        }
    }
</script>
