@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green">
        Update Advert
        @include('public.components.flash-message')
    </div>
    <div class="">
       	<div class="">
		            
		        	<div class="max-w-2xl mx-auto bg-white p-3 md:p-10  mb-20 rounded-lg space-y-3">
				        <div class="flex justify-between font-medium leading-5 md:font-bold text-base md:text-lg md:mt-2"> 
				        	<span>{{ $ad->advert->ad_title }}</span>
				        	<span class="capitalize">Shipping Status: {{ $ad->shipping_status }}</span>
				        </div>

				        <div class="mb-4 ">
				            
				            <img class="h-24 lg:h-40 object-cover" src="{{  asset('uploads/images/'.$ad->advert->firstImage->image) }}">
				            
				        </div>
						<!-- Display Boost Records -->
						<h3>Update Shipping Status</h3>

						<form method="POST" action="/user/update-shipping/{{ $ad->id }}" class="space-y-4">
							@csrf
							<div >
								<select name="shipping_status" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
									<option value="pending" {{ $ad->shipping_status == 'pending' ? 'selected' : '' }}>Pending</option>
									<option value="shipped" {{ $ad->shipping_status == 'shipped' ? 'selected' : '' }}>Shipped</option>
									<option value="delivered" {{ $ad->shipping_status == 'delivered' ? 'selected' : '' }}>Delivered</option>
								</select>
							</div>
							<button type="submit" class="btn btn-secondary py-2 px-6">Update</button>
						</form>



			
				    </div>
		            
		        </div>

   
</div>

    
</section>


@include('user.layouts.footer')
