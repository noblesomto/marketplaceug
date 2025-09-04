<section class=" space-y-2 px-1">

@forelse($ads as $row)
    <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}">
      <div class="bg-white my-2 py-1 border-b-1 border-b-gray-300 h-[154px] sm:h-[170px] md:h-[190px]">
         <div class="flex w-full h-full">
              <div class="flex-[40%] xs:flex-[35%] sm:flex-[33%] mr-1 relative h-full p-2">
                <img class="w-full h-full object-cover" src="{{ $row->firstImage ? asset('uploads/images/' . $row->firstImage->image) : asset('frontend/images/default.png') }}" onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';">
                <div class="absolute top-2 right-2 space-y-2">
                    @if($row->owner->verified=='yes')
                        <div class="bg-green-50 opacity-8 flex space-x-2 py-1 px-2 rounded">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                                  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                                  <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                </svg>
                            </span>
                            <span class="text-xs">Verified</span>
                        </div>
                    @endif
                    @if($row->views >= setViews())
                    <div class="bg-white opacity-8 flex space-x-2 py-1 px-2">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                                <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                            </svg>
                        </span>
                        <span class="text-xs">Popular</span>
                    </div>
                    @endif
                </div>
                <div class="absolute top-0 left-3">
                    @if ($row->featured == 'Yes')
                        <div class="bg-gray-50 inline-block px-2 py-1 transform rotate-90 origin-left text-xs">
                            Promoted
                        </div>
                    @endif
                </div>
                <div class="absolute bottom-3 right-3 bg-black w-6 h-5 text-xs text-white flex justify-center items-center">{{ $row->images->count() }}</div>
              </div>
              <div class="flex-[60%] xs:flex-[65%] sm:flex-[67%] relative h-full overflow-hidden">
                <div class="flex justify-between text-xs">
                  <div class="flex justify-start items-center text-sm md:mr-5">
                    <div class="flex gap-2">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </span>
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
                <div class="font-semibold leading-5 md:font-bold text-sm md:text-base mt-1 line-clamp-2"> {{ Str::limit($row->ad_title, 50) }}</div>

                <div class="text-xs mt-1 hidden lg:block line-clamp-2">{!! Str::limit(strip_tags($row->description), 80) !!}</div>
                @if($row->category==3)
                    <div class="text-dark_green font-bold text-sm my-1">
                        {{ $row->salary }}
                    </div>
                    @elseif($row->category==18)
                        <div class="text-dark_green font-bold text-sm my-1">
                            {{ $row->expected_salary }}
                        </div>
                    @elseif($row->contact_price=="yes")
                        <div class="text-dark_green font-bold text-sm my-1">
                            Contact For Price
                        </div>
                    @else
                <div class="flex items-center justify-between text-xs mt-1">
                    <div class="flex justify-start text-dark_green font-bold text-sm my-1">
                      <div class="mr-2">₦ {{ number_format($row->price, 0, '.', ',') }} </div>
                      <div>{{ $row->price_type }}</div>
                    </div>
                </div>
                @endif

                <div class="mb-2 pb-4">
                    @if($row->sub_category==2)
                        <div class="flex-col space-y-2">
                            <span class="bg-gray-100 p-1 mr-2 text-xs">{{ $row->car->condition }} </span>
                            <div class="flex items-center">
                                <span class="bg-gray-100 p-1 mr-2 text-xs">{{ $row->car->registration }} </span>
                            </div>
                        </div>
                    @elseif($row->sub_category==6)
                        <div class="flex-col space-y-2 pb-2">
                            <span class="bg-gray-100 p-1 mr-2 text-xs">{{ $row->phone->condition }} </span>
                        </div>
                    @else
                        @if(!empty($row->item_condition))
                            <div class="flex-col space-y-2 pb-2">
                                <span class="bg-gray-100 p-1 mr-2 text-xs">{{ $row->item_condition }} </span>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between text-xs">
                    @if($row->shipment=="Ship")
                        <span class="bg-gray-100 p-1 text-xs">Shipping Possible</span>
                    @endif
                  @if($row->sold=="Yes")
                  <span class="flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded cursor-not-allowed mr-1 lg:mr-4 text-xs" title="This advert is already sold" >
                      <span>
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                          </svg>
                      </span>
                      <span class="font-semibold">Sold</span>
                  </span>
                  @else
                     @if($row->buy_direct=="Yes")
                          <div class="flex items-center bg-blue-50 rounded-full px-2 py-1 w-fit mr-1 lg:mr-4">
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                  stroke="currentColor" class="w-3 h-3 text-blue-600 mr-1">
                                  <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                              </svg>
                              <span class="text-xs text-blue-600">Buy Direct</span>
                          </div>
                      @endif
                  @endif
              </div>
              </div>
          </div>
      </div>
</a>
@empty
  <div class="flex flex-col  items-center bg-white p-10">
        <span>
            <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="No Adverts Currently"/>
        </span>
        <span>No Item here yet...</span>
    </div>
@endforelse


</section>
