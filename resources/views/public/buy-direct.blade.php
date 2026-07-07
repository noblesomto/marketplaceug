@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.layouts.mobile-back-nav')
@include('public.layouts.search')

<section class="max-w-4xl mx-auto  space-y-3 mb-20">
  <!-- Advertisement Banner (Desktop) -->
  <div class="hidden lg:block my-6 rounded-lg overflow-hidden shadow-md">
    @include('public.components.advert.banner-advert')
  </div>

  <!-- Product Purchase Section -->
  <div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="md:flex">
      <!-- Product Image Gallery -->
      <div class="md:w-1/2 p-2">
        <div class="relative overflow-hidden rounded-lg bg-gray-100 aspect-square mb-4">
          <img src="{{ $ad->hasMedia('images') ? $ad->getFirstMediaUrl('images', 'large') : asset('frontend/images/default.png') }}"
               alt="{{ $ad->ad_title }}" 
               class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
        </div>
        <div class="grid grid-cols-4 gap-2">
          <!-- Additional thumbnails can go here -->
        </div>
      </div>
      
      <!-- Product Details -->
      <div class="md:w-1/2 px-2">
        <div class="mb-6">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ $ad->ad_title }}</h1>
          
          <!-- Price Information -->
          <div class="mb-4">
            <div class="flex items-center space-x-4 mb-1">
              <span class="text-2xl font-bold text-primary">₦{{ number_format($ad->price, 0, '.', ',') }}</span>
              <span class="text-gray-600">{{ $ad->price_type }}</span>
            </div>

            @include('public.components.flash-message')

            <!-- Purchase Form -->
            <form method="POST" action="/calculate-shipping/{{ $ad->id }}" class="mt-6">
                @csrf

            <h4 class="font-semibold">Shipping Information</h4>

            <div class="my-3">
                @if ($errors->has('first_name'))
                    <span class="text-red-700 py-1">{{ $errors->first('first_name') }}</span>
                @endif
                <input type="text" name="first_name" id="first_name" placeholder="First Name" class="w-full bg-white px-3 py-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-400" value="{{ old('first_name') }}">
            </div>
            <div class="my-3">
                @if ($errors->has('last_name'))
                    <span class="text-red-700 py-1">{{ $errors->first('last_name') }}</span>
                @endif
                <input type="text" name="last_name" id="last_name" placeholder="Last Name" class="w-full bg-white px-3 py-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-400" value="{{ old('last_name') }}">
            </div>
            <div class="my-3">
                @if ($errors->has('phone'))
                    <span class="text-red-700 py-1">{{ $errors->first('phone') }}</span>
                @endif
                <input type="text" name="phone" id="phone" placeholder="Phone Number" class="w-full bg-white px-3 py-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-400" required value="{{ old('phone') }}">
            </div>

            <div x-data="{ open: false, selected: null }" class="relative w-full max-w-md mx-auto">
            <!-- Selected item -->
            <div
                @click="open = !open"
                class="border border-gray-300 rounded-xl px-4 py-3 flex items-center justify-between bg-white shadow-sm cursor-pointer transition-all"
                :class="{ 'border-red-500': !selected && @json($errors->has('shipping_selected')) }"
            >
                <template x-if="selected">
                    <div class="flex items-center space-x-3 w-full">
                        <img :src="selected.logo" class="w-10 h-10 object-contain rounded-md" loading="lazy">
                        <div class="w-full">
                            <div class="flex justify-between items-center">
                                <div class="font-medium text-base text-gray-800" x-text="selected.company"></div>
                                <div class="text-sm text-gray-700 flex-shrink-0"><span x-text="selected.weight"></span> KG</div>
                            </div>
                            <div class="text-sm text-gray-500">Max <span x-text="selected.weight"></span> kg</div>
                        </div>
                    </div>
                </template>
                <template x-if="!selected">
                    <span class="text-gray-400 text-sm">Select a shipping option</span>
                </template>
                <svg class="w-5 h-5 ml-3 text-gray-500 transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <!-- Dropdown list -->
            <div
                x-show="open"
                x-transition
                @click.away="open = false"
                class="absolute z-50 bg-white border border-gray-200 mt-2 w-full rounded-xl shadow-lg overflow-hidden max-h-80 overflow-y-auto"
                style="scroll-behavior: smooth;"
            >
                @foreach($ad->shippings as $shipping)
                    <div
                        @click="
                            selected = {
                                company: '{{ $shipping->company }}',
                                price: {{ $shipping->price }},
                                weight: {{ $shipping->weight }},
                                logo: '{{ Str::startsWith($shipping->logo, 'http') ? $shipping->logo : asset('uploads/shipping/'.$shipping->logo) }}',
                                ship_id: {{ $shipping->id }},
                            };
                            open = false;
                            document.getElementById('shipping_price').value = selected.price;
                            document.getElementById('ship_id').value = selected.ship_id;
                            document.getElementById('shipping_selected').value = selected.ship_id;
                        "
                        class="flex items-center gap-4 px-4 py-3 hover:bg-gray-100 cursor-pointer border-b last:border-b-0 transition-colors"
                    >
                        <img src="{{ Str::startsWith($shipping->logo, 'http') ? $shipping->logo : asset('uploads/shipping/'.$shipping->logo) }}"
                             class="w-10 h-10 object-contain rounded-md" loading="lazy">
                        <div>
                            <div class="font-medium text-sm text-gray-800">{{ $shipping->company }}</div>
                            <div class="text-xs text-gray-500">Max {{ $shipping->weight }} kg</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Hidden inputs -->
            <input type="hidden" name="shipping_price" id="shipping_price">
            <input type="hidden" name="ship_id" id="ship_id">
            <input type="hidden" name="shipping_selected" id="shipping_selected" required>

            @if ($errors->has('shipping_selected'))
                <span class="text-red-700 py-1">Please select a shipping option</span>
            @endif
        </div>


            @php 
              $commission = 0.04 * $ad->price;
            @endphp
            

          </div>

          
          

            <input type="hidden" name="email" value="{{ $user->email }}">
            <input type="hidden" name="amount" value="{{ $ad->price }}">
            <input type="hidden" name="advert_id" value="{{ $ad->id }}">
            <input type="hidden" name="total_price" id="grand_total_input" >
            <input type="hidden" name="shipping_cost" id="shipping_cost" >
            <input type="hidden" name="commission" value="{{ $commission }}">
            <input type="hidden" name="shipping_method" id="ship_method" >

            <div class="">
              <h4 class="font-semibold">Delivery / Pickup Location</h4>

                  <div class="my-2">
                    @if ($errors->has('state'))
                        <span class="text-red-700 py-1">{{ $errors->first('state') }}</span>
                    @endif
                      <select name="state" id="state" class="w-full bg-white  px-3 py-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-400" required>
                    <option value="" selected="selected">-- Select State --</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                        @endforeach
                    </select>
                  </div>
                  <div class="my-2">
                      <select name="city" id="city" class="w-full bg-white px-3 py-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-400" required>
                    <option value="">-- Select City --</option>
                    </select>
                  </div>

            </div>
            
            <button type="submit" class="w-full md:w-auto mt-4 flex items-center justify-center px-8 py-3 bg-primary hover:bg-primary-dark  font-medium rounded-lg shadow-md transition-colors duration-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Proceed To Payment
            </button>
          </form>

          <div id="clickableDiv" class="py-4 cursor-pointer" >
            <p class="text-dark_green">Learn more about Buy Direct</p>
        </div>
          
          <!-- Trust Badges -->
          <div class="mt-4 flex items-center space-x-4">
            <div class="flex items-center text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span class="text-sm">Secure Payment</span>
            </div>
            <div class="flex items-center text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <span class="text-sm">Buyer Protection</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Product Description (can be added if available) -->
    @if($ad->description)
    <div class="border-t border-gray-200 p-6">
      <h2 class="text-xl font-semibold mb-4">Description</h2>
      <div class="prose max-w-none">
        {!! $ad->description !!}
      </div>
    </div>
    @endif
  </div>
