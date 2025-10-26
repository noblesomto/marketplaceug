
@if ($ad->sold == 'Yes')
   
@else
<div class="text-sm font-semibold lg:mt-10">
    @include('frontend.layouts.flash-message')
        @if($cat->category =="Jobs")
            <div class="mt-2">
                <button id="openModalJob"
                    class="flex justify-center items-center w-full btn btn-secondary font-semibold py-2">
                    <span class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>
                    </span>
                    <span>Apply</span>
                </button>
            </div>
        @else
            @if ($ad->buy_direct == 'Yes')
                <div class="w-full">
                    <a href="/buy-direct/{{ $ad->ad_id }}"
                        class="flex justify-center items-center bg-secondary_dark text-white rounded-lg  w-full py-2 px-4 ">
                        <span class="mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6 accent-bg_primary">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </span>
                        <span>Buy Directly</span>
                    </a>
                </div>
            @endif

            <div class="mt-2">
                <a href="/chat/{{ $ad->id }}/{{ $ad->user_id }}"
                    class="flex justify-center items-center w-full bg-transparent hover:bg-secondary_dark text-dark_green font-semibold hover:text-white  py-1 px-2 border-2 border-dark_green hover:border-dark_green rounded-lg">
                    <span class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>
                    </span>
                    <span>Write Message</span>
                </a>
            </div>
            @endif

            @if($ad->show_contact=="Yes")
            <div class="mt-2">
                <button id="showContact" 
                    class="flex justify-center items-center w-full bg-transparent hover:bg-secondary_dark  text-dark_green font-semibold hover:text-white  py-1 px-2 border-2 border-dark_green hover:border-dark_green rounded-lg">
                    <span class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                 </span>
                    <span>Show Contact</span>
                </button>
            </div>

            <div id="contactPhone" class="bg-white p-2 hidden">
                @if(session()->get('user_id') =='')
                    <div class="my-2 flex justify-center">
                        <span class="text-dark_green"> <a href="/login">Login to view Contact</a> </span>
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <span class="text-base">Phone:</span>
                        <span class="text-lg"> <a class="hover:text-dark_green hover:underline" href="tel:{{ $ad->owner->phone }}">{{ $ad->owner->phone }}</a> </span>
                    </div>
                @endif
            </div>
        @endif


    <div class="mt-2">
        <a href="/user/add-wishlist/{{ $ad->id }}" 
            class="flex justify-center items-center w-full bg-transparent hover:bg-secondary_dark  text-dark_green font-semibold hover:text-white  py-1 px-2 border-2 border-dark_green hover:border-dark_green rounded-lg">
            <span class="mr-2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="3" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
            </span>
            <span>Add to Wishlist</span>
        </a>
    </div>

    <div class="mt-2">
        <button id="openModalShare" 
            class="flex justify-center items-center w-full bg-transparent hover:bg-secondary_dark  text-dark_green font-semibold hover:text-white  py-1 px-2 border-2 border-dark_green hover:border-dark_green rounded-lg">
            <span class="mr-2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="3" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                </svg>

            </span>
            <span>Share Ad</span>
        </button>
    </div>
</div>
@endif

