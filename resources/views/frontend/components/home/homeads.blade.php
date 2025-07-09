<div class="flex justify-between h-16 px-5 -mb-4 ">
	<div class="flex items-center font-bold">
		Recent Listings
	</div>
	<div class="flex justify-end">
		<div class="text-dark_green text-sm font-semibold mr-3 flex items-center"><a href="/user/post-ad">Place Ad Here</a> </div>
	
	</div>
</div>

<div class="container mx-auto">
	<div class="container mx-auto px-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($ads as $row)
            <a href="/advert/{{ $row->id }}/{{ $row->title_slug }}" class="group">
                <div class="h-full flex flex-col">
                    <div class="bg-white rounded-lg shadow-md group-hover:shadow-lg border border-gray-200 flex flex-col h-full">
                        <!-- Image wrapper with fixed aspect ratio -->
                        <div class="w-full aspect-[4/3] overflow-hidden rounded-t-lg">
                            <img 
                                src="{{ asset('uploads/images/' . $row->firstImage->image) }}" 
                                alt="{{ $row->ad_title }}" 
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null;this.src='{{ asset('images/default.jpg') }}';"
                            />
                        </div>

                        <!-- Price tag -->
                        <div class="relative -mt-6 mb-2 mr-2 w-full">
                            <div class="bg-primary h-8 px-2 text-sm font-semibold inline-flex items-center float-right">
                                ₦ {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 1) }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-3 flex flex-col flex-grow">
                            <h4 class="font-bold text-sm mb-2">{{ Str::limit($row->ad_title, 20) }}</h4>
                            
                            @if($row->buy_direct=="Yes")
                            <div class="flex items-center mt-2 bg-blue-50 rounded-full px-2 py-1 w-fit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-3 h-3 text-blue-600 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                                <span class="text-xs text-blue-600">Buy Direct</span>
                            </div>
                            @endif
                            
                            <div class="flex items-center justify-between text-xs mt-auto">
                                <span class="text-gray-500 truncate">{{ $row->state }}</span>
                                @if($row->sold=="Yes")
                                <span class="flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded cursor-not-allowed" title="This advert is already sold">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </span>
                                    <span class="font-semibold text-xs">Sold</span>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>


  <!-- Slider Container -->
  <div class="relative overflow-hidden">
    <!-- Cards Wrapper -->
    <div id="cardSlider" class="flex transition-transform duration-500">
     
      <!-- Add more cards as needed -->
    </div>
  </div>

 
</div>

