<div class="mb-2 md:my-2  w-full bg-white rounded p-2 md:p-5">
  <div class="font-bold text-xl md:text-2xl flex">
    @if ($ad->sold == 'Yes')
    <span class="text-red-500 mr-1">Sold -  </span>
    @endif
    <span> {{ $ad->ad_title }}</span>
  </div>
  @if($cat->category !="Jobs")
  <div class="flex justify-start text-dark_green font-bold text-lg md:text-xl my-2">
    <div class="mr-4">₦ {{ number_format($ad->price, 2, '.', ',') }} </div>
    <div>{{ $ad->price_type }}</div>
  </div>
  @endif
  <div class="flex justify-start items-center text-xs md:text-sm mr-5">
    <span class="mr-3"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
</svg>
</span>
<span>{{ $ad->lga }} - {{ $ad->state }}</span> 
</div>

  <div class="my-2 flex justify-start text-xs md:text-sm">
    <div class="flex justify-start mr-5"><span class="mr-3"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
</svg>
</span>  <span>{{ date('j F Y', strtotime($ad->created_at)); }}</span></div>   

<div class="flex justify-start items-center"><span class="mr-2"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
</svg>
</span> <span>{{ $ad->views }}</span> </div>
  </div>



</div>


<div class="my-2 w-full bg-white rounded p-5 flex justify-start">
    <div class="w-32 font-bold">Type</div>
    <div>{{ $cat->category }}</div>
</div>

@if($ad->sub_category=="2")
<div class="my-2 w-full bg-white rounded p-2 md:p-5">
    <div class="block lg:hidden">
      <div class="w-48 font-bold">Details</div>
    <div class="border border-gray-200 my-2"></div>
    </div>
    <div class="text-sm leading-relaxed">
        <div class="grid grid-cols-6 md:gap-20">
          <div class="col-span-6 md:col-span-3">
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">brand</div>
              <div>{{ $brand->brand }}</div>
            </div>
            @isset($model->model)
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Model</div>
              <div>{{ $model->model }}</div>
            </div>
            @endisset
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Mileage</div>
              <div>{{ $car->mileage }}Km</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Vehicle Condition</div>
              <div>{{ $car->condition }}</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Initial Registration</div>
              <div>{{ $car->registration }}</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Fuel Type</div>
              <div>{{ $car->fuel }}</div>
            </div>
          </div>

          <div class="col-span-6 md:col-span-3">
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Gear Box</div>
              <div>{{ $car->transmission }}</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Vehicle Type</div>
              <div>{{ $car->vehicle_type }}</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Number of doors</div>
              <div>{{ $car->doors }}</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Exterior Color</div>
              <div>{{ $car->exterior_color }}</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Material interior</div>
              <div>{{ $car->material_interior }}</div>
            </div>
          </div>
        </div>
    </div>
</div>


<!-- Interior Fetures -->
@php
    $interiors = $car->interior;
    $interiors = explode(",",$interiors);
@endphp
<div class="my-2 w-full bg-white rounded p-2 md:p-5">
    <div class="block lg:hidden">
      <div class="w-48 font-bold">Interior Features</div>
    <div class="border border-gray-200 my-2"></div>
    </div>
    <div class="text-sm leading-relaxed">
        <div class="grid grid-cols-6 md:gap-2">
          @foreach($interiors as $interior)
          <div class="col-span-3 md:col-span-2">
            <div class="flex justify-start">
              <span class="text-dark_green mr-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </span>
              <div class="md:font-bold capitalize">{{ $interior }}</div>
            </div>
          </div>
          @endforeach
 
        </div>
    </div>
</div>

<!-- Exterior Fetures -->
@php
    $exteriors = $car->exterior_equipment;
    $exteriors = explode(",",$exteriors);
@endphp
<div class="my-2 w-full bg-white rounded p-2 md:p-5">
    <div class="block lg:hidden">
      <div class="w-48 font-bold">Exterior Features</div>
    <div class="border border-gray-200 my-2"></div>
    </div>
    <div class="text-sm leading-relaxed">
        <div class="grid grid-cols-6 md:gap-2">
          @foreach($exteriors as $exterior)
          <div class="col-span-3 md:col-span-2">
            <div class="flex justify-start">
              <span class="text-dark_green mr-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </span>
              <div class="md:font-bold capitalize">{{ $exterior }}</div>
            </div>
          </div>
          @endforeach
 
        </div>
    </div>
</div>

@endif

@if($ad->sub_category=="6")
    <div class="my-2 w-full bg-white rounded p-2 md:p-5">
    <div class="block lg:hidden">
      <div class="w-48 font-bold">Details</div>
    <div class="border border-gray-200 my-2"></div>
    </div>
    <div class="text-sm leading-relaxed">
        <div class="grid grid-cols-6 md:gap-20">
          <div class="col-span-6 md:col-span-3">
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">brand</div>
              <div>{{ $brand->brand }} @isset($model->model) {{ $model->model }} @endisset</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Device & accessories</div>
              <div>{{ $phone->device }}</div>
            </div>
          </div>
          <div class="col-span-6 md:col-span-3">
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Colour</div>
              <div>{{ $phone->color }}</div>
            </div>
            <div class="flex justify-between items-center mb-1">
              <div class="md:font-bold">Condition</div>
              <div>{{ $phone->condition }}</div>
            </div>
          </div>
        </div>
    </div>
</div>
@endif


<div class="my-2 w-full bg-white rounded p-2 md:p-5">
    <div class="w-48 font-bold">Description</div>
    <div class="border border-gray-200 my-2"></div>
    <div class="text-sm leading-relaxed">
        {!! $ad->description !!}
    </div>
</div>

<div>

</div>

@if ($ad->sold == 'Yes')

@else
<div class="my-2 w-full bg-white rounded p-5 hidden lg:block">
    <div class="w-48 font-bold">Write Message</div>
    <div class="border border-gray-200 my-2"></div>
    @if($cat->category =="Jobs")
        <form method="POST" action="/apply/{{ $ad->id }}">
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
    @else
    <div>
      <a href="/chat/{{ $ad->id }}/{{ $ad->user_id }}" class="flex justify-start items-center btn btn-secondary w-52">
        <span class="mr-2">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
        </svg>
        </span>
        <span class="text-sm py-2">Write Message</span>
      </a>
    </div>
    @endif
</div>
@endif

<!--
<div class="fixed bottom-20 left-0 right-0 block lg:hidden">
  <div class="w-5/6 mx-auto">
      <a href="/chat-seller/{{ $ad->user_id }}/{{ $ad->ad_id }}" class="flex justify-center items-center btn btn-secondary py-2">
        <span class="mr-2">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
        </svg>
        </span>
        <span class="text-sm">Write Message</span>
      </a>
    </div>
</div>
-->
