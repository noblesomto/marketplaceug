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
    <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}">
        <div class="relative overflow-hidden">
            <img class="w-full h-28 md:h-32 object-cover transition duration-300 ease-in-out hover:scale-110" src="{{ $row->firstImage ? asset('uploads/images/' . $row->firstImage->image) : asset('frontend/images/default.png') }}" onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';">
            <div class="absolute top-2 right-2 space-y-1">
                @if($row->owner->verified=='yes')
                    <div class="bg-green-50 opacity-8 flex space-x-2 py-1 px-2 rounded">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                              <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                              <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                            </svg>
                        </span>
                        <span class="text-xxs">Verified</span>
                    </div>
                @endif
                @if($row->views >= setViews())
                <div class="bg-white opacity-8 flex space-x-2 py-1 px-2">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                            <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                        </svg>
                    </span>
                    <span>Popular</span>
                </div>
                @endif
            </div>
            <div class="absolute top-0 left-2">
                @if ($row->featured == 'Yes')
                    <div class="bg-gray-50 inline-block px-2 py-1 transform rotate-90 origin-left">
                        Promoted
                    </div>
                @endif
            </div>

        </div>
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
