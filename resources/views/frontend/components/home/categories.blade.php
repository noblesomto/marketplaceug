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
        <a href="/category/{{ $category->id }}/{{ $category->category_slug }}">
            <h2 class="font-semibold text-base">{{ $category->category }}</h2>
        </a>

        @php
            $subCategories = $category->subCategories;
            $limit = 2;
        @endphp

        <ul id="subcat-{{ $category->id }}" class="">
            @foreach ($subCategories->take($limit) as $subCategory)
                <li class="ml-3 text-sm">
                    <a href="/subcat/{{ $subCategory->id }}/{{ $subCategory->sub_cat_slug }}">{{ $subCategory->sub_category }}</a>
                </li>
            @endforeach

            @if ($subCategories->count() > $limit)
                @foreach ($subCategories->slice($limit) as $subCategory)
                    <li class="ml-3 text-sm hidden extra-{{ $category->id }}">
                        <a href="/subcat/{{ $subCategory->id }}/{{ $subCategory->sub_cat_slug }}">{{ $subCategory->sub_category }}</a>
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

<script>
    function toggleExtra(categoryId, button) {
        const items = document.querySelectorAll('.extra-' + categoryId);
        const isHidden = items[0]?.classList.contains('hidden');

        items.forEach(item => item.classList.toggle('hidden'));

        if (isHidden) {
            button.innerText = 'Show less';
        } else {
            button.innerText = 'Show more';
        }
    }
</script>
