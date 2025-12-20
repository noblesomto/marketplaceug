<div class="flex justify-between bg-white border-b border-b-gray-400 mt-2 py-3 pb-1 px-3">
    <div class="font-bold text-base">Other Ads from this Seller</div>
    <div class="text-dark_green hidden lg:block"><a href="/seller/{{ $ad->owner->user_id }}/{{ $ad->id }}">All Ads from this Poster</a> </div>

</div>

<div class="mt-2  px-2">
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
        @foreach ($adverts as $row)
            @if($isMobile)
                @include('frontend.components.advert.advert-card-mobile', ['row' => $row])
            @else
                @include('frontend.components.advert.advert-card', ['ads' => $row])
            @endif
        @endforeach
    </div>

    @if($advertsCount > 6)
        <span class="my-4  w-full flex justify-end">
            <a class="text-dark_green font-semibold" href="/seller/{{ $ad->owner->user_id }}/{{ $ad->id }}">View all ads from this seller  ({{ $advertsCount }} Ads)</a>
        </span>
    @endif
</div>



<div class="md:flex justify-between mt-2 border-b border-b-gray-400 bg-white py-4 md:pb-1 px-3">
  <div class="font-bold text-base">Similar Ads</div>
</div>

<div class="mt-2 md:mt-0 p-2">
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
        @foreach ($similar_ads as $row)
            @if($isMobile)
                    @include('frontend.components.advert.advert-card-mobile', ['row' => $row])
                @else
                    @include('frontend.components.advert.advert-card', ['ads' => $row])
                @endif

        @endforeach
    </div>

</div>
<div class="pb-5"></div>
