@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="w-full md:w-5/6 mx-auto mt-3">
  <div class="grid grid-cols-10 gap-3">
      <div class="col-span-2  hidden sm:block">
          @include('frontend.components.advert.side-advert')
      </div>
      <div class="col-span-10 md:col-span-6">
        <div class=" my-5 hidden lg:block">
                 @include('frontend.components.advert.banner-advert')
              </div>
        <div class="grid grid-cols-10 gap-3">
           <div class="col-span-3 hidden sm:block bg-white p-2">
             @include('frontend.components.advert.seller-profile')
           </div>
           <div class="col-span-10 md:col-span-7">


              <div class="mt-2 bg-white p-3 block lg:hidden">
             
                <div class="flex justify-start ">
                    <div class=" bg-gray-200 rounded-full py-4 px-4 mr-2 h-12">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-dark_green text-sm font-semibold"><a href="#">{{ $owner->name }} </a> </div>
                        <div class="flex justify-start items-center bg-purple-200 rounded-full px-2 py-1 text-xs mt-2">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                                </svg>
                            </span>
                            <span>Top Satisfaction</span>
                        </div>

                        <div class="flex justify-start items-center bg-purple-200 rounded-full px-2 py-1 text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                            </span>
                            <span>Particulary Friendly</span>
                        </div>

                        <div class="flex justify-start items-center bg-purple-200 rounded-full px-2 py-1 text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                </svg>
                            </span>
                            <span>Particulary Reliable</span>
                        </div>

                        <div class="flex justify-start items-center  rounded-full px-2 py-1 text-xs mt-2">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </span>
                            <span>{{ $owner->acc_type }} User</span>
                        </div>

                        <div class="flex justify-start items-center  rounded-full px-2 py-1 text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                                </svg>
                            </span>
                            <span>Active since {{ date('j F Y', strtotime($owner->created_at)) }}</span>
                        </div>

                        <div class="flex justify-start items-center  rounded-full px-2 py-1 text-xs mt-2">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                  <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
                                </svg>
                            </span>
                            <span>{{ countUserFollowers($owner->user_id) }} follower(s)</span>
                        </div>

                    </div>

                </div>
                <div class="border border-gray-200 my-2"></div>
                <div class="flex justify-between">
                    <div class="text-dark_green text-sm">{{ $count_ads }} ads online</div>
                    @if(session()->get('user_id') !='')

                    @if($owner->user_id == $user->user_id)

                    @else
                    <div>
                        <button 
                            id="followButton"
                            data-user-id="{{ $owner->user_id }}"
                            class="follow-button flex justify-start items-center w-full bg-transparent hover:bg-primary text-dark_green font-semibold hover:text-dark_green py-1 px-2 border border-dark_green hover:border-dark_green rounded-full">
                            <span class="mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                            </span>
                            <span>Follow</span>
                        </button>
                    </div>
                    @endif
                    @endif
                </div>
                <div class="border border-gray-200 my-2"></div>

            </div>
            @if (!$ads->isEmpty())
              @foreach ($ads as $row)
                <a href="/advert/{{ $row->id }}/{{ $row->title_slug }}">
                  <div class="bg-white mb-1 border-b border-b-gray-300">
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
                            <div class="flex justify-start text-sm my-3">
                              @if($row->shipment=="Ship")
                              <span class="bg-gray-100 p-1 mr-2">Shipping Possible</span>
                              @endif
                            </div>
                          </div>
                      </div>
                  </div>
                </a>
              @endforeach
              @else
            <div class="flex flex-col h-screen items-center bg-white">
                <span>
                    <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="external-Empty-empty-state-(outline)-outline-andi-nur-abdillah"/>
                </span>
                <span>No Item matches the Search...</span>
                
            </div>
        @endif

           </div>
        </div>
      </div>
      <div class="col-span-2 hidden sm:block">
        @include('frontend.components.advert.side-advert')
      </div>
  </div>
</section>





@include('frontend.layouts.footer')


