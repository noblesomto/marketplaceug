@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6  mx-auto p-3 text-sm">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        Buy Direct Ads
        @include('frontend.components.flash-message')
    </div>
    <div class="pb-10 mb-10">
        @if (!$buyAds->isEmpty())
          @foreach ($buyAds as $row)
            <div class="bg-white mb-3 border border-gray-200 shadow-sm rounded-lg overflow-hidden">
                <div class="flex flex-col md:flex-row">

                    <!-- Image -->
                    <a href="{{ isset($row->advert->state_slug, $row->advert->title_slug, $row->advert->ad_id) ? url($row->advert->state_slug . '/' . $row->advert->title_slug .'/'. $row->advert->ad_id) : '#' }}"
                       class="w-full md:w-2/6">
                        @if(isset($row->advert->firstImage->image))
                            <img class="h-48 w-full object-cover md:h-64"
                                 src="{{ asset('uploads/images/'.$row->advert->firstImage->image) }}"
                                 alt="Ad image">
                        @else
                            <img class="h-48 w-full object-cover md:h-64"
                                 src="{{ asset('images/default-ad.jpg') }}"
                                 alt="Default ad image">
                        @endif
                    </a>

                    <!-- Details -->
                    <div class="w-full md:w-4/6 p-3 md:p-4">
                        <!-- Location & Payment Date -->
                        <div class="flex justify-between md:items-center text-xs text-gray-600">
                            <div class="flex items-center mb-2 md:mb-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-4 w-4 mr-1 text-gray-500"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642
                                          4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <span>{{ $row->advert->state ?? 'Location not specified' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-700">Payment:</span>
                                <span>{{ isset($row->created_at) ? date('d.m.Y', strtotime($row->created_at)) : 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="font-semibold text-base md:text-xl mt-2 leading-snug">
                            {{ Str::limit($row->advert->ad_title ?? 'No title available', 50) }}
                        </div>

                        <!-- Description (only desktop) -->
                        <div class="hidden md:block text-sm mt-2 text-gray-600">
                            {!! Str::limit(strip_tags($row->advert->description ?? 'No description available', 80)) !!}
                        </div>

                        <!-- Price & Status -->
                        <div class="flex justify-between items-center text-dark_green font-bold text-lg mt-3">
                            <div>₦ {{ isset($row->amount_paid) ? number_format($row->amount_paid, 0, '.', ',') : '0' }}</div>
                            <div class="capitalize px-3 py-1 rounded-lg text-xs md:text-sm {{ ($row->payment_status ?? '') === 'paid' ? 'bg-green-200' : 'bg-yellow-200' }}">
                                {{ $row->payment_status ?? 'pending' }}
                            </div>
                        </div>

                        <!-- Shipping (same as before, with responsive buttons) -->
                        @if(isset($row->shipping))
                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex items-center">
                                    @if(isset($row->shipping->logo))
                                        <img class="w-12"
                                             src="{{ asset('uploads/shipping/'.$row->shipping->logo) }}"
                                             alt="{{ $row->shipping->company ?? 'Shipping company' }}">
                                    @endif
                                    <span class="ml-2 font-semibold">{{ $row->shipping->company ?? 'Shipping not specified' }}</span>
                                </div>

                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                    <div class="flex items-center justify-between">
                                        <h5 class="font-medium">Shipping Status:</h5>
                                        @if(($row->shipping_status ?? '') == "delivered")
                                            <span class="ml-1 bg-green-100 px-2 py-0 rounded">Delivered</span>
                                        @elseif(($row->shipping_status ?? '') == "shipped")
                                            <span class="ml-1 bg-yellow-100 px-2 py-0 rounded">Shipped</span>
                                        @else
                                            <span class="ml-1 bg-red-100 px-2 py-0 rounded">Pending</span>
                                        @endif
                                    </div>

                                    <!-- Buttons -->
                                    <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                                        <a href="/report-ad/{{ $row->advert->id }}"
                                           class="bg-secondary-200 px-4 py-2 text-center rounded-lg text-sm">
                                            Report Issue
                                        </a>
                                        @if(($row->buyer_status ?? '') == 'delivered')
                                            <button class="bg-green-200 px-4 py-2 rounded-lg text-sm cursor-not-allowed" disabled>
                                                ✓ Delivered
                                            </button>
                                        @else
                                            <button class="bg-gray-300 px-4 py-2 rounded-lg text-sm confirm-delivery-btn w-full md:w-auto"
                                                    data-order-id="{{ $row->id }}"
                                                    onclick="confirmDelivery(this)">
                                                Confirm Delivery
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Delivery Notes -->
                        @if(($row->shipping_status ?? '') == "shipped")
                            <p class="text-xs mt-2 text-gray-600">
                                Item will be delivered within 3 - 7 working days
                                <br><span class="text-gray-500">Updated: {{ isset($row->shipping_status_date) ? date('d.m.Y', strtotime($row->shipping_status_date)) : 'N/A' }}</span>
                            </p>
                        @endif

                        @if(($row->shipping_status ?? '') == "delivered")
                            <div class="text-xs mt-2 text-gray-600 flex justify-between">
                                <span>Package Delivered</span>
                                <span class="text-gray-500">Updated: {{ isset($row->shipping_status_date) ? date('d.m.Y', strtotime($row->shipping_status_date)) : 'N/A' }}</span>
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
                <button
                    id="closeModal"
                    onclick="document.getElementById('successModal').classList.add('hidden'); location.reload();"
                    class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-6 rounded-lg">
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

    // OK button closes modal and refreshes page
    document.getElementById('closeModal').onclick = () => {
        modal.classList.add('hidden');
        location.reload();
    };

    // Close when clicking outside
    modal.onclick = (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            location.reload();
        }
    };

    // Auto close after 10 seconds + refresh
    setTimeout(() => {
        if (!modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            location.reload();
        }
    }, 10000);
}
</script>

