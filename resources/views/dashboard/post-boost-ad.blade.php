@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('dashboard.layouts.back-nav')
@include('dashboard.layouts.search')

<section class="w-full max-w-4xl mx-auto p-4 md:p-6 pb-20">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Header Section -->
        <div class="bg-dark_green text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Boost Your Advert</h1>
            @include('frontend.components.flash-message')
        </div>

        <!-- Content Section -->
        <div class="p-6">
            <form method="POST" action="/post-boost/pay" class="space-y-6">
                @csrf

                <!-- Ad Details Section -->
                <div>
                    <h2 class="text-lg font-semibold text-dark_green mb-4">Ad Details</h2>
                    <div class="flex flex-col md:flex-row gap-6 items-start">
                        <img class="w-full md:w-48 h-48 object-contain rounded-lg border border-gray-200"
                             src="{{ $advert->getFirstMediaUrl('images', 'thumbnail') }}"
                             alt="{{ $advert->ad_title }}">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ $advert->ad_title }}</h3>
                            <p class="text-gray-600 mt-2">Status: {{ $advert->status }}</p>
                            <p class="text-gray-600">Posted: {{ $advert->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Boost Options Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-semibold text-dark_green mb-4">Boost Options</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Ad Boost *</label>
                            @if ($errors->has('promotion'))
                                <p class="text-red-600 text-sm mb-2">{{ $errors->first('promotion') }}</p>
                            @endif

                            <select name="promotion" id="promotion"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-dark_green focus:border-dark_green"
                                    required>
                                <option value="">Select Boost Option</option>
                                <option value="1500" {{ $promotion == 'highlight' ? 'selected' : '' }}>Highlight - ₦1,500 (7 days)</option>
                                <option value="3500" {{ $promotion == 'repeated' ? 'selected' : '' }}>Repeated Pushing Up - ₦3,500 (7 days)</option>
                                <option value="7500" {{ $promotion == 'top' ? 'selected' : '' }}>Top Ad - ₦7,500 (14 days)</option>
                                <option value="10000" {{ $promotion == 'gallery' ? 'selected' : '' }}>Gallery - ₦10,000 (14 days)</option>
                            </select>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-md">
                            <h3 class="font-medium text-blue-800">What does boosting do?</h3>
                            <ul class="list-disc list-inside text-sm text-blue-700 mt-2 space-y-1">
                                <li>Increases visibility of your ad</li>
                                <li>Appears in premium positions</li>
                                <li>Gets more clicks and responses</li>
                            </ul>
                        </div>
                    </div>

                    <input type="hidden" name="duration" value="7">
                    <input type="hidden" name="advert_id" value="{{ $advert->id }}">
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="/user/my-ads"
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 flex items-center justify-center space-x-2">
                        <span>Boost Later</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                        </svg>
                    </a>

                    <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-white bg-dark_green hover:bg-green-700 flex items-center justify-center space-x-2 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-dark_green">
                        <span>Boost Ad Now</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

@include('dashboard.layouts.footer')
