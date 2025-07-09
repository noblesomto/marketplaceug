@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm">
    <div class="border-b-2 border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        Buy Direct Ads
        @include('frontend.components.flash-message')
    </div>
    <div class="pb-10 mb-10">
        @if (!$buyAds->isEmpty())
          @foreach ($buyAds as $row)
              <a href="/advert/{{ $row->advert->id }}/{{ $row->advert->title_slug }}">
                  <div class="bg-white mb-1 border-b border-b-gray-300 shadow">
                     <div class="flex w-full">
                          <div class="w-2/6 mr-1 relative">
                            <img class="h-24 md:h-48 object-cover" src="{{  asset('uploads/images/'.$row->advert->firstImage->image) }}">
                            
                          </div>
                          <div class="w-4/6 relative ">
                            <div class="flex justify-between text-xs">
                              <div class="flex justify-start items-center text-sm md:mr-5">
                                <span class="mr-3 hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                </span>
                                <div>
                                  <span class="text-xs">{{ $row->advert->state }}</span> 
                                </div>
                                </div>

                              <div>
                                <div class="flex justify-start mr-5 text-xs md:mt-2">
                                  <span class="mr-3 hidden lg:block flex justify-start">Payment Date: 
                                </span>  <span class="text-xs" >{{ date('d.m.Y', strtotime($row->created_at)) }}</span></div> 
                              </div>
                            </div>
                            <div class="font-medium leading-5 md:font-bold text-base md:text-xl md:mt-2"> {{ Str::limit($row->advert->ad_title, 50) }}</div>
                            <div class="text-sm mt-2 hidden lg:block">{!! Str::limit($row->advert->description, 80) !!}</div>
                            <div class="flex justify-start items-center text-dark_green font-bold text-base my-1 lg:my-3">
                                <div class="mr-4">₦ {{ number_format($row->amount_paid, 0, '.', ',') }} </div>
                                <div class="capitalize px-3 py-0 lg:py-2 {{ $row->payment_status === 'paid' ? 'bg-green-200' : 'bg-yellow-200' }}">
                                    {{ $row->payment_status }}
                                </div>
                            </div>
                            <div class="text-base my-2 space-y-2">
                              <div>
                                  <h5 class="font-semibold text-sm">Shipping Method:</h5>
                                  <div class="flex items-center">
                                    <span><img class="w-16" src="{{  asset('uploads/shipping/'.$row->shipping->logo) }}"> </span>
                                    <span class="ml-2 text-sm font-bold">{{ $row->shipping->company }} </span>
                                  </div>
                              </div>
                              <div class="flex justify-start items-center text-sm">
                                <h5 class="font-semibold">Shipping Status:</h5>
                                @if($row->shipping_status=="delivered")
                                    <span class="ml-2 capitalize bg-green-100 p-2">{{ $row->shipping_status }} </span>
                                @elseif($row->shipping_status=="shipped")
                                    <span class="ml-2 capitalize bg-yellow-100 p-2">{{ $row->shipping_status }} </span>
                                @else
                                    <span class="ml-2 capitalize bg-red-100 p-2">{{ $row->shipping_status }} </span>
                                @endif
                              </div>
                             
                            </div>
                            @if($row->shipping_status=="shipped")
                            <div class="mb-2">
                                <p>Item Will be delivered within 5 - 14 working days</p>
                                <span>Updated: {{ date('d.m.Y', strtotime($row->shipping_status_date)) }}</span>
                            </div>
                            @endif
                            @if($row->shipping_status=="delivered")
                            <div class="mb-2">
                                <p>Package Delivered</p>
                                <span>Updated: {{ date('d.m.Y', strtotime($row->shipping_status_date)) }}</span>
                            </div>
                            @endif
                            <div>
                                
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