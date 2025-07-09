@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm">
    <div class="border-b-2 border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        My Wishlist
        @include('frontend.components.flash-message')
    </div>
    <div>
        @if (!$favoriteAds->isEmpty())
          @foreach ($favoriteAds as $row)
              <a href="/advert/{{ $row->id }}/{{ $row->title_slug }}">
                  <div class="bg-white mb-1 border-b border-b-gray-300 shadow">
                     <div class="flex w-full">
                          <div class="w-2/6 mr-1 relative">
                            <img class="h-24 md:h-48 object-cover" src="{{  asset('uploads/images/'.$row->firstImage->image) }}">
                            <div class="absolute bottom-3 right-3 bg-black w-6 h-5 text-xs text-white flex justify-center items-center">{{ $row->images->count() }}</div>
                          </div>
                          <div class="w-4/6 relative">
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
                <span>No Posts here...</span>
                
            </div>
        @endif
    </div>

    
</section>


@include('dashboard.layouts.footer')