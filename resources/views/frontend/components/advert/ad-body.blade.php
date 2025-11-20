<div class="mb-2 md:my-2 w-full bg-white rounded p-2 md:p-5">
    @if ($ad->featured == 'Yes')
        <div class="bg-gray-50 inline px-2 py-1 mb-2">Promoted</div>
    @endif
    <div class="font-bold text-xl md:text-2xl flex">
        @if ($ad->sold == 'Yes') <span class="text-red-500 mr-1">Sold</span> @endif
        <span>{{ $ad->ad_title ?? '' }}</span>
    </div>

    @if($ad->category==3)
        <span class="text-dark_green font-bold text-lg md:text-xl my-2">{{ $ad->salary ?? '' }}</span>
    @elseif($ad->category==18)
        <span class="text-dark_green font-bold text-lg md:text-xl my-2">{{ $ad->expected_salary ?? '' }}</span>
    @elseif($ad->contact_price=="yes")
        <span class="text-dark_green font-bold text-lg md:text-xl my-2">Contact For Price</span>
    @else
        <div class="flex text-dark_green font-bold text-lg md:text-xl my-2">
            <div class="mr-4">₦ {{ number_format($ad->price ?? 0, 0, '.', ',') }}</div>
            <div>{{ $ad->price_type ?? '' }}</div>
        </div>
    @endif

    <div class="flex items-center text-xs md:text-sm mr-5">
        <span class="mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
        </span>
        <span>{{ $ad->lga ?? '' }}, {{ $ad->state ?? '' }}</span>
    </div>

    <div class="my-2 flex text-xs md:text-sm">
        <div class="flex mr-5"><span class="mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
            </svg>
        </span><span>{{ $ad->created_at ? date('j F Y', strtotime($ad->created_at)) : '' }}</span></div>
        <div class="flex items-center"><span class="mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
        </span><span>{{ $ad->views ?? 0 }}</span></div>
    </div>
</div>

<div class="my-2 w-full bg-white rounded p-5 flex">
    <div class="w-32 font-bold">Condition</div>
    <div>
        @if($ad->sub_category=="2") {{ $car->condition ?? '' }}
        @elseif($ad->sub_category=="6") {{ $phone->condition ?? '' }}
        @else {{ $ad->item_condition ?? '' }} @endif
    </div>
</div>

@if($ad->sub_category=="2")
<div class="my-2 w-full bg-white rounded p-2 md:p-5">
    <div class="text-sm grid grid-cols-6 md:gap-20">
        <div class="col-span-6 md:col-span-3">
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none">
                <div>Brand</div>
                <div>{{ optional($brand)->brand }}</div>
            </div>

            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Model</div><div>{{ optional($model)->model }}</div></div>
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Mileage</div><div>{{ $car->mileage ?? '' }}Km</div></div>
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Condition</div><div>{{ $car->condition ?? '' }}</div></div>
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Registration</div><div>{{ $car->registration ?? '' }}</div></div>
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Fuel</div><div>{{ $car->fuel ?? '' }}</div></div>
        </div>
        <div class="col-span-6 md:col-span-3">
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Gear</div><div>{{ $car->transmission ?? '' }}</div></div>
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Type</div><div>{{ $car->vehicle_type ?? '' }}</div></div>
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Doors</div><div>{{ $car->doors ?? '' }}</div></div>
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Color</div><div>{{ $car->exterior_color ?? '' }}</div></div>
            <div class="flex justify-between border-b border-gray-200 border-solid mb-1 py-1 lg:border-none"><div>Interior</div><div>{{ $car->material_interior ?? '' }}</div></div>
        </div>
    </div>
</div>

@php
$interiors = array_filter(array_map(fn($i)=>trim(str_replace(['/', '"', '\\', '[]'], '', $i)), explode(',', $car->interior ?? '')));
@endphp
@if(count($interiors))
<div class="my-4 bg-white rounded-lg p-4 md:p-6">
    <h2 class="text-lg font-semibold mb-2">Interior Features</h2>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3 text-sm">
        @foreach($interiors as $i)<div class="flex items-start">✔ <span class="ml-1 capitalize">{{ $i ?? '' }}</span></div>@endforeach
    </div>
</div>
@endif

@php
$exteriors = array_filter(array_map(fn($i)=>trim(str_replace(['/', '"', '\\', '[]'], '', $i)), explode(',', $car->exterior_equipment ?? '')));
@endphp
@if(count($exteriors))
<div class="my-2 bg-white rounded p-2 md:p-5">
    <div class="text-sm grid grid-cols-6 md:gap-2">
        @foreach($exteriors as $e)<div class="col-span-3 md:col-span-2 flex">✔ <span class="ml-1 capitalize">{{ $e ?? '' }}</span></div>@endforeach
    </div>
</div>
@endif
@endif

@if($ad->sub_category=="6")
<div class="my-2 bg-white rounded p-2 md:p-5">
    <div class="text-sm grid grid-cols-6 md:gap-20">
        <div class="col-span-6 md:col-span-3">
            <div class="flex justify-between"><div>Brand</div><div>{{ optional($brand)->brand }}</div></div>
            <div class="flex justify-between"><div>Accessories</div><div>{{ $phone->device ?? '' }}</div></div>
        </div>
        <div class="col-span-6 md:col-span-3">
            <div class="flex justify-between"><div>Color</div><div>{{ $phone->color ?? '' }}</div></div>
            <div class="flex justify-between"><div>Condition</div><div>{{ $phone->condition ?? '' }}</div></div>
        </div>
    </div>
</div>
@endif

<div class="my-2 bg-white rounded p-2 md:p-5">
    <div class="w-48 font-bold">Description</div>
    <div class="border border-gray-200 my-2"></div>
    <div class="text-sm leading-relaxed" style="word-break: break-word; overflow-wrap: anywhere;">
        {!! $ad->description ?? '' !!}
    </div>
</div>

@if ($ad->sold != 'Yes')
<div class="my-2 bg-white rounded p-5 hidden lg:block">
    @if(($cat->category ?? '') =="Jobs")
        <form method="POST" action="/apply/{{ $ad->id ?? '' }}">
            @csrf
            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 ">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Message</div>
                </div>
                <div class="col-span-10 md:col-span-8">
                    @if ($errors->has('message'))
                        <span class="text-red-400">{{ $errors->first('message') }}</span>
                    @endif
                <textarea type="text" name="message" rows="5" placeholder="Write a Friendly message to {{ $ad->owner->name }} and get a fast attention" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"  required></textarea>
                </div>

           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 ">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Profile name</div>
                </div>
                <div class="col-span-10 md:col-span-8">
                    @if ($errors->has('message'))
                        <span class="text-red-400">{{ $errors->first('message') }}</span>
                    @endif
                <input type="text" id="name" name="name" placeholder="Profile Name" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="@if(session()->get('user_id') != ''){{ $user->name }}@endif" readonly>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 ">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Phone</div>
                </div>
                <div class="col-span-10 md:col-span-8">
                    @if ($errors->has('phone'))
                        <span class="text-red-400">{{ $errors->first('phone') }}</span>
                    @endif
                <input type="text" id="name" name="phone" placeholder="Phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" >
                </div>
           </div>
            <p>Your data will be transmitted to the provider and automatically prefilled for future request</p>
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
    @else
        <a href="/chat/{{ $ad->id }}/{{ $ad->user_id }}" class="flex justify-start items-center btn btn-secondary w-52">
            <span class="mr-2">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
            </svg>
            </span>
            <span class="text-sm py-2">Write Message</span>
          </a>
    @endif
</div>
@endif
