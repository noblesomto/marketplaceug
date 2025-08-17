<div class="md:flex md:justify-between bg-white md:bg-body mt-2 py-5 md:pb-1 px-3">
    <div class="font-bold text-xl">Other ads from the provider</div>
    <div class="text-dark_green hidden lg:block"><a href="">All Ads from this Poster</a> </div>
    <div class="border border-gray-200 my-2"></div>
</div>

<div class="-mt-5 md:mt-0">
    @include('frontend.components.advert.advert-list-fixed', ['ads' => $adverts])
    @if($advertsCount > 6)
        <span class="mb-4 -mt-20 w-full flex justify-end">
            <a class="text-dark_green font-semibold" href="/seller/{{ $ad->owner->user_id }}">View All Ads – Other Listings from this Seller  ({{ $advertsCount }} Ads)</a>
        </span>
    @endif
</div>



<div class="md:flex justify-between mt-2 bg-white py-4 md:pb-1 px-3">
  <div class="font-bold text-xl">This might also interest you</div>
  <div class="border border-gray-200 my-2"></div>
</div>

<div class=" -mt-5 md:mt-0">
    @include('frontend.components.advert.advert-list-fixed', ['ads' => $similar_ads])

</div>