</section>


<!-- Modal -->
<div id="modalBackdrop" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="modal" class="hidden relative bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-white z-10">
                <h3 class="text-xl font-semibold">What is "Buy Direct"?</h3>
                <button id="closeModalBuy" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scrollable content -->
            <div class="p-4 overflow-y-auto flex-grow">
                <p class="text-gray-600 mb-4">
                Buy Direct is a fast and secure way to purchase items instantly on our platform—no need to wait for negotiations or back-and-forth messaging.
            </p>

                <p class="text-gray-600 mb-4">
                    When you see the Buy Direct button, it means the seller has enabled instant purchase. Simply click, pay, and the item is yours. It's the easiest way to shop with confidence.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">How it works:</h3>
                
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Instant Purchase</h4>
                            <p class="text-gray-600">Tap Buy Direct to immediately lock in the item before someone else does.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Secure Payments with Paystack</h4>
                            <p class="text-gray-600">All payments are processed safely through Paystack, supporting cards, bank transfers, and other local methods.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Trusted Delivery Partners</h4>
                            <p class="text-gray-600">Choose delivery through GIG Logistics, FedEx, or GUO Logistics—or arrange local pickup if offered by the seller.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Buyer Protection</h4>
                            <p class="text-gray-600">Your payment is held securely until you confirm you've received the item in good condition. If you don't take any action after the item is successfully delivered, we'll automatically release the payout to the seller 7 days after delivery—giving you enough time to inspect the item and report any issues.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Payment Refund</h4>
                            <p class="text-gray-600">If the seller does not ship your item within 3 working days after payment, you will receive a full refund.</p>
                        </div>
                    </li>

                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Dispute Resolution</h4>
                            <p class="text-gray-600">If there's an issue with your order, you can open a dispute within 3 days of delivery. Our support team will step in to mediate and resolve the issue fairly—whether it's a refund, replacement, or other solution.</p>
                        </div>
                    </li>

                </ul>
                
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-blue-800 font-medium">
                        With Buy Direct, shopping is simpler, faster, and more reliable—giving you peace of mind every step of the way.
                    </p>
                </div>

            </div>

            <!-- Footer (optional) -->
            <div class="p-4 border-t sticky bottom-0 bg-white">
                <button id="closeModalBtn" class="px-4 py-2 bg-primary hover:bg-primary-dark rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@include('public.layouts.footer')