<div class="mt-2 bg-white p-3">
    <div class="block lg:hidden">
        <div class="w-48 font-bold">Provider</div>
        <div class="border border-gray-200 my-2"></div>
    </div>
    <div class="flex justify-start ">
        <a href="/seller/{{ $ad->owner->user_id }}">
            @if($ad->owner->profile_picture == "")
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                <span class="text-dark_green text-sm font-medium">
                    {{ strtoupper(substr($ad->owner->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $ad->owner->name)[1] ?? '', 0, 1)) }}
                </span>
            </div>
            @else
                <img class="w-12 h-12 rounded-full" src="{{ $ad->owner->profile_thumbnail_url }}">
            @endif
        </a>
        <div>
            <div class="text-dark_green text-sm font-semibold"><a href="/seller/{{ $ad->owner->user_id }}">{{ $ad->owner->name }} </a> </div>
            @if($ad->owner->verified=='yes')
                <div class="bg-green-100  flex space-x-2 py-1 px-2 rounded-lg mt-2">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                          <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                          <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                        </svg>
                    </span>
                    <span class="text-xs">Verified Seller</span>
                </div>
            @endif
            @php $labels = feedback_rating_labels($ad->owner->user_id); @endphp
                <div class="{{ $labels['satisfaction']['color'] }} flex justify-start items-center rounded-lg px-2 py-1 text-xs mt-1">
                    <span class="mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                        </svg>
                    </span>
                    <span>{{ $labels['satisfaction']['label'] }} Satisfied</span>
                </div>


                <div class="{{ $labels['friendly']['color'] }} flex justify-start items-center rounded-lg px-2 py-1 text-xs mt-1">
                    <span class="mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                    </span>
                    <span>{{ $labels['friendly']['label'] }} Friendly</span>
                </div>


                <div class="{{ $labels['reliable']['color'] }} flex justify-start items-center rounded-lg px-2 py-1 text-xs mt-1">
                    <span class="mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                        </svg>
                    </span>
                    <span>{{ $labels['reliable']['label'] }} Reliable</span>
                </div>

            <div class="flex justify-start items-center  rounded-lg px-2 py-1 text-xs mt-2">
                @if($ad->owner->acc_type=="Private")
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </span>
                @else
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>
                </span>

                @endif
                <span>{{ $ad->owner->acc_type }} User</span>
            </div>

            <div class="flex justify-start items-center  rounded-lg px-2 py-1 text-xs mt-1">
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                    </svg>
                </span>
                <span>Active since {{ date('j F Y', strtotime($ad->owner->created_at)) }}</span>
            </div>

            <div class="flex justify-start items-center  rounded-lg px-2 py-1 text-xs mt-1">
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
                <span>Last Seen:
                    @php
                        $lastSeen = \Carbon\Carbon::parse($ad->owner->last_login_at);
                        if ($lastSeen->isToday()) {
                            echo 'Today at ' . $lastSeen->format('g:i A');
                        } elseif ($lastSeen->isYesterday()) {
                            echo 'Yesterday at ' . $lastSeen->format('g:i A');
                        } else {
                            echo $lastSeen->diffForHumans();
                        }
                    @endphp
                    </span>
            </div>

            <div class="flex justify-start items-center  rounded-lg px-2 py-1 text-xs mt-2">
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                      <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
                    </svg>
                </span>
                <span>{{ countUserFollowers($ad->owner->user_id) }} follower(s)</span>
            </div>

        </div>

    </div>
    <div class="border border-gray-200 my-2"></div>
    <div class="flex justify-between">
        <a href="/seller/{{ $ad->owner->user_id }}"><div class="text-dark_green text-sm">{{ $count_ads }} ads online</div></a>
        @if(session()->get('user_id') !='')

        @if($ad->owner->user_id == $user->user_id)

        @else
        <div>
            <button 
                id="followButton"
                data-user-id="{{ $ad->owner->user_id }}"
                class="follow-button flex justify-start items-center w-full bg-transparent hover:bg-secondary_dark  text-dark_green font-semibold hover:text-dark_green py-1 px-2 border border-dark_green hover:border-dark_green rounded-lg">
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

</div>


<div class="my-2 w-full bg-white rounded p-5 flex justify-between text-sm">
    <div class="font-bold">Ad ID</div>
    <div>{{ $ad->ad_id }}</div>
</div>

<div class="my-2 w-full bg-white rounded p-5 flex justify-between text-sm text-dark_green">
    <div class="font-bold space-x-1">
        <span><i class="bi bi-chat-dots"></i></span>
       <span> {{ get_user_feedback_averages($ad->owner->user_id)['count'] }} Feedback(s)</span>
    </div>
    <div class="underline"><a href="/reviews/seller/{{ $ad->owner->user_id }}">View all</a> </div>
</div>


