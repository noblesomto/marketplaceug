@include('frontend.layouts.header')
@include('frontend.layouts.nav')



<section class="my-10">

    <div class="max-w-5xl mx-auto bg-white p-3 md:p-10  pb-20 mb-5 rounded-lg">
        <div class="flex justify-center">
            <h3 class="text-2xl font-bold underline">Shipping Order Details</h3>
        </div>
        <div class="grid grid-cols-10 gap-10 mt-10">


        <div class="col-span-10 lg:col-span-5">
            <!-- Product Image Gallery -->
              <div class="w-full">
                <div class="relative overflow-hidden rounded-lg bg-gray-100 aspect-square mb-4">
                  <img src="{{ asset('uploads/images/' . $ship->advert?->firstImage?->image) }}"
                       alt="{{ $ship->advert->ad_title }}"
                       class="w-full  object-cover transition-transform duration-300 hover:scale-105">
                </div>
              </div>

              <!-- Product Details -->
              <div class="text-base space-y-4">
                <h4 class="text-xl font-semibold underline">Product Details</h4>
                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">Name:</span>
                    <span><h5>{{ $ship->advert->ad_title }}</h5></span>
                </div>
                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">Location:</span>
                    <span><h5>{{ $ship->advert->owner->state }}</h5></span>
                </div>
            </div>

        </div>
        <div class="col-span-10 lg:col-span-5 space-y-10">
            <!-- Sender Details -->
            <div class="text-base space-y-4">
                <h4 class="text-xl font-semibold underline">Sender Details</h4>
                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">Name:</span>
                    <span><h5>{{ $ship->advert->owner->name }}</h5></span>
                </div>
                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">Phone:</span>
                    <span><h5>{{ $ship->advert->owner->phone }}</h5></span>
                </div>
                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">Location:</span>
                    <span><h5>{{ $ship->advert->owner->state }}</h5></span>
                </div>
            </div>

            <!-- Receiver Details -->
            <div class="text-base space-y-4">
                <h4 class="text-xl font-semibold underline">Receiver Details</h4>
                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">Name:</span>
                    <span><h5>{{ $ship->first_name }} {{ $ship->last_name }}</h5></span>
                </div>

                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">Phone:</span>
                    <span><h5>{{ $ship->phone }}</h5></span>
                </div>

            </div>

            <!-- Pickup Location Details -->
            <div class="text-base space-y-4">
                <h4 class="text-xl font-semibold underline">Receiver Pickup Location</h4>
                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">City:</span>
                    <span><h5>{{ $city->city }}</h5></span>
                </div>
                <div class="flex justify-start space-x-4">
                    <span class="font-semibold">State:</span>
                    <span><h5>{{ $ship->stateRel->name }}</h5></span>
                </div>
            </div>
        </div>
    </div>
</div>

@include('frontend.components.flash-message')

<div class="max-w-5xl mx-auto bg-white p-3 md:p-10  pb-20 mb-5 rounded-lg">
    <div class="flex justify-center mb-4">
            <h3 class="text-2xl font-bold underline">Shipping Company Section</h3>
        </div>
    <form action="/shipper/update-shipping/{{ $ship->ship_code }}">
        @csrf

        <div class="mb-4 space-y-2">
            @if ($errors->has('tracking_id'))
                <span class="text-red-700 py-1">{{ $errors->first('tracking_id') }}</span>
            @endif
            <label class="text-sm font-semibold mb-2">Enter The Tracking ID*</label>
            <input type="text" id="name" name="tracking_id" placeholder="WREH5768FET" max="10" min="5" class="w-full px-3 py-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $ship->tracking_id }}" required>
        </div>

        <div class="mb-4  space-y-2">
            @if ($errors->has('ship_status'))
                <span class="text-red-700 py-1">{{ $errors->first('ship_status') }}</span>
            @endif
            <label class="text-sm font-semibold mb-2">Shipping Status*</label>
            <select name="shipping_status" class="w-full px-3 py-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="pending" {{ ($ship->shipping_status =='pending') ? "selected" : ""; }}>Pending</option>
                <option value="shipped" {{ ($ship->shipping_status =='shipped') ? "selected" : ""; }}>Shipped</option>
                <option value="delivered" {{ ($ship->shipping_status =='delivered') ? "selected" : ""; }}>Delivered</option>
            </select>
        </div>

        <div class="mt-8">
                <button type="submit" class="flex justify-center items-center bg-transparent hover:bg-primary text-dark_green font-semibold hover:text-dark_green  py-3 px-6 border-2 border-dark_green hover:border-dark_green rounded-full ">
                    <span>Update</span>
                    <span class="ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                          <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
              </div>
    </form>
</div>
</section>

@include('frontend.layouts.footer')


