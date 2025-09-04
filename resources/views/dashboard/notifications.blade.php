@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6  mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        My Notifications
    </div>

    <div class="max-w-4xl mx-auto mt-4 rounded-lg">
        @forelse($groupedNotifications as $dateGroup => $notifications)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3 px-2">{{ $dateGroup }}</h3>

                @foreach($notifications as $row)
                    <a href="{{ url($row->advert->state_slug . '/' . $row->advert->title_slug .'/'. $row->advert->ad_id) }}" class="block">
                        <div class="flex p-2 border-b bg-white mb-2 hover:bg-gray-50">
                            <div class="flex-[30%] sm:flex-[15%]">
                                <img class="w-20 h-20 object-cover rounded-full" src="{{ $row->advert->firstImage ? asset('uploads/images/' . $row->advert->firstImage->image) : asset('frontend/images/default.png') }}" onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';">
                            </div>
                            <div class="flex-[70%] sm:flex-[85%]">
                                <strong>{{ $row->type }}</strong><br>
                                {{ $row->message }}<br>
                                <small class="text-gray-500">{{ $row->created_at->format('g:i A') }}</small>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @empty
            <div class="p-2 text-gray-500">No notifications</div>
        @endforelse
    </div>
</section>


@include('dashboard.layouts.footer')