<div class="mt-5 w-full rounded p-2 flex justify-center text-sm text-dark_green font-semibold">
    <a href="/report-ad/{{ $ad->id }}" class="flex justify-center items-center">
        <span class="mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </span>
        <span>
            Report Ad
        </span>
    </a>
</div>


<div class="mt-5 w-full bg-white p-5">
    <h4 class="font-bold text-lg">Safety Guidelines</h4>
    @if($ad->category==3)
    <ul class="list-disc pl-5 space-y-2 mt-2">
        <li class="text-gray-800">Never pay to apply or attend an interview</li>
        <li class="text-gray-800">Research the company and verify their details beforehand</li>
        <li class="text-gray-800">Only attend interviews at official company addresses</li>
        <li class="text-gray-800">Share personal information only after receiving a confirmed job offer</li>
    </ul>
    @elseif($ad->category==11)
    <ul class="list-disc pl-5 space-y-2 mt-2">
        <li class="text-gray-800">Clearly define the work and payment terms in advance</li>
        <li class="text-gray-800">Review ratings and feedback to confirm reliability</li>
        <li class="text-gray-800">Arrange meetings only in safe, public locations</li>
        <li class="text-gray-800">Keep a record of all agreements and communications for future reference</li>
    </ul>
    @elseif($ad->category==18)
    <ul class="list-disc pl-5 space-y-2 mt-2">
        <li class="text-gray-800">Check the candidate’s background before arranging interviews</li>
        <li class="text-gray-800">Keep sensitive personal details private</li>
        <li class="text-gray-800">Avoid clicking on suspicious links outside the provided CV</li>
        <li class="text-gray-800">Hold interviews only in a secure, professional setting</li>
    </ul>
    @else
    <ul class="list-disc pl-5 space-y-2 mt-2">
        <li class="text-gray-800">Never pay upfront before inspecting the item.</li>
        <li class="text-gray-800">Meet in a safe, public location.</li>
        <li class="text-gray-800">Check the item carefully to ensure it matches the listing.</li>
        <li class="text-gray-800">Verify all documents and pay only when you're fully satisfied.</li>
        <li class="text-gray-800">Always use the <strong>"Buy Direct"</strong> option (if available) to enjoy <strong>100% Buyer Protection</strong>.</li>
    </ul>
    @endif
</div>

<div class="mt-2 hidden lg:block">
    <img class="w-full" src="{{ asset('frontend/images/download-app.png') }}" alt="Download our App">
</div>

<!--
<div class="my-2 w-full rounded p-2 flex justify-center text-sm text-dark_green font-semibold">
    <button class="flex justify-center items-center" onclick="window.print()">
        <span class="mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
            </svg>
        </span>
        <span>
            Print Ad
        </span>
    </button>
</div>
-->

<div id="jobModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden transition-opacity duration-300">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl relative transform transition-transform scale-95">
        <div class="flex justify-between">
            <span class="text-2xl font-semibold">Apply:</span>
            <button id="closeModalJob" class=text-grey-500 px-4 py-2 rounded ">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    <form method="POST" action="/apply/{{ $ad->id }}">
        @csrf
        <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 ">
            <div class="col-span-10 md:col-span-3">
                <div class="font-semibold">Write a Message</div>
            </div>
            <div class="col-span-10 md:col-span-7">
                @if ($errors->has('message'))
                    <span class="text-red-400">{{ $errors->first('message') }}</span>
                @endif
            <textarea type="text" name="message" rows="5" placeholder="Write a Friendly message to {{ $ad->owner->name }} and get a fast attention" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"  required></textarea>
            </div>

       </div>
       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3">
            <div class="col-span-10 md:col-span-3">
                <div class="font-semibold">Profile name</div>
            </div>
            <div class="col-span-10 md:col-span-7">
                @if ($errors->has('message'))
                    <span class="text-red-400">{{ $errors->first('message') }}</span>
                @endif
            <input type="text" id="name" name="name" placeholder="Profile Name" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="@if(session()->get('user_id') != ''){{ $user->name }}@endif" readonly>
            </div>
       </div>

       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 ">
            <div class="col-span-10 md:col-span-3">
                <div class="font-semibold">Phone</div>
            </div>
            <div class="col-span-10 md:col-span-7">
                @if ($errors->has('phone'))
                    <span class="text-red-400">{{ $errors->first('phone') }}</span>
                @endif
            <input type="text" id="name" name="phone" placeholder="Phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" >
            </div>
       </div>

       <div class="">
           <p>Your data will be transmitted to the provider and automatically prefilled for future request</p>
       </div>
       <div class="flex justify-end">
           <button
                class="flex justify-center items-center w-48 btn btn-secondary font-semibold py-2">
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                </span>
                <span>Apply</span>
            </button>
       </div>
   </form>


    </div>
