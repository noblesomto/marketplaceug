<div class="flex justify-between h-16 px-5 -mb-4 ">
	<div class="flex items-center font-bold">
		Recent Listings
	</div>
	<div class="flex justify-end">
		<div class="text-dark_green text-sm font-semibold mr-3 flex items-center"><a href="/user/post-ad">Place Ad Here</a> </div>
	
	</div>
</div>

<div class="container mx-auto">
	<div class="container mx-auto">
    <div id="listings-container" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
        @foreach ($listings as $row)
            @include('frontend.components.advert.advert-card', ['row' => $row])
        @endforeach
    </div>
    <div id="loading" class="text-center py-4 hidden">Loading...</div>

    <!-- The sentinel (invisible div at bottom) -->
    <div id="load-more-trigger" class="h-1"></div>
</div>


</div>

