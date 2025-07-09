@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green">
        Boost Advert
        @include('frontend.components.flash-message')
    </div>
    <div class="">
       	<div class="">
		            
		        	<div class="max-w-2xl mx-auto bg-white p-3 md:p-10  mb-20 rounded-lg">
				        <form method="POST" action="/boost/pay">
				            @csrf
				        <div class="mt-1 font-semibold text-xl">Ad Details:</div>

				        <div class="mb-4 mt-4">
				            
				            <img class="h-24 lg:h-40 object-cover" src="{{  asset('uploads/images/'.$advert->firstImage->image) }}">
				            <div class="font-medium leading-5 md:font-bold text-base md:text-xl md:mt-2"> {{ $advert->ad_title }}</div>
				        </div>

				        <div class="mb-4 mt-4">
						    <label class="text-sm font-semibold">Duration *</label>
						    @if ($errors->has('phone'))
						        <span class="text-red-700 py-1">{{ $errors->first('phone') }}</span>
						    @endif
						    <select name="duration" id="duration" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="calculatePrice()">
						        <option value="">Select Duration</option>
						        <option value="7">7 Days</option>
						        <option value="14">14 Days</option>
						        <option value="30">30 Days/1 Month</option>
						        <option value="90">90 Days/3 Months</option>
						        <option value="120">120 Days/6 Months</option>
						    </select>
						</div>

						<input type="hidden" id="basePrice" value="{{ $price }}">
						<input type="hidden" name="advert_id" value="{{ $advert->id }}">

						<div class="mb-6 mt-4">
						    <label class="text-sm font-semibold">Price *</label>
						    <input type="text" id="amount" name="amount" value="" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required readonly>
						</div>


				
		         				    

				          <div class="mt-8">
				            <button type="submit" class="btn btn-primary py-1 text-lg flex justify-center items-center">
				                <span>Boost Ad</span>
				                <span class="ml-2">
				                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
									  <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
									</svg>
				                </span>
				            </button>
				          </div>
				     				        
				        </form>
				        
				    </div>
		            
		        </div>

   
</div>

    
</section>

<script>
function calculatePrice() {
    const duration = document.getElementById('duration').value;
    const basePrice = parseFloat(document.getElementById('basePrice').value);
    const amountField = document.getElementById('amount');
    
    if (!duration) {
        amountField.value = '';
        return;
    }
    
    let price = basePrice;
    
    switch(duration) {
        case '7':
            // No discount for 7 days
            break;
        case '14':
            // 3% discount
            price = basePrice * 0.97;
            break;
        case '30':
            // 5% discount
            price = basePrice * 0.95;
            break;
        case '90':
            // 7% discount
            price = basePrice * 0.93;
            break;
        case '120':
            // 10% discount
            price = basePrice * 0.90;
            break;
    }
    
    // Multiply by the number of days (assuming the base price is daily)
    const totalPrice = price * parseInt(duration);
    
    // Format the price to 2 decimal places
    amountField.value = totalPrice.toFixed(2);
}
</script>

@include('dashboard.layouts.footer')