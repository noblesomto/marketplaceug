@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

@php
    $productName  = $payment->advert->ad_title ?? '—';
    $productPrice = $payment->amount_paid ?? 0;
    $productState = $payment->advert->state ?? null;
    $adLink       = isset($payment->advert->state_slug, $payment->advert->title_slug, $payment->advert->ad_id)
                      ? url($payment->advert->state_slug . '/' . $payment->advert->title_slug . '/' . $payment->advert->ad_id)
                      : '#';

    $shipCompany  = $payment->shipping->company ?? null;
    $shipLogo     = $payment->shipping && $payment->shipping->logo
                        ? (Str::startsWith($payment->shipping->logo, 'http') ? $payment->shipping->logo : asset('uploads/shipping/'.$payment->shipping->logo))
                        : null;
    $shipStatus   = $payment->shipping_status ?? 'pending';
    $sellerStatus = $payment->seller_status ?? 'pending';
    $buyerStatus  = $payment->buyer_status ?? 'pending';
    $trackingId   = $payment->tracking_id ?? null;

    $pickupCity    = $payment->cityLocation->name ?? null;
    $pickupState   = $payment->cityLocation->state->name ?? null;
    $bannerAddress = trim(implode(', ', array_filter([$pickupCity, $pickupState])));

    $isPaid       = ($payment->payment_status ?? '') === 'paid';
    $isDelivered  = $buyerStatus === 'delivered';
    $orderDate    = $payment->created_at ? date('d M Y', strtotime($payment->created_at)) : '—';

    $badge = \App\Support\ShippingStatusBadge::resolve($sellerStatus, $shipStatus, $buyerStatus, $shipCompany ?? 'the shipping company');
    $shipOnlyBadge = \App\Support\ShippingStatusBadge::resolveShippingOnly($shipStatus);
    $banner = \App\Support\ShippingStatusBadge::bannerFor($badge['stage'], $shipCompany, $payment->shipping_status_date, $payment->seller_status_date, $bannerAddress ?: null);
@endphp

