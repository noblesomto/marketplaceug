@include('dashboard.layouts.header')
@include('dashboard.layouts.back-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6  mx-auto p-3 text-sm">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        Your Orders
        @include('frontend.components.flash-message')
    </div>
    <div class="pb-10 mb-10">
        @if (!$buyAds->isEmpty())
          @foreach ($buyAds as $row)
          <div class="bg-white mb-2 p-2 border-b border-b-gray-300 shadow">
             <div class="flex w-full">
                  <div class="w-2/6 mr-1 relative bg-gray-50">
                   <a href="{{ isset($row->advert->state_slug, $row->advert->title_slug, $row->advert->ad_id) ? url($row->advert->state_slug . '/' . $row->advert->title_slug .'/'. $row->advert->ad_id) : '#' }}">
                     @if(isset($row->advert->firstImage->image))
                       <img class="h-24 md:h-48 object-cover" src="{{ $row->hasMedia('images') ? $row->getFirstMediaUrl('images', 'thumbnail') : asset('frontend/images/default.png') }}" alt="Ad image">
                     @else
                       <img class="h-24 md:h-48 object-cover" src="{{ asset('images/default-ad.jpg') }}" alt="Default ad image">
                     @endif
                   </a>
                  </div>
                  <div class="w-4/6 relative">
                    <div class="flex justify-between text-xs">
                      <div class="flex justify-start items-center text-sm md:mr-5">
                        <span class="mr-3 hidden lg:block">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                          </svg>
                        </span>
                        <div>
                          <span class="text-xs">{{ $row->advert->state ?? 'Location not specified' }}</span>
                        </div>
                      </div>

                      <div>
                        <div class="flex justify-start mr-5 text-xs md:mt-2">
                          <span class="mr-3 hidden lg:block">Payment Date:</span>
                          <span class="text-xs">{{ isset($row->created_at) ? date('d.m.Y', strtotime($row->created_at)) : 'N/A' }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="font-medium leading-5 md:font-bold text-base md:text-xl md:mt-2">
                      {{ Str::limit($row->advert->ad_title ?? 'No title available', 50) }}
                    </div>
                    <div class="text-sm mt-2 hidden lg:block">
                      {!! Str::limit(strip_tags($row->advert->description ?? 'No description available', 80)) !!}
                    </div>
                    <div class="flex justify-between items-center text-dark_green font-bold text-base my-1 lg:my-3">
                        <div class="mr-4">₦ {{ isset($row->amount_paid) ? number_format($row->amount_paid, 0, '.', ',') : '0' }}</div>
                        <div class="capitalize w-36 px-3 py-0 lg:py-1 text-center rounded {{ ($row->payment_status ?? '') === 'paid' ? 'bg-green-200' : 'bg-yellow-200' }}">
                            {{ $row->payment_status ?? 'pending' }}
                        </div>
                    </div>

                    @if(($row->shipping_status ?? '') == "shipped")
                    <div class="flex flex-col lg:flex-row lg:justify-between gap-1 lg:gap-10 mt-1">
                        <span class="block">Item Will be delivered within 3 - 7 working days</span>
                        <span class="block">Updated: {{ isset($row->shipping_status_date) ? date('d.m.Y', strtotime($row->shipping_status_date)) : 'N/A' }}</span>
                    </div>
                    @endif

                    @if(($row->shipping_status ?? '') == "delivered")
                    <div class="flex flex-col lg:flex-row lg:justify-between gap-1 lg:gap-10 mt-1">
                        <span class="block">Package Delivered</span>
                        <span class="block">Updated: {{ isset($row->shipping_status_date) ? date('d.m.Y', strtotime($row->shipping_status_date)) : 'N/A' }}</span>
                    </div>
                    @endif

                  </div>
              </div>

              <div class="w-full pb-2">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
        @if(isset($row->shipping))

            <div class="w-full">
                <h5 class="font-semibold text-sm">Shipping Method:</h5>
                <div class="flex items-center w-full">
                    @if(isset($row->shipping->logo))
                        <span><img class="w-16" src="{{ asset('uploads/shipping/'.$row->shipping->logo) }}" alt="{{ $row->shipping->company ?? 'Shipping company' }} logo"></span>
                    @endif
                    <span class="ml-2 text-sm font-bold">{{ $row->shipping->company ?? 'Shipping not specified' }}</span>
                </div>
            </div>

            <div class="w-full items-center text-sm">
                <h5 class="font-semibold">Shipping Status:</h5>
                <div class="mt-2 w-full">
                    @if(($row->shipping_status ?? '') == "delivered")
                        <span class="capitalize text-center rounded-lg bg-green-100 p-2 w-full block">✓ {{ $row->shipping_status }}</span>
                    @elseif(($row->shipping_status ?? '') == "shipped")
                        <span class="capitalize text-center rounded-lg bg-yellow-100 p-2 w-full block">{{ $row->shipping_status }}</span>
                    @else
                        <span class="capitalize text-center rounded-lg bg-red-100 p-2 w-full block">{{ $row->shipping_status ?? 'pending' }}</span>
                    @endif
                </div>
            </div>

            <div class="w-full lg:pt-7">
                <a href="{{ optional($row->advert)->ad_id ? '/report-ad/' . $row->advert->ad_id : '#' }}"
                   class="bg-secondary-200 px-4 py-2 rounded-lg inline-block w-full text-center">
                    Report an Issue
                </a>
            </div>


            <div class="w-full lg:pt-7">
                @if(($row->buyer_status ?? '') == 'delivered')
                    <button class="bg-green-200 px-4 py-2 rounded-lg cursor-not-allowed w-full"
                            disabled>
                        Delivered
                    </button>
                @else
                    <button class="bg-gray-300 px-4 py-2 rounded-lg confirm-delivery-btn w-full"
                            data-order-id="{{ $row->id }}"
                            onclick="confirmDelivery(this)">
                        Confirm Delivery
                    </button>
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
                    <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="No ads found"/>
                </span>
                <span>No Posts here...</span>
            </div>
        @endif
    </div>
</section>


<!-- Custom Modal for Success Message -->
<div id="successModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">Delivery Confirmed!</h3>
            <p class="text-sm text-gray-500 mb-4">Thanks for confirming the delivery.</p>
            <p class="text-sm text-gray-500 mb-4">We are glad your item arrived safely, your transaction is now complete. </p>
            <div class="flex justify-center">
                <button id="closeModal" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-6 rounded-lg">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

@include('dashboard.layouts.footer')
<script>
function confirmDelivery(button) {
    const orderId = button.getAttribute('data-order-id');

    // Show confirmation dialog
    if (confirm('Are you sure you want to confirm this delivery?')) {
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...</span>';

        // Make AJAX request
        fetch(`/user/confirm-delivery/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: 'delivered'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show custom success modal
                showSuccessModal();

                // Update button appearance only if status is actually "delivered"
                if (data.order && data.order.shipping_status === 'delivered') {
                    button.innerHTML = '✓ Delivered';
                    button.classList.remove('bg-green-200', 'hover:bg-green-300');
                    button.classList.add('bg-gray-300', 'cursor-not-allowed');
                    button.disabled = true;
                } else {
                    // Reset button if status is not delivered
                    button.disabled = false;
                    button.innerHTML = 'Confirm Delivery';
                }

            } else {
                throw new Error(data.message || 'Failed to update delivery status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
            // Reset button state
            button.disabled = false;
            button.innerHTML = 'Confirm Delivery';
        });
    }
}

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.classList.remove('hidden');

    // Close modal when clicking OK or outside
    document.getElementById('closeModal').onclick = () => {
        modal.classList.add('hidden');
    };

    // Close when clicking outside
    modal.onclick = (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    };

    // Auto close after 3 seconds (optional)
    setTimeout(() => {
        if (!modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
        }
    }, 10000);
}
</script>

