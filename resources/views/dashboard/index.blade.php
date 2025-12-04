@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 mx-auto p-3 mb-10 text-sm">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        <div class="flex justify-between items-center">
            <span>My Dashboard</span>
            <span class="btn btn-primary py-2"><a href="/user/boosted">Boosted Ad</a> </span>
        </div>
        @include('frontend.components.flash-message')
    </div>

        @include('dashboard.components.my-ads')
</section>



@include('dashboard.layouts.footer')
