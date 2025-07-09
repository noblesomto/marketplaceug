<section class="bg-white">
    <div class="flex justify-start items-center ml-2 my-1 text-gray-500">
    <span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-7.5A2.25 2.25 0 0 1 8.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 0 0-2.25 2.25v6" />
        </svg>
    </span>
    <span>gallery</span>
</div>

<div class="flex overflow-x-auto space-x-2 px-2  pb-5">
  <!-- Card 1 -->
  @foreach ( $featured as $row )
  <div class="flex-none w-32">
    <a href="/advert/{{ $row->id }}/{{ $row->title_slug }}">
        <img class="w-full h-28 md:h-32 object-cover transition duration-300 ease-in-out hover:scale-110" src="{{  asset('uploads/images/'.$row->firstImage->image) }}">
        <p class="text-sm text-gray-600 leading-4 mt-1">{{ Str::limit($row->ad_title, 15) }}</p>
        <div class="flex justify-between">
            <span class="text-xs font-semibold text-dark_green">₦ {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 10) }}
            </span>
        </div>
        @if($row->buy_direct=="Yes")
        <div class="flex items-center mt-1">
            <span class="mr-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 accent-bg_primary">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </span>
            <span>Buy Direct</span>
        </div>
        @endif
    </a>
  </div>
  @endforeach
    
  <!-- Add more cards as needed -->
</div>

</section>