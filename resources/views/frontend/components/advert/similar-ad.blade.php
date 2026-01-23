@if($advertsCount > 0 || count($similar_ads) > 0)
    <!-- More from Seller -->
    @if($advertsCount > 0)
    <div class="mb-10 bg-white rounded-xl p-1 shadow-sm border border-gray-100">
        <div class="flex justify-between items-end mb-4 px-1 border-b pb-2">
            <h3 class="font-bold text-base lg:text-xl text-gray-900">
            More Ads from {{
                \Illuminate\Support\Str::ucfirst(
                    \Illuminate\Support\Str::lower(
                        \Illuminate\Support\Str::limit($ad->owner->name, 18)
                    )
                )
            }}
        </h3>

            <a href="/seller/{{ $ad->owner->user_id }}/{{ $ad->id }}" class="text-sm font-semibold text-dark_green hover:underline">View All</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
            @foreach ($adverts as $row)
                @if($isMobile)
                    @include('frontend.components.advert.advert-card-mobile', ['row' => $row])
                @else
                    @include('frontend.components.advert.advert-card', ['row' => $row])
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Similar Items -->
    @if(count($similar_ads) > 0)
    <div class="bg-white rounded-xl p-1 shadow-sm border border-gray-100">
        <h3 class="font-bold text-base lg:text-xl text-gray-900 mb-6 border-b pb-2">Recommended for you</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
            @foreach ($similar_ads as $row)
                @if($isMobile)
                        @include('frontend.components.advert.advert-card-mobile', ['row' => $row])
                    @else
                        @include('frontend.components.advert.advert-card', ['row' => $row])
                    @endif

            @endforeach
        </div>
    </div>
    @endif
@endif
