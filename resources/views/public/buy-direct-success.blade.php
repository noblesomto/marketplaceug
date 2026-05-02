@include('public.layouts.header')
@include('public.layouts.nav')

<section class="w-full md:w-3/6 mx-auto p-4 pb-24 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-md p-8 text-center w-full max-w-md">

        <div class="flex justify-center mb-4">
            <div class="bg-green-100 rounded-full p-4">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Order Confirmed!</h1>

        @if(session('ad_title'))
            <p class="text-gray-600 mb-1">
                You successfully purchased <strong>{{ session('ad_title') }}</strong>.
            </p>
        @else
            <p class="text-gray-600 mb-1">Your order has been placed successfully.</p>
        @endif

        @if(session('amount'))
            <p class="text-gray-500 text-sm mb-1">Amount paid: <strong>₦{{ session('amount') }}</strong></p>
        @endif

        @if(session('ship_code'))
            <p class="text-gray-500 text-sm mb-4">
                Shipment code: <strong class="font-mono tracking-widest">{{ session('ship_code') }}</strong>
            </p>
        @endif

        <p class="text-gray-500 text-sm mb-6">
            A confirmation email has been sent to you and the seller. Keep your shipment code safe — you'll need it to track your delivery.
        </p>

        <a href="/user/index"
           class="inline-block bg-dark_green hover:opacity-90 text-white px-8 py-3 rounded-full font-semibold transition">
            Go to Dashboard
        </a>
    </div>
</section>

@include('public.layouts.footer')
