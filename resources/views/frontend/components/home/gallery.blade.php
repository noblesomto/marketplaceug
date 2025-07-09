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
          <a href="/advert/{{ $row->id }}/{{ $row->title_slug }}">
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg border border-gray-200 h-full flex flex-col">
              <!-- Image container with fixed height -->
              <div class="relative h-32 md:h-36 overflow-hidden">
                <img class="w-full h-full object-cover" src="{{ asset('uploads/images/'.$row->firstImage->image) }}">
                <!-- Price badge - positioned absolutely within image container -->
                <div class="bg-primary h-6 absolute bottom-0 right-0 pl-2 pr-2 text-sm font-semibold ">
                  ₦ {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 1) }}
                </div>
              </div>
              
              <!-- Content container with consistent padding -->
              <div class="p-3 flex-grow flex flex-col">
                <h4 class="font-bold text-sm mb-1">{{ Str::limit($row->ad_title, 20) }}</h4>
                
                <div class="flex items-center justify-between text-xs mt-auto">
                  <span class="text-gray-400">{{ $row->state }}</span>
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
