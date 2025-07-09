@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green">
        Boosted Advert
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
						<!-- Display Boost Records -->
						<h3>Active Boosts</h3>
						@if($advert->boost->isEmpty())
						    <p>No active boosts found.</p>
						@else
						    <ul>
						        @foreach($advert->boost as $boost)
						            <li>
						                Boost ID: {{ $boost->id }}<br>
						                Status: <span class="capitalize">{{ $boost->boost_status }}</span> <br>
						                Duration: {{ $boost->duration }} Days<br>
						            </li>
						        @endforeach
						    </ul>
						@endif



				     				        
				        </form>
				        
				    </div>
		            
		        </div>

   
</div>

    
</section>


@include('dashboard.layouts.footer')