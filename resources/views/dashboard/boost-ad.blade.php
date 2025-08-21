@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full max-w-4xl mx-auto pb-20">
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100 p-4 md:p-6">
        <!-- Header Section -->
        <div class="bg-dark_green text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Boost Your Advert</h1>
            @include('frontend.components.flash-message')
        </div>

        <!-- Content Section -->
        <div class="p-6">
            <form method="POST" action="/boost/pay" class="space-y-6" onsubmit="setBoostName()">
                @csrf

                <!-- Ad Details Section -->
                <div>
                    <h2 class="text-lg font-semibold text-dark_green mb-4">Ad Details</h2>
                    <div class="flex flex-col md:flex-row gap-6 items-start">
                        <img class="w-full md:w-48 h-48 object-cover rounded-lg border border-gray-200 shadow-sm"
                             src="{{ asset('uploads/images/'.$advert->firstImage->image) }}"
                             alt="{{ $advert->ad_title }}">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ $advert->ad_title }}</h3>
                            <div class="mt-2 text-sm text-gray-600">

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boost Options Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-semibold text-dark_green mb-4">Boost Options</h2>

                    <div class="space-y-4">
                       <!-- Boost Type -->
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Boost Type *</label>
    <select name="boost_type" id="boost_type"
      class="w-full px-4 py-2 border rounded shadow-sm"
      onchange="setBoostName(); calculatePrice();" required>
      <option value="">Select Boost</option>
      <option value="214.285" class="capitalize">highlight</option>
      <option value="500">repeated</option>
      <option value="1071.428">top</option>
      <option value="1428.571">gallery</option>
    </select>
    <input type="hidden" name="boost_name" id="boost_name">
  </div>

  <!-- Duration -->
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Duration *</label>
    <select name="duration" id="duration"
      class="w-full px-4 py-2 border rounded shadow-sm"
      onchange="calculatePrice();" required>
      <option value="">Select Duration</option>
      <option value="7">7 Days (Standard)</option>
      <option value="14">14 Days (3% discount)</option>
      <option value="30">30 Days (5% discount)</option>
      <option value="90">90 Days (7% discount)</option>
      <option value="120">120 Days (10% discount)</option>
    </select>
  </div>

  <!-- Price Display -->
  <div class="bg-gray-50 p-4 rounded-md border">
    <label class="block text-sm font-medium text-gray-700 mb-1">Total Price</label>
    <div class="flex items-center">
      <span class="text-2xl font-bold text-[#1a5276]">₦</span>
      <input type="text" id="amount" name="amount" readonly
        class="w-full bg-transparent border-none text-2xl font-bold focus:ring-0 pl-2">
    </div>
    <p class="text-xs text-gray-500 mt-1">Automatically calculated</p>
  </div>

  <input type="hidden" id="basePrice" value="{{ $price }}">
  <input type="hidden" name="advert_id" value="{{ $advert->id }}">

  <div class="flex justify-end pt-6 border-t">
    <button type="submit" class="px-8 py-3 bg-dark_green text-white rounded-md flex items-center space-x-2">
      <span>Boost Ad Now</span>
      <!-- svg icon -->
    </button>
  </div>
</form>
        </div>
    </div>
</section>

<script>
  function setBoostName() {
    const boostSelect = document.getElementById('boost_type');
    document.getElementById('boost_name').value =
      boostSelect.options[boostSelect.selectedIndex].text;
  }

  function calculatePrice() {
    const boostValue = parseFloat(document.getElementById('boost_type').value) || 0;
    const days = parseInt(document.getElementById('duration').value) || 0;

    if (!boostValue || !days) {
      document.getElementById('amount').value = '';
      return;
    }

    // Discount lookup
    const discounts = {
      14: 0.03,
      30: 0.05,
      90: 0.07,
      120: 0.10
    };

    // Compute raw and discounted total
    const rawTotal = boostValue * days;
    const discountRate = discounts[days] || 0;
    const finalTotal = rawTotal * (1 - discountRate);

    // Format with commas, no decimals
    document.getElementById('amount').value =
      Math.round(finalTotal).toLocaleString();
  }
</script>

@include('dashboard.layouts.footer')