<section class="w-full md:w-3/6 mx-auto px-3 py-4 text-sm" style="max-width:640px;padding-bottom:7rem;">

    @include('public.components.flash-message')

    {{-- Page title + shipping status --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-base font-bold text-gray-800">Order #{{ $payment->order_code ?? $payment->id }}</h1>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
            <i class="bi {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
        </span>
    </div>

    {{-- Status update banner --}}
    @if($banner)
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl mb-3 text-xs font-medium {{ $banner['class'] }}">
        <i class="bi {{ $banner['icon'] }} text-base flex-shrink-0"></i>
        <span>{{ $banner['text'] }}</span>
    </div>
    @endif

    {{-- ── 1. Item Purchased ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-bag-check-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Item Purchased</span>
        </div>
        <div class="flex items-center gap-3 px-4 py-3">
            <a href="{{ $adLink }}" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-100 block">
                @if($payment->advert && $payment->advert->hasMedia('images'))
                    <img class="w-full h-full object-cover"
                         src="{{ $payment->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                         onerror="this.src='{{ asset('frontend/images/default.png') }}'">
                @else
                    <img class="w-full h-full object-cover" src="{{ asset('frontend/images/default.png') }}" alt="Product">
                @endif
            </a>
            <div class="flex-1 min-w-0">
                @if($productState)
                <div class="flex items-center gap-1 text-xs text-gray-400 mb-0.5">
                    <i class="bi bi-geo-alt-fill text-dark_green" style="font-size:0.65rem;"></i>
                    {{ $productState }}
                </div>
                @endif
                <div class="text-xs text-gray-400 mb-0.5">Product Name</div>
                <a href="{{ $adLink }}" class="font-semibold text-gray-800 leading-snug line-clamp-2 block no-underline hover:text-dark_green">
                    {{ $productName }}
                </a>
                <div class="text-base font-black text-dark_green mt-1">{{ money($productPrice, 0) }}</div>
            </div>
        </div>
    </div>

    {{-- ── 2. Selected Shipping ── --}}
    @if($shipCompany)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-truck text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Selected Shipping</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($shipLogo)
                        <img src="{{ $shipLogo }}" alt="{{ $shipCompany }}" class="h-6 max-w-[60px] object-contain">
                    @endif
                    <div>
                        <div class="text-xs text-gray-400 mb-0.5">Company</div>
                        <div class="font-semibold text-gray-800">{{ $shipCompany }}</div>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $shipOnlyBadge['class'] }}">
                    <i class="bi {{ $shipOnlyBadge['icon'] }}"></i> {{ $shipOnlyBadge['label'] }}
                </span>
            </div>
            @if($trackingId)
            <div class="border-t border-gray-100 pt-2">
                <div class="text-xs text-gray-400 mb-0.5">Tracking Number</div>
                <div class="font-semibold text-gray-800 font-mono">{{ $trackingId }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── 3. Delivery / Pickup Information ── --}}
    @if($pickupCity || $pickupState)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-geo-alt-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Selected Delivery / Pickup Information</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            @if($pickupCity)
            <div class="flex justify-between">
                <span class="text-gray-400">District</span>
                <span class="font-medium text-gray-800">{{ $pickupCity }}</span>
            </div>
            @endif
            @if($pickupState)
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Region</span>
                <span class="font-medium text-gray-800">{{ $pickupState }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── 4. Order Summary ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-receipt text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Order Summary</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-400">Order Date</span>
                <span class="font-medium text-gray-800">{{ $orderDate }}</span>
            </div>
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Payment Status</span>
                <span class="font-semibold {{ $isPaid ? 'text-green-700' : 'text-amber-600' }}">
                    {{ $isPaid ? '✓ Paid' : '⏳ Pending' }}
                </span>
            </div>
            @if($payment->payment_reference)
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between gap-4">
                <span class="text-gray-400 flex-shrink-0">Reference</span>
                <span class="font-medium text-gray-800 font-mono text-right text-xs">{{ $payment->payment_reference }}</span>
            </div>
            @endif
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Amount Paid</span>
                <span class="font-black text-dark_green text-base">{{ money($productPrice, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- ── 5. Confirm delivery / Cancel order (if not yet confirmed) ── --}}
    @if(!$isDelivered && $isPaid && $badge['stage'] !== 'canceled')
    <div class="bg-white rounded-xl border border-green-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border-b border-green-100">
            <i class="bi bi-box-seam-fill text-dark_green"></i>
            <span class="font-semibold text-dark_green text-xs uppercase tracking-wide">Received Your Order?</span>
        </div>
        <div class="px-4 py-4 text-xs text-gray-600 leading-relaxed space-y-3">
            <p>Once your item arrives, tap the button below to confirm delivery. This releases payment to the seller and completes your transaction.</p>
            <div class="flex gap-2">
                @if($shipStatus === 'pending')
                    <button id="cancelBtn"
                            onclick="cancelOrder({{ $payment->id }})"
                            class="flex-1 min-w-0 py-2.5 bg-white text-red-600 font-semibold rounded-lg text-sm border border-red-200 hover:bg-red-50 transition-colors flex items-center justify-center gap-1.5 text-center">
                        <i class="bi bi-x-circle"></i> Cancel Order
                    </button>
                @endif

                <button id="confirmBtn"
                        onclick="confirmDelivery({{ $payment->id }})"
                        class="flex-1 min-w-0 py-2.5 bg-dark_green text-white font-semibold rounded-lg text-sm hover:bg-green-800 transition-colors flex items-center justify-center gap-1.5 text-center">
                    <i class="bi bi-check-circle"></i> Confirm Delivery
                </button>
            </div>

            @if($shipStatus === 'pending')
                <p class="text-gray-400 leading-relaxed">
                    <i class="bi bi-info-circle mr-1"></i>
                    You can cancel for a full refund any time before the seller ships your order. Once it's on its way, cancellations go through our support team instead.
                </p>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-500">
                    <p class="leading-relaxed"><i class="bi bi-lock-fill mr-1"></i>This order is already with {{ $shipCompany ?? 'the shipping company' }}, so it can no longer be canceled here. Please <a href="/contact-us" class="font-semibold underline text-dark_green">contact support</a> if you need help.</p>
                </div>
            @endif
        </div>
    </div>
    @elseif($isDelivered)
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl mb-3 bg-green-50 border border-green-200 text-green-800 text-xs font-medium">
        <i class="bi bi-check-circle-fill text-base flex-shrink-0"></i>
        <span>You confirmed delivery of this order. Transaction complete.</span>
    </div>
    @endif

    {{-- ── 6. Help note ── --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-4 text-xs text-blue-800 leading-relaxed">
        <p><i class="bi bi-shield-lock-fill mr-1"></i>
        <strong>Secured by Marketplace Uganda.</strong> If you have any questions or require assistance,
        please <a href="/contact-us" class="font-semibold underline">contact our support team</a>.</p>
    </div>

</section>

<div id="successModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" style="max-width:90vw;">
        <div class="mt-3 text-center">
            <div style="width:56px;height:56px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <i class="bi bi-check-circle-fill" style="font-size:1.6rem;color:#15803d;"></i>
            </div>
            <h3 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:6px;">Delivery Confirmed!</h3>
            <p style="font-size:0.82rem;color:#6b7280;margin-bottom:20px;">Thanks for confirming. Your transaction is now complete.</p>
            <button id="closeModal"
                    style="background:#326916;color:#fff;padding:9px 28px;border-radius:8px;border:none;font-weight:600;font-size:0.85rem;cursor:pointer;">
                Done
            </button>
        </div>
    </div>
</div>

<div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" style="max-width:90vw;">
        <div class="mt-3 text-center">
            <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <i class="bi bi-x-circle-fill" style="font-size:1.6rem;color:#dc2626;"></i>
            </div>
            <h3 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:6px;">Order Canceled</h3>
            <p style="font-size:0.82rem;color:#6b7280;margin-bottom:20px;">Your refund is being processed and will be credited back to you shortly.</p>
            <button id="closeCancelModal"
                    style="background:#dc2626;color:#fff;padding:9px 28px;border-radius:8px;border:none;font-weight:600;font-size:0.85rem;cursor:pointer;">
                Done
            </button>
        </div>
    </div>
</div>

@include('user.layouts.footer')

<script>
function confirmDelivery(orderId) {
    Swal.fire({
        title: 'Confirm Delivery?',
        text: 'This releases payment to the seller and completes your transaction.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#326916',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, confirm delivery',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btn = document.getElementById('confirmBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing…';

        fetch(`/user/confirm-delivery/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: 'delivered' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('successModal');
                modal.classList.remove('hidden');
                document.getElementById('closeModal').onclick = () => location.reload();
                setTimeout(() => location.reload(), 4000);
            } else {
                throw new Error(data.message || 'Failed');
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#326916' });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Delivery';
        });
    });
}

function cancelOrder(orderId) {
    Swal.fire({
        title: 'Cancel this order?',
        text: 'This cannot be undone and will trigger a refund.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, cancel order',
        cancelButtonText: 'Keep order'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btn = document.getElementById('cancelBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Canceling…';

        fetch(`/user/cancel-order/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('cancelModal');
                modal.classList.remove('hidden');
                document.getElementById('closeCancelModal').onclick = () => location.reload();
                setTimeout(() => location.reload(), 4000);
            } else {
                throw new Error(data.message || 'Failed');
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#326916' });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancel Order';
        });
    });
}
</script>
