<section class="p-3 bg-white">
    <div class="flex justify-between">
        <div class="font-bold">Categories</div>
        <div>
            <a class="text-dark_green text-sm" href="/all-categories">Show All</a>
        </div>
    </div>
    <div class="p-1 mt-4">
        @foreach ($categories as $category)
            <div class="border-b border-b-gray-300 pb-3 pt-1">
                <a href="{{ url('/category/' . $category->category_slug) }}" class="flex items-center">
                    <div class="bg-gray-100 rounded-md p-2 flex items-center justify-center mr-2">
                        <img src="{{ asset('frontend/images/icons/' . $category->icon) }}" alt="{{ $category->category }}" class="w-5 h-5">
                    </div>
                    <h2 class="font-semibold text-base">{{ $category->category }}</h2>
                </a>

                @php
                    $subCategories = $category->subCategories;
                    $limit = 2;
                @endphp

                <ul id="subcat-{{ $category->id }}">
                    @foreach ($subCategories->take($limit) as $subCategory)
                        <li class="ml-3 text-sm">
                            <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}">
                                {{ $subCategory->sub_category }}
                            </a>
                        </li>
                    @endforeach

                    @if ($subCategories->count() > $limit)
                        @foreach ($subCategories->slice($limit) as $subCategory)
                            <li class="ml-3 text-sm hidden extra-{{ $category->id }}">
                                <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}">
                                    {{ $subCategory->sub_category }}
                                </a>
                            </li>
                        @endforeach

                        <li class="ml-3 text-sm">
                            <button
                                onclick="toggleExtra('{{ $category->id }}', this)"
                                class="text-dark_green font-semibold hover:underline focus:outline-none"
                            >
                                Show more...
                            </button>
                        </li>
                    @endif
                </ul>
            </div>
        @endforeach
    </div>
</section>

<section class="mt-2">
    <img class="w-full" src="{{ asset('frontend/images/download-app.png') }}" alt="Download our App">
</section>

<script>
    function toggleExtra(categoryId, button) {
        const items = document.querySelectorAll('.extra-' + categoryId);
        const isHidden = items[0]?.classList.contains('hidden');

        items.forEach(item => item.classList.toggle('hidden'));

        button.innerText = isHidden ? 'Show less' : 'Show more...';
    }
</script>
