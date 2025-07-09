<div class="md:flex md:justify-between bg-white md:bg-body mt-2 py-5 md:pb-1 px-3">
    <div class="font-bold text-xl">Other ads from the provider</div>
    <div class="text-dark_green hidden lg:block"><a href="">All Ads from this Poster</a> </div>
    <div class="border border-gray-200 my-2"></div>
</div>

<div class="bg-white p-2 md:p-5 -mt-5 md:mt-0">
    @if (!$adverts->isEmpty())
        @foreach ($adverts as $row)
        <a href="/advert/{{ $row->id }}/{{ $row->title_slug }}">
          <div class="bg-white mb-1 border-b border-b-gray-300">
             <div class="flex w-full">
                  <div class="w-1/4 mr-1 relative bg-gray-100">
                    <img class="w-full h-32 sm:h-40 object-contain rounded" 
                         src="{{ asset('uploads/images/'.$row->firstImage->image) }}" 
                         alt="{{ $row->ad_title }}">
                        @if($row->images->count() > 0)
                        <div class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-1 rounded">
                            {{ $row->images->count() }}+
                        </div>
                        @endif
                  </div>
                  <div class="w-3/4 relative">
                    <div class="flex justify-between text-xs">
                      <div class="flex justify-start items-center text-sm md:mr-5">
                        <span class="mr-3 hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        </span>
                        <div>
                          <span class="text-xs">{{ $row->state }}</span> 
                        </div>
                        </div>

                      <div>
                        <div class="flex justify-start mr-5 text-xs md:mt-2">
                          <span class="mr-3 hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        </span>  <span class="text-xs" >{{ date('d.m.Y', strtotime($row->created_at)) }}</span></div> 
                      </div>
                    </div>
                    <div class="font-medium leading-5 md:font-bold text-base md:text-xl md:mt-2"> {{ Str::limit($row->ad_title, 50) }}</div>
                    <div class="text-sm mt-2 hidden lg:block">{!! Str::limit($row->description, 80) !!}</div>
                    <div class="flex justify-start text-dark_green font-bold text-base my-2">
                      <div class="mr-4">₦ {{ number_format($row->price, 0, '.', ',') }} </div>
                      <div>{{ $row->price_type }}</div>
                    </div>
                    <div class="flex justify-start text-sm mt-2 absolute bottom-1">
                      @if($row->shippment=="Ship")
                      <span class="bg-gray-100 p-1 mr-2">Shipping Possible</span>
                      @endif
                    </div>
                  </div>
              </div>
          </div>
        </a>
        @endforeach
        @else
            <div class="flex flex-col items-center bg-white">
                <span>
                    <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="external-Empty-empty-state-(outline)-outline-andi-nur-abdillah"/>
                </span>
                <span>No Posts from Ad Owner...</span>
                
            </div>
        @endif
        @if($advertsCount > 6)
            <span class="my-2">
                <a class="text-dark_green font-semibold" href="/seller/{{ $ad->owner->user_id }}">View All Ads More from Seller ({{ $advertsCount }} Ads)</a>
            </span>
        @endif
</div>



<div class="md:flex justify-between mt-2 bg-white py-4 md:pb-1 px-3">
  <div class="font-bold text-xl">This might also interest you</div>
  <div class="border border-gray-200 my-2"></div>
</div>

<div class="bg-white p-2 md:p-5 -mt-5 md:mt-0">
   @if (!$similar_ads->isEmpty())
        @foreach ($similar_ads as $row)
        <a href="/advert/{{ $row->id }}/{{ $row->title_slug }}">
          <div class="bg-white mb-1 border-b border-b-gray-300">
             <div class="flex w-full">
                  <div class="w-1/4 mr-1 relative bg-gray-100">
                    <img class="w-full h-32 sm:h-40 object-contain rounded" 
                         src="{{ asset('uploads/images/'.$row->firstImage->image) }}" 
                         alt="{{ $row->ad_title }}">
                            @if($row->images->count() > 0)
                            <div class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-1 rounded">
                                {{ $row->images->count() }}+
                            </div>
                            @endif
                  </div>
                  <div class="w-3/4 relative">
                    <div class="flex justify-between text-xs">
                      <div class="flex justify-start items-center text-sm md:mr-5">
                        <span class="mr-3 hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        </span>
                        <div>
                          <span class="text-xs">{{ $row->state }}</span> 
                        </div>
                        </div>

                      <div>
                        <div class="flex justify-start mr-5 text-xs md:mt-2">
                          <span class="mr-3 hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        </span>  <span class="text-xs" >{{ date('d.m.Y', strtotime($row->created_at)) }}</span></div> 
                      </div>
                    </div>
                    <div class="font-medium leading-5 md:font-bold text-base md:text-xl md:mt-2"> {{ Str::limit($row->ad_title, 50) }}</div>
                    <div class="text-sm mt-2 hidden lg:block">{!! Str::limit($row->description, 80) !!}</div>
                    <div class="flex justify-start text-dark_green font-bold text-base my-2">
                      <div class="mr-4">₦ {{ number_format($row->price, 0, '.', ',') }} </div>
                      <div>{{ $row->price_type }}</div>
                    </div>
                    <div class="flex justify-start text-sm mt-2 absolute bottom-1">
                      @if($row->shippment=="Ship")
                      <span class="bg-gray-100 p-1 mr-2">Shipping Possible</span>
                      @endif
                    </div>
                  </div>
              </div>
          </div>
        </a>
        @endforeach
        @else
            <div class="flex flex-col items-center bg-white">
                <span>
                    <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="external-Empty-empty-state-(outline)-outline-andi-nur-abdillah"/>
                </span>
                <span>No Similar Ads...</span>
                
            </div>
        @endif
</div>