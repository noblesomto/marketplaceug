@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm ">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2 flex justify-between">
        <span>Profile Section</span>
        <div class="space-x-4">
        	<a title="My Ad" href="/user/my-ads" ><i class="bi bi-badge-ad text-lg lg:text-3xl"></i></a>
            <a title="Purchase" href="/user/purchase" ><i class="bi bi-credit-card text-lg lg:text-3xl"></i></a>
            <a title="Feedbacks" href="/user/feedbacks" ><i class="bi bi-chat-right-dots text-lg lg:text-3xl"></i></a>
        	<a title="Settings" href="/user/settings"><i class="bi bi-gear text-lg lg:text-3xl"></i></a>
            <a title="Logout" href="/user/logout" ><i class="bi bi-box-arrow-right text-lg lg:text-3xl"></i></a>
        </div>
    </div>

    <div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 md:mt-10 mb-20 rounded-lg">
    	<div class="w-full bg-white shadow p-3">
			<div class="flex flex-col">
		        <div class="flex">
		        	@if($user->profile_picture=="")
		            	<img class="w-10 h-10 rounded-full" src="{{ asset('frontend/images/user.png') }}">
		            @else
		            	<img class="w-10 h-10 rounded-full" src="{{ asset('uploads/profile/'. $user->profile_picture) }}">
		            @endif
		        </div>
		        <div class="mt-2 ">
		  <div class="block lg:hidden">
		      <div class="w-48 font-bold">Profile</div>
		    <div class="border border-gray-200 my-2"></div>
		  </div>
		  <div class="flex justify-start ">
		    <div>
		      <div class="text-dark_green text-sm font-semibold">{{ $user->name }}</div>
              @if($user->verified=='yes')
                <div class="bg-green-100  flex space-x-2 py-1 px-2 rounded-full mt-2">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                          <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                          <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                        </svg>
                    </span>
                    <span class="text-xs">Verified Seller</span>
                </div>
            @endif
		      <div class="flex justify-start items-center bg-purple-200 rounded-full px-2 py-1 text-xs mt-2">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
		        </svg>
		        </span>
		        <span>Top Satisfaction</span> 
		      </div>

		      <div class="flex justify-start items-center bg-purple-200 rounded-full px-2 py-1 text-xs mt-1">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
		        </svg>
		        </span>
		        <span>Very Friendly</span>
		      </div>

		      <div class="flex justify-start items-center bg-purple-200 rounded-full px-2 py-1 text-xs mt-1">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
		        </svg>
		        </span>
		        <span>Very Reliable</span>
		      </div>

		      <div class="flex justify-start items-center  rounded-full px-2 py-1 text-xs mt-2">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
		        </svg>
		        </span>
		        <span>{{ $user->acc_type }} User</span> 
		      </div>

		      <div class="flex justify-start items-center  rounded-full px-2 py-1 text-xs mt-1">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
		        </svg>
		        </span>
		        <span>Active since {{ date('j F Y', strtotime($user->created_at)); }}</span> 
		      </div>

		    </div>

		  </div>
		  <div class="border border-gray-200 my-2"></div>
		  <div class="flex justify-between">
		    
		    @if($count_ads >= 1)
		    <a href="/user/my-ads">
		    	<div class="text-dark_green text-sm">{{ $count_ads }} ads online</div>
		    </a>
		    @else
		    <div class="flex-col items-center justify-center text-center ">
			    <img src="{{ asset('frontend/images/empty-box.png') }}" class="mx-auto mb-4">
			    <p class="font-semibold mb-2">No Ads at the moment</p>
			    <p class="text-gray-600 mb-4">
			        Post your ad now and easily manage everything in one place.
			    </p>
			    <a href="/user/post-ad" class="bg-primary py-2 text-xs lg:text-lg rounded-lg px-4 mt-4">Post an Ad</a>
			</div>
		    @endif

		  </div>

		</div>
		    </div>
		</div>
    </div>

