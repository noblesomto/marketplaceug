<div class="flex justify-between h-16 px-5 -mb-4">
	<div class="flex items-center font-bold">
		Gallery
	</div>
	<div class="flex justify-end">
		<div class="text-dark_green text-sm font-semibold mr-3 flex items-center"><a href="/user/post-ad">Post Ad</a> </div>
		<div>
			 <!-- Navigation Buttons -->
			  <div class="flex justify-center mt-5 text-sm">
			    <button id="prevButton" class="text-dark_green px-3 py-1 rounded-full border-2 border-dark_green mr-3">
			    	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="5" stroke="currentColor" class="size-3 font-bold">
					  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
					</svg>

			    </button>
			    <button id="nextButton" class="text-dark_green px-3 py-1 rounded-full border-2 border-dark_green">
			    	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="5" stroke="currentColor" class="size-3">
					  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
					</svg>

			    </button>
			  </div>
		</div>
	</div>
</div>

<div class="container mx-auto">
  <!-- Slider Container -->
  <div class="relative overflow-hidden">
    <!-- Cards Wrapper -->
    <div id="cardSlider" class="flex transition-transform duration-500">
      <!-- Cards -->
      @foreach ( $featured as $row )
        <div class="flex-none w-2/4 md:w-2/4 lg:w-2/4 2xl:w-1/4 p-1">
          <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}">
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg border border-gray-200 h-full flex flex-col">
              <!-- Image container with fixed height -->
              <div class="relative h-32 md:h-36 overflow-hidden">
                <img class="w-full h-full object-cover" src="{{ $row->hasMedia('images') ? $row->getFirstMediaUrl('images', 'thumbnail') : asset('frontend/images/default.png') }}" onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';">
                <div class="absolute top-1 right-1 space-y-1">
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

                <!-- Price badge - positioned absolutely within image container -->
                @if($row->category==3)
                    <div class="bg-primary h-6 absolute bottom-0 right-0 pl-2 pr-2 text-sm font-semibold ">
                        {{ $row->salary }}
                    </div>
                    @elseif($row->category==18)
                        <div class="bg-primary h-6 absolute bottom-0 right-0 pl-2 pr-2 text-sm font-semibold ">
                            {{ $row->expected_salary }}
                        </div>
                    @elseif($row->contact_price=="yes")
                        <div class="bg-primary h-6 absolute bottom-0 right-0 pl-2 pr-2 text-sm font-semibold ">
                            Contact For Price
                        </div>
                    @else
                <div class="bg-primary h-6 absolute bottom-0 right-0 pl-2 pr-2 text-sm font-semibold ">
                  ₦ {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 1) }}
                </div>
                @endif
              </div>

              <!-- Content container with consistent padding -->
              <div class="p-3 flex-grow flex flex-col">
                <h4 class="font-bold text-sm mb-1">{{ Str::limit($row->ad_title, 20) }}</h4>

                <div class="flex items-center justify-between text-xs mt-auto">
                  <div class="flex justify-start items-center text-sm md:mr-2">
                    <span class="mr-1"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    </span>
                    <div>
                      <span class="text-xs">{{ $row->state }}</span>
                    </div>
                    </div>
                  @if($row->sold=="Yes")
                  <span class="flex items-center gap-2 bg-red-100 text-red-800 p-1 rounded cursor-not-allowed" title="This advert is already sold">
                    <span>
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                    </span>
                    <span class="font-semibold">Sold</span>
                  </span>
                  @endif
                </div>

                <div class="flex justify-between mt-2">
                    @if($row->buy_direct=="Yes")
                    <div class="flex items-center mt-2">
                      <span class="mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                          stroke="currentColor" class="size-4 accent-bg_primary">
                          <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                      </span>
                      <span class="text-xs">Buy Direct</span>
                    </div>
                    @endif

                </div>

              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</div>


<script>
  const slider = document.getElementById('cardSlider');
  const prevButton = document.getElementById('prevButton');
  const nextButton = document.getElementById('nextButton');
  let currentIndex = 0;

  function updateSliderPosition() {
    const cardWidth = slider.querySelector('div').offsetWidth;
    slider.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
  }

  prevButton.addEventListener('click', () => {
    if (currentIndex > 0) {
      currentIndex--;
      updateSliderPosition();
    }
  });

  nextButton.addEventListener('click', () => {
    if (currentIndex < slider.children.length - 1) {
      currentIndex++;
      updateSliderPosition();
    }
  });

  window.addEventListener('resize', updateSliderPosition); // Adjust on resize
</script>
