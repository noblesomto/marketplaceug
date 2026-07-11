@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

@php
    $buyerName    = trim(($ad->first_name ?? '') . ' ' . ($ad->last_name ?? ''));
    $buyerPhone   = $ad->phone ?? '—';
    $productName  = $ad->advert->ad_title ?? '—';
    $shipCompany  = $ad->shipping->company ?? '—';
    $shipLogo     = $ad->shipping->logo ?? null;
    $shipCode     = $ad->ship_code ?? '—';
    $trackingId   = $ad->tracking_id ?? null;
    $shipStatus   = $ad->shipping_status ?? 'pending';
    $sellerStatus = $ad->seller_status ?? 'pending';
    $city         = $ad->cityLocation->city ?? null;
    $address      = $ad->cityLocation->address ?? null;
    $stateName    = $ad->cityLocation->state->name ?? null;
    $isDelivered  = ($ad->buyer_status ?? '') === 'delivered';
@endphp

<section class="w-full md:w-3/6 mx-auto px-3 py-4 text-sm" style="max-width:640px;padding-bottom:7rem;">

    @include('public.components.flash-message')

    {{-- Page title + status badge --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-base font-bold text-gray-800">Shipping Details</h1>
        @if($isDelivered)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                <i class="bi bi-check-circle-fill"></i> Delivered
            </span>
        @elseif($shipStatus === 'delivered')
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700 border border-orange-200">
                <i class="bi bi-hourglass-split"></i> Pending Confirmation
            </span>
        @elseif($shipStatus === 'shipped')
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                <i class="bi bi-truck"></i> Shipped
            </span>
        @elseif($shipStatus === 'pickup')
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                <i class="bi bi-shop"></i> Ready for Pickup
            </span>
        @elseif($shipStatus === 'canceled')
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                <i class="bi bi-x-circle-fill"></i> Canceled
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                <i class="bi bi-clock"></i> Pending
            </span>
        @endif
    </div>

    {{-- ── 1. Buyer Information ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-person-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Buyer Information</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-400">Name</span>
                <span class="font-medium text-gray-800">{{ $buyerName ?: '—' }}</span>
            </div>
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Phone</span>
                <a href="tel:{{ $buyerPhone }}" class="font-medium text-dark_green">{{ $buyerPhone }}</a>
            </div>
        </div>
    </div>

    {{-- ── 2. Item Sold ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-bag-check-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Item Sold</span>
        </div>
        <div class="flex items-center gap-3 px-4 py-3">
            {{-- Ad thumbnail --}}
            <div class="flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden bg-gray-100">
                @if($ad->advert && $ad->advert->hasMedia('images'))
                    <img class="w-full h-full object-cover"
                         src="{{ $ad->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                         onerror="this.src='{{ asset('frontend/images/default.png') }}'">
                @else
                    <img class="w-full h-full object-cover" src="{{ asset('frontend/images/default.png') }}" alt="Product">
                @endif
            </div>
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Product Name</div>
                <div class="font-semibold text-gray-800 leading-snug">{{ $productName }}</div>
            </div>
        </div>
    </div>

    {{-- ── 3. Selected Shipping ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-truck text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Selected Shipping</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    @if($shipLogo)
                        <img src="{{ $shipLogo }}" alt="{{ $shipCompany }}" class="h-7 max-w-[70px] object-contain flex-shrink-0">
                    @endif
                    <div>
                        <div class="text-xs text-gray-400 mb-0.5">Company</div>
                        <div class="font-semibold text-gray-800">{{ $shipCompany }}</div>
                    </div>
                </div>
                @if($shipStatus === 'delivered' || $isDelivered)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        <i class="bi bi-check-circle-fill"></i> Delivered
                    </span>
                @elseif($shipStatus === 'shipped')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                        <i class="bi bi-truck"></i> Shipped
                    </span>
                @elseif($shipStatus === 'pickup')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                        <i class="bi bi-shop"></i> Ready for Pickup
                    </span>
                @elseif($shipStatus === 'canceled')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                        <i class="bi bi-x-circle-fill"></i> Canceled
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                        <i class="bi bi-clock"></i> Pending
                    </span>
                @endif
            </div>
            @if($trackingId)
            <div class="border-t border-gray-100 pt-2">
                <div class="text-xs text-gray-400 mb-0.5">Tracking ID</div>
                <div class="font-semibold text-gray-800 font-mono">{{ $trackingId }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── 4. Delivery / Pickup Information ── --}}
    @if($address || $city || $stateName)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-geo-alt-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Selected Delivery / Pickup Location</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            @if($address)
            <div class="flex justify-between gap-4">
                <span class="text-gray-400 flex-shrink-0">Address</span>
                <span class="font-medium text-gray-800 text-right">{{ $address }}</span>
            </div>
            @endif
            @if($city)
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">City</span>
                <span class="font-medium text-gray-800">{{ $city }}</span>
            </div>
            @endif
            @if($stateName)
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">State</span>
                <span class="font-medium text-gray-800">{{ $stateName }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── 5. It's Time to Ship ── --}}
    @if(!$isDelivered)
    <div class="bg-white rounded-xl border border-green-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border-b border-green-100">
            <i class="bi bi-box-seam-fill text-dark_green"></i>
            <span class="font-semibold text-dark_green text-xs uppercase tracking-wide">It's Time to Ship</span>
        </div>
        <div class="px-4 py-4 space-y-4">
            <p class="text-gray-600 text-xs leading-relaxed">
                To complete the delivery, please follow these steps:
            </p>

            <ol class="space-y-3">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-dark_green text-white text-[10px] font-bold flex items-center justify-center mt-0.5">1</span>
                    <p class="text-gray-700 text-xs leading-relaxed">
                        Ensure the item is well-packaged and clearly label it with the buyer's name, phone number, and delivery address to ensure it arrives in good condition.
                    </p>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-dark_green text-white text-[10px] font-bold flex items-center justify-center mt-0.5">2</span>
                    <p class="text-gray-700 text-xs leading-relaxed">
                        Take it to your nearest <strong>{{ $shipCompany }}</strong> office.
                    </p>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-dark_green text-white text-[10px] font-bold flex items-center justify-center mt-0.5">3</span>
                    <p class="text-gray-700 text-xs leading-relaxed">
                        Present the following <strong>10-digit shipping code</strong> at the counter to process the shipment:
                    </p>
                </li>
            </ol>

            {{-- Shipping code highlight --}}
            <div class="flex items-center justify-between bg-gray-50 border border-dashed border-gray-300 rounded-xl px-4 py-3 mt-1">
                <div>
                    <div class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Shipping Code</div>
                    <div class="text-xl font-black text-dark_green tracking-widest">{{ $shipCode }}</div>
                </div>
                <button onclick="copyShipCode('{{ $shipCode }}')"
                        class="flex items-center gap-1.5 text-xs font-semibold text-dark_green border border-dark_green rounded-lg px-3 py-1.5 hover:bg-green-50 transition-colors">
                    <i class="bi bi-clipboard" id="copy-icon"></i>
                    <span id="copy-label">Copy</span>
                </button>
            </div>

            <li class="flex gap-3 list-none">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-dark_green text-white text-[10px] font-bold flex items-center justify-center mt-0.5">4</span>
                <p class="text-gray-700 text-xs leading-relaxed">
                    <strong>No payment is required</strong> at the shipping office. All logistics fees have been covered.
                </p>
            </li>

            <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5 text-xs text-amber-800 leading-relaxed">
                <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                Please follow these instructions carefully to ensure smooth processing of your shipment.
            </div>
        </div>
    </div>
    @endif

    {{-- ── 6. Note ── --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-4 text-xs text-blue-800 leading-relaxed space-y-2">
        <p><i class="bi bi-shield-lock-fill mr-1"></i>
        <strong>Note:</strong> This transaction is secured by our Buy Direct service. Your payment will be released as soon as the buyer confirms delivery.</p>
        <p>If you have any questions or need assistance, feel free to <a href="/contact-us" class="font-semibold underline">contact our support team</a>.</p>
    </div>

    {{-- ── 7. Update Shipping Status ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-pencil-square text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Update Shipping Status</span>
        </div>
        <div class="px-4 py-4">
            <form method="POST" action="/user/update-shipping/{{ $ad->id }}">
                @csrf
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Current Status</label>
                <select name="seller_status"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-dark_green focus:border-transparent mb-3">
                    <option value="pending" {{ $sellerStatus === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="delivered" {{ $sellerStatus === 'delivered' ? 'selected' : '' }}>✅ Delivered</option>
                    <option value="canceled" {{ $sellerStatus === 'canceled' ? 'selected' : '' }}>❌ Canceled</option>
                </select>
                <button type="submit"
                        class="w-full py-2.5 bg-dark_green text-white font-semibold rounded-lg text-sm hover:bg-green-800 transition-colors">
                    Save Status
                </button>
            </form>
        </div>
    </div>

</section>

<script>
function copyShipCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        document.getElementById('copy-icon').className = 'bi bi-clipboard-check';
        document.getElementById('copy-label').textContent = 'Copied!';
        setTimeout(() => {
            document.getElementById('copy-icon').className = 'bi bi-clipboard';
            document.getElementById('copy-label').textContent = 'Copy';
        }, 2000);
    });
}
</script>

@include('user.layouts.footer')