</section>

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20 -mt-24">
    <div class="border-b-2 border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        My Adverts
        @include('frontend.components.flash-message')
    </div>
    <div class="pb-10 mb-10">
        @if (!$ads->isEmpty())
          @foreach ($ads as $row)
              <div class="bg-white mb-1 border-b border-b-gray-300 shadow p-2">
                 <div class="flex w-full">
                      <div class="w-2/6 mr-1 relative">
                        <img class="h-24 lg:h-40 object-cover" src="{{ $row->firstImage ? asset('uploads/images/' . $row->firstImage->image) : asset('frontend/images/default.png') }}">
                        <div class="absolute bottom-3 right-3 bg-black w-6 h-5 text-xs text-white flex justify-center items-center">{{ $row->images->count() }}</div>
                      </div>
                      <div class="w-4/6 relative space-y-2">
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
                        <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}">
                            <div class="font-medium leading-5 md:font-bold text-base md:text-xl md:mt-2"> {{ Str::limit($row->ad_title, 50) }}</div>
                        </a>
                        <div class="text-sm mt-2 hidden lg:block text-gray-600">
                            {!! Str::limit(strip_tags($row->description), 80) !!}

                        </div>
                        @if($row->category==3)
                        <div class="text-dark_green font-bold text-base my-2">
                            {{ $row->salary }}
                        </div>
                        @elseif($row->category==18)
                            <div class="text-dark_green font-bold text-base my-2">
                                {{ $row->expected_salary }}
                            </div>
                        @elseif($row->contact_price=="yes")
                            <div class="text-dark_green font-bold text-base my-2">
                                Contact For Price
                            </div>
                        @else
                        <div class="flex justify-start text-dark_green font-bold text-base my-2">
                          <div class="mr-4">₦ {{ number_format($row->price, 0, '.', ',') }} </div>
                          <div>{{ $row->price_type }}</div>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm mt-2 ">
                          @if($row->shipment=="Ship")
                          <span class="bg-gray-100 p-1 mr-2">Shipping Possible</span>
                          @endif
                        </div>
                        @if($row->sold=="Yes")
                          <div class="bg-gray-100 p-1 mr-2"><a href="/user/ad-shipping/{{ $row->id }}">Shipping Status</a> </div>
                        @endif
                      </div>
                  </div>
                  <div class="w-full">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-4">
                           @if($row->sold == "Yes")
                                <span class="flex items-center gap-2 bg-red-100 text-red-800 p-1 rounded cursor-not-allowed" title="This advert is already sold">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </span>
                                    <span>Sold</span>
                                </span>

                                <a class="flex items-center gap-2 bg-gray-100 p-1 rounded cursor-not-allowed" >
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </span>
                                <span>Edit Ad</span>
                            </a>
                            @if($row->featured=="Yes")
                                <a class="flex items-center gap-2 bg-green-300 p-1 rounded cursor-not-allowed" >
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                        </svg>
                                    </span>
                                    <span>Ad Boosted</span>
                            </a>
                            @else
                                <a class="flex items-center gap-2 bg-gray-100 p-1 rounded cursor-not-allowed" >
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                        </svg>
                                    </span>
                                    <span>Boost Ad</span>
                                </a>
                            @endif

                            <div >
                               @if($row->ad_status==1)
                                    <a title="Click to Change Status" class="flex items-center gap-2 bg-green-200 p-1 rounded cursor-not-allowed" >
                                        <span>Active Ad</span>
                                        <span class="">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                    </span>
                                    </a>
                                @else
                                    <a title="Click to Change Status" class="flex items-center gap-2 bg-red-200 p-1 rounded cursor-not-allowed" >
                                        <span>Disabled</span>
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                    </span>
                                    </a>
                                @endif

                            </div>

                            @else
                                <a class="flex items-center gap-2 bg-gray-100 p-1 rounded" href="/user/mark-sold/{{ $row->id }}" title="Mark Advert Sold" onclick="return confirm('Are you sure you want to Mark Advert Sold?');">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </span>
                                    <span>Mark Sold</span>
                                </a>

                                <a class="flex items-center gap-2 bg-gray-100 p-1 rounded" href="/user/edit-ad/{{ $row->id }}">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </span>
                                <span>Edit Ad</span>
                            </a>
                            @if($row->featured=="Yes")
                                <a class="flex items-center gap-2 bg-green-300 p-1 rounded" href="/user/boosted-ad/{{ $row->id }}">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                        </svg>
                                    </span>
                                    <span>Ad Boosted</span>
                            </a>
                            @else
                                <a class="flex items-center gap-2 bg-gray-100 p-1 rounded" href="/user/boost-ad/{{ $row->id }}">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                        </svg>
                                    </span>
                                    <span>Boost Ad</span>
                                </a>
                            @endif

                            <div >
                               @if($row->ad_status==1)
                                    <a title="Click to Change Status" class="flex items-center gap-2 bg-green-200 p-1 rounded" href="/user/ad-status/0/{{ $row->id }}">
                                        <span>Active Ad</span>
                                        <span class="">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                    </span>
                                    </a>
                                @else
                                    <a title="Click to Change Status" class="flex items-center gap-2 bg-red-200 p-1 rounded" href="/user/ad-status/1/{{ $row->id }}">
                                        <span>Disabled</span>
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                    </span>
                                    </a>
                                @endif

                            </div>
                            @endif



                        </div>
                  </div>
              </div>

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