<!-- Alpine.js for simple interactivity (can be replaced with plain JS if needed) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
 <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>



<script>
    document.getElementById('clickableDiv').addEventListener('click', function() {
        document.getElementById('modalBackdrop').classList.remove('hidden');
        document.getElementById('modal').classList.remove('hidden');
    });

    document.getElementById('closeModalBuy').addEventListener('click', function() {
        document.getElementById('modalBackdrop').classList.add('hidden');
        document.getElementById('modal').classList.add('hidden');
    });

    // Close modal when clicking on backdrop
    document.getElementById('modalBackdrop').addEventListener('click', function() {
        document.getElementById('modalBackdrop').classList.add('hidden');
        document.getElementById('modal').classList.add('hidden');
    });
</script>


<script>
        $('#state').on('change', function () {
            let stateID = $(this).val();

            if (stateID) {
                $.ajax({
                    url: '/get-gig/' + stateID,
                    type: 'GET',
                    success: function (data) {
                        $('#city').empty().append('<option value="">-- Select City --</option>');
                        $.each(data, function (key, city) {
                            $('#city').append(
                                `<option value="${city.id}">${city.city} - ${city.address}</option>`
                            );
                        });
                    }
                });
            } else {
                $('#city').empty().append('<option value="">-- Select City --</option>');
            }
        });
    </script>
