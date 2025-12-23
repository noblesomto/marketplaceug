<article itemscope itemtype="https://schema.org/Product" class="pb-10">

    <!-- Header: Title, Price, Meta -->
    <header class="border-b border-gray-100 pb-4 px-2 bg-white">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
            <div class="flex-1">
                <div class="flex flex-wrap gap-2 mb-3">
                    @if ($ad->featured == 'Yes')
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2.5 py-0.5 rounded border border-yellow-200 uppercase tracking-wide">Promoted</span>
                    @endif
                    @if ($ad->sold == 'Yes')
                        <span class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded border border-red-200 uppercase tracking-wide">Sold</span>
                    @endif
                    <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-0.5 rounded border border-gray-200">
                       AD ID: {{ $ad->ad_id }}
                    </span>
                </div>

                <h1 itemprop="name" class="text-lg md:text-2xl font-semibold text-gray-900 leading-tight mb-2">
                    {{ $ad->ad_title ?? '' }}
                </h1>

                @if($isMobile)
                    <div class="my-2 md:mt-0 md:text-right">
                        @if($ad->category==3)
                            <p class="text-lg font-bold text-dark_green">{{ $ad->salary ?? '' }}</p>
                            <p class="text-sm text-gray-500">Salary</p>
                        @elseif($ad->category==18)
                            <p class="text-lg font-bold text-dark_green">{{ $ad->expected_salary ?? '' }}</p>
                        @elseif($ad->contact_price=="yes")
                            <p class="text-lg font-bold text-dark_green">Contact for Price</p>
                        @else
                            <div class="flex justify-between md:items-end">
                                <p class="text-lg font-bold text-dark_green tracking-tight">
                                    ₦{{ number_format($ad->price ?? 0, 0, '.', ',') }}
                                </p>
                                @if($ad->price_type)
                                    <span class="text-sm text-gray-500 font-medium bg-gray-50 px-2 py-0.5 rounded">{{ $ad->price_type }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                <div class="flex items-center" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>{{ $ad->lga ?? '' }}, {{ $ad->state ?? '' }}</span>
                    </div>
                <div class="flex items-center text-sm text-gray-500 gap-4 mt-2">

                    <div class="flex items-center" data-nosnippet>
                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <time datetime="{{ $ad->created_at }}">{{ $ad->created_at ? date('j M Y', strtotime($ad->created_at)) : '' }}</time>
                    </div>
                    <div class="flex items-center" data-nosnippet>
                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <span>{{ $ad->views ?? 0 }} views</span>
                    </div>
                </div>
            </div>

            <!-- Price Block -->
            @if(!$isMobile)
            <div class="mt-4 md:mt-0 md:text-right">
                @if($ad->category==3)
                    <p class="text-xl font-bold text-dark_green">{{ $ad->salary ?? '' }}</p>
                    <p class="text-sm text-gray-500">Salary</p>
                @elseif($ad->category==18)
                    <p class="text-xl font-bold text-dark_green">{{ $ad->expected_salary ?? '' }}</p>
                @elseif($ad->contact_price=="yes")
                    <p class="text-xl font-bold text-dark_green">Contact for Price</p>
                @else
                    <div class="flex flex-col md:items-end">
                        <p class="text-xl font-bold text-dark_green tracking-tight">
                            ₦{{ number_format($ad->price ?? 0, 0, '.', ',') }}
                        </p>
                        @if($ad->price_type)
                            <span class="text-sm text-gray-500 font-medium bg-gray-50 px-2 py-0.5 rounded">{{ $ad->price_type }}</span>
                        @endif
                    </div>
                @endif
            </div>
            @endif
        </div>
    </header>

    @if(!in_array($ad->category, [3, 11, 18]))
    <!-- Specs Grid (Replaces old definition lists) -->
    <section class="mt-8 px-2">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-dark_green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            Specifications
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 ">

                <!-- Universal Condition -->
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Condition</span>
                    <span class="font-semibold text-gray-800">
                        @if($ad->sub_category == 2)
                            {{ $car->condition ?? 'N/A' }}
                        @elseif($ad->sub_category == 6)
                            {{ $phone->condition ?? 'N/A' }}
                        @else
                            {{ $ad->item_condition ?? 'N/A' }}
                        @endif
                    </span>
                </div>


            <!-- Vehicle Specifics -->
            @if($ad->sub_category=="2")
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Brand</span>
                    <span class="font-semibold text-gray-800">{{ optional($brand)->brand }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Model</span>
                    <span class="font-semibold text-gray-800">{{ optional($model)->model }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Mileage</span>
                    <span class="font-semibold text-gray-800">{{ $car->mileage ?? '-' }} Km</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Year</span>
                    <span class="font-semibold text-gray-800">{{ $car->year ?? '-' }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Transmission</span>
                    <span class="font-semibold text-gray-800">{{ $car->transmission ?? '-' }}</span>
                </div>
                 <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Fuel</span>
                    <span class="font-semibold text-gray-800">{{ $car->fuel ?? '-' }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Doors</span>
                    <span class="font-semibold text-gray-800">{{ $car->doors ?? '-' }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Color</span>
                    <span class="font-semibold text-gray-800">{{ $car->exterior_color ?? '-' }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Interior</span>
                    <span class="font-semibold text-gray-800">{{ $car->material_interior ?? '-' }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Type</span>
                    <span class="font-semibold text-gray-800">{{ $car->vehicle_type ?? '-' }}</span>
                </div>
            @endif

            <!-- Phone Specifics -->
            @if($ad->sub_category=="6")
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Brand</span>
                    <span class="font-semibold text-gray-800">{{ optional($brand)->brand }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Color</span>
                    <span class="font-semibold text-gray-800">{{ $phone->color ?? '-' }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 col-span-2">
                    <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Accessories</span>
                    <span class="font-semibold text-gray-800">{{ $phone->device ?? '-' }}</span>
                </div>
            @endif
        </div>

        <!-- Features Tags -->
        @if($ad->sub_category=="2")
            @php $interiors = array_filter(array_map(fn($i)=>trim(str_replace(['/', '"', '\\', '[]'], '', $i)), explode(',', $car->interior ?? ''))); @endphp
            @if(count($interiors))
            <div class="mt-6">
                <h3 class="text-sm font-bold text-gray-700 mb-3">Key Features</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($interiors as $i)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-dark_green border border-green-100">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ ucfirst($i) }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
        @endif
    </section>
    @endif

    <hr class="border-gray-100 my-1">

    <!-- Description -->
    <section class="mt-4 p-2">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-dark_green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            Description
        </h2>
        <div class="prose prose-sm sm:prose-base text-gray-700 max-w-none bg-gray-50 p-3" itemprop="description">
            {!! $ad->description ?? '' !!}
        </div>
    </section>


        @if ($isMobile)
        <div class="p-5 space-y-3">
            @if($cat->category !="Jobs")
                @if ($ad->buy_direct == 'Yes')
                <a href="/buy-direct/{{ $ad->ad_id }}" class="flex justify-center items-center w-full py-3 bg-secondary_dark text-white font-bold rounded-lg shadow hover:opacity-90 transition active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5 accent-bg_primary mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    Buy Direct (Secure)
                </a>
                @endif

                <div class="grid grid-cols-2 gap-3">
                     <a href="/chat/{{ $ad->id }}/{{ $ad->user_id }}" class="flex justify-center items-center py-2 border-2 border-green-600 text-green-700 font-bold rounded-lg hover:bg-green-50 transition active:scale-95">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        Chat
                    </a>
                    @if($ad->show_contact=="Yes")
                    <button id="showContact" class="flex justify-center items-center py-2 border-2 border-blue-600 text-blue-700 font-bold rounded-lg hover:bg-blue-50 transition active:scale-95">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        Call
                    </button>
                    @endif
                </div>

                <div id="contactPhone" class="hidden mt-2 p-3 bg-blue-50 text-blue-900 rounded-lg text-center border border-blue-100">
                    @if(session()->get('user_id') =='')
                        <a href="/login" class="font-bold underline">Login to see number</a>
                    @else
                        <a href="tel:{{ $ad->owner->phone }}" class="text-xl font-bold">{{ $ad->owner->phone }}</a>
                    @endif
                </div>
            @endif
        </div>
        @endif

    <!-- Inline Application Form (For Jobs Only) -->
    @if(($cat->category ?? '') =="Jobs" && $ad->sold != 'Yes')
        <div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-100">
            <h3 class="text-lg font-bold text-blue-900 mb-4">Apply for this Position</h3>
            <form method="POST" action="/apply/{{ $ad->id ?? '' }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Introduce yourself..." required></textarea>
                        @if ($errors->has('message'))<span class="text-red-500 text-xs">{{ $errors->first('message') }}</span>@endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" value="{{ session()->get('user_id') ? $user->name : '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-secondary_dark text-white font-semibold rounded-lg hover:bg-opacity-90 transition shadow-md">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    @endif
</article>