</div>

<div id="shareModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden transition-opacity duration-300">
    <div class="bg-white rounded-lg p-6 w-full max-w-md relative transform transition-transform scale-95">
        <div class="flex justify-between">
            <span class="text-2xl font-semibold">Share:</span>
            <button id="closeModalShare" class="bg-red-500 text-white px-4 py-2 rounded">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="w-full space-x-4 flex justify-center items-center mt-10 pb-10">
            @include('frontend.components.social-share', [
            'url' => url()->current(),
            'title' =>$ad->ad_title,
        ])
      </div>

         
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jobOpenBtn = document.getElementById('openModalJob');
        const jobCloseBtn = document.getElementById('closeModalJob');
        const modal = document.getElementById('jobModal');

        jobOpenBtn.addEventListener('click', function () {
            modal.classList.remove('hidden');
        });

        jobCloseBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
        });

        // Optional: click outside modal to close
        window.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openBtn = document.getElementById('openModalShare');
        const closeBtn = document.getElementById('closeModalShare');
        const modal = document.getElementById('shareModal');

        openBtn.addEventListener('click', function () {
            modal.classList.remove('hidden');
        });

        closeBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
        });

        // Optional: click outside modal to close
        window.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const followButton = document.getElementById('followButton');
    
    if (!followButton) {
        console.error('Follow button not found');
        return;
    }

    const adOwnerId = followButton.dataset.userId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Initial check
    checkFollowingStatus();

    followButton.addEventListener('click', toggleFollow);

    async function checkFollowingStatus() {
        try {
            const response = await fetch(`/api/check-following/${adOwnerId}`, {
                headers: {
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                updateButtonUI(data.isFollowing);
            }
        } catch (error) {
            console.error('Error checking follow status:', error);
        }
    }

    async function toggleFollow() {
        try {
            const response = await fetch('/api/toggle-follow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    followee_id: adOwnerId
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                updateButtonUI(data.isFollowing);
            } else {
                const errorData = await response.json();
                alert(errorData.message || 'Error updating follow status');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update follow status');
        }
    }

    function updateButtonUI(isFollowing) {
        const iconSvg = isFollowing ? `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
            </svg>
        ` : `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
        `;

        followButton.innerHTML = `
            <span class="mr-2">${iconSvg}</span>
            <span>${isFollowing ? 'Unfollow' : 'Follow'}</span>
        `;

        if (isFollowing) {
            followButton.classList.add('bg-secondary_dark ', 'text-dark_green');
            followButton.classList.remove('hover:bg-secondary_dark ');
        } else {
            followButton.classList.remove('bg-secondary_dark ', 'text-dark_green');
            followButton.classList.add('hover:bg-secondary_dark ');
        }
    }
});
</script>

@if($ad->show_contact == 'Yes')
<script>
    document.getElementById('showContact').addEventListener('click', function() {
        const contactPhone = document.getElementById('contactPhone');
        contactPhone.classList.toggle('hidden');
        
        // Optional: Change the button text based on visibility
        const buttonText = this.querySelector('span:last-child');
        if (contactPhone.classList.contains('hidden')) {
            buttonText.textContent = 'Show Contact';
        } else {
            buttonText.textContent = 'Hide Contact';
        }
    });
</script>
@endif
