@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('dashboard.layouts.back-nav')
@include('dashboard.layouts.search')

<!-- Custom CSS for consistent iOS Dropdowns -->
<style>
    /* Removes the ugly default iOS gradient/gloss and adds a custom arrow */
    .custom-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 1rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }
    /* Prevents iOS from zooming in on focus */
    input, select, textarea {
        font-size: 16px !important;
    }
</style>

<section class="max-w-4xl mx-auto my-4 px-1 sm:px-1 pb-20">

    <!-- Page Title -->
    <div class="mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-dark_green">Create a New Ad</h1>
        <p class="text-gray-500 mt-1">Fill in the details below to publish your Ad.</p>
    </div>

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were issues with your submission:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="/user/post-ad" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- CARD 1: Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-3 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Basic Information</h2>
            </div>
            <div class="p-3 md:p-4 space-y-6">

                <!-- Ad Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">What do you want to do?</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-green-50 hover:border-dark_green transition-all has-[:checked]:border-dark_green has-[:checked]:bg-green-50 has-[:checked]:ring-1 has-[:checked]:ring-dark_green">
                            <input type="radio" name="ad_type" value="Private" class="w-5 h-5 text-dark_green border-gray-300 focus:ring-dark_green" required>
                            <span class="ml-3 block text-gray-900 font-medium">I offer (Selling)</span>
                        </label>
                        <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-green-50 hover:border-dark_green transition-all has-[:checked]:border-dark_green has-[:checked]:bg-green-50 has-[:checked]:ring-1 has-[:checked]:ring-dark_green">
                            <input type="radio" name="ad_type" value="Commercial" class="w-5 h-5 text-dark_green border-gray-300 focus:ring-dark_green">
                            <span class="ml-3 block text-gray-900 font-medium">I'm looking for (Request)</span>
                        </label>
                    </div>
                </div>

                <!-- Title -->
                <div>
                    <label for="ad_title" class="block text-sm font-semibold text-gray-700 mb-2">Ad Title <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text"
                               name="ad_title"
                               id="ad_title"
                               placeholder="e.g. iPhone 14 Pro Max 256GB"
                               class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-dark_green focus:border-transparent transition-colors text-base"
                               value="{{ old('ad_title') }}"
                               maxlength="75"
                               required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-xs text-gray-400 bg-white pl-1"><span id="char-count">0</span>/75</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">A precise title helps you sell faster.</p>
                </div>

                <!-- Category Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                        <select id="category" name="category" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subcategory -->
                    <div>
                        <label for="subcategory" class="block text-sm font-semibold text-gray-700 mb-2">Sub Category <span class="text-red-500">*</span></label>
                        <select id="subcategory" name="subcategory" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" onchange="showHideDiv()" required>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>

                    <!-- Brand -->
                    <div>
                        <label for="brand" class="block text-sm font-semibold text-gray-700 mb-2">Brand <span class="text-red-500">*</span></label>
                        <select id="brand" name="brand" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" required>
                            <option value="">Select Options</option>
                        </select>
                    </div>

                    <!-- Model (Hidden by default) -->
                    <div id="divModel" class="hidden">
                        <label for="model" class="block text-sm font-semibold text-gray-700 mb-2">Model</label>
                        <select id="model" name="model" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Select Model</option>
                        </select>
                    </div>

                    <!-- Item Condition -->
                    <div id="itemCondition">
                        <label for="pr" class="block text-sm font-semibold text-gray-700 mb-2">Item Condition <span class="text-red-500">*</span></label>
                        <select id="pr" name="item_condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="New">New</option>
                            <option value="Foreign Used">Foreign Used</option>
                            <option value="Locally Used">Locally Used</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: Vehicle Details (Hidden) -->
        <!-- Logic Note: ID "divCar" is required for JS to toggle visibility -->
        <div id="divCar" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Vehicle Specifics</h2>
            </div>
            <div class="p-3 md:p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- Vehicle Condition -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Vehicle Condition <span class="text-red-500">*</span></label>
                        <select name="condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Local used">Local used</option>
                            <option value="Foreign used">Foreign used</option>
                            <option value="Brand new">Brand new</option>
                        </select>
                    </div>

                    <!-- Mileage -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mileage (Km) </label>
                        <div class="flex">
                            <input type="text" name="mileage" placeholder="0" value="{{ old('mileage') }}" class="block w-full px-4 py-3 rounded-l-lg border border-gray-300 focus:ring-dark_green focus:border-dark_green text-base">
                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border border-gray-300 bg-gray-50 text-gray-500 text-sm">Km</span>
                        </div>
                    </div>



                    <!-- Registration -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Registration <span class="text-red-500">*</span></label>
                        <select name="registration" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">--Select Type--</option>
                            <option value="Registered">Registered</option>
                            <option value="Unregistered">Unregistered</option>
                        </select>
                    </div>

                    <!-- Fuel -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fuel Type <span class="text-red-500">*</span></label>
                        <select name="fuel" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Natural gas CNG">Natural gas CNG</option>
                            <option value="LPG">LPG</option>
                            <option value="Hybrid">Hybrid</option>
                            <option value="Electric">Electric</option>
                        </select>
                    </div>

                    <!-- Transmission -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Transmission <span class="text-red-500">*</span></label>
                        <select name="transmission" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                            <option value="CVT">CVT</option>
                            <option value="AMT">AMT</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Vehicle Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Body Type </label>
                        <select name="vehicle_type" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Small Car">Small Car</option>
                            <option value="Station Wagon">Station Wagon</option>
                            <option value="Limousine">Limousine</option>
                            <option value="Convertible">Convertible</option>
                            <option value="SUV/Off Road Vehicle">SUV/Off Road Vehicle</option>
                            <option value="Van/Bus">Van/Bus</option>
                            <option value="Coupe">Coupe</option>
                            <option value="Truck">Truck</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Exterior Color <span class="text-red-500">*</span></label>
                        <select id="Carcolor" name="exterior_color" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Choose Color</option>
                            @php
                                $carColors = ['Black' => 'Black','White' => 'White','Gray' => 'Gray','Silver' => 'Silver','Blue' => 'Blue','Red' => 'Red','Gold' => 'Gold','Green' => 'Green','Beige' => 'Beige','Brown' => 'Brown','Yellow' => 'Yellow','Orange' => 'Orange','Purple' => 'Purple','Maroon' => 'Maroon','Burgundy' => 'Burgundy','Bronze' => 'Bronze','Champagne' => 'Champagne','Pearl White' => 'Pearl White','Gunmetal' => 'Gunmetal Gray','Midnight Blue' => 'Midnight Blue','Navy Blue' => 'Navy Blue','Olive Green' => 'Olive Green','Charcoal' => 'Charcoal','Matte Black' => 'Matte Black','Two Tone' => 'Two-Tone','Gradient' => 'Gradient','Chameleon' => 'Chameleon','Custom Wrap' => 'Custom Wrap','Chrome' => 'Chrome','Camo' => 'Camouflage','Others' => 'Others'];
                            @endphp
                            @foreach($carColors as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Doors -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Doors </label>
                        <select name="doors" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="1 Door">1 Door</option>
                            <option value="2 Doors">2 Doors</option>
                            <option value="3 Doors">3 Doors</option>
                            <option value="4 Doors">4 Doors</option>
                        </select>
                    </div>

                    <!-- Interior Material -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Interior Material</label>
                        <select name="material_interior" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Full Grain Leather">Full Grain Leather</option>
                            <option value="Partial Leather">Partial Leather</option>
                            <option value="Material">Material</option>
                            <option value="Velour">Velour</option>
                            <option value="Alcantara">Alcantara</option>
                        </select>
                    </div>

                </div>

                <!-- Equipment Section -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-md font-bold text-gray-800 mb-4">Features & Equipment</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Exterior Eq -->
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Exterior</span>
                            <div class="space-y-3">
                                @foreach(['Trailer hitch', 'Parking assistance', 'Alloy wheels', 'Xenon/LED headlights'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="exterior_equipment[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border-gray-300 focus:ring-dark_green cursor-pointer">
                                    <span class="ml-3 text-sm text-gray-700 group-hover:text-dark_green transition-colors">{{ $item }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Interior Eq -->
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Interior</span>
                            <div class="space-y-3">
                                @foreach(['Air conditioning', 'Navigation system', 'Radio/tuner', 'Bluetooth', 'Hands-free system', 'Sunroof/panoramic roof', 'Seat heating', 'Cruise control', 'Non-smoking vehicle'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="interior[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border-gray-300 focus:ring-dark_green cursor-pointer">
                                    <span class="ml-3 text-sm text-gray-700 group-hover:text-dark_green transition-colors">{{ $item }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Security -->
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Security</span>
                            <div class="space-y-3">
                                @foreach(['Anti-lock braking system (ABS)', 'Service history maintained'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="security[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border-gray-300 focus:ring-dark_green cursor-pointer">
                                    <span class="ml-3 text-sm text-gray-700 group-hover:text-dark_green transition-colors">{{ $item }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: Phone Details (Hidden) -->
        <!-- Logic Note: ID "divPhone" is required for JS -->
        <div id="divPhone" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Device Details</h2>
            </div>
            <div class="p-3 md:p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Phone Color -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Color <span class="text-red-500">*</span></label>
                        <select name="phone_color" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Choose Color</option>
                            <option>Black</option><option>White</option><option>Gray</option><option>Silver</option><option>Gold</option><option>Blue</option><option>Red</option><option>Green</option><option>Yellow</option><option>Orange</option><option>Purple</option><option>Pink</option><option>Rose Gold</option><option>Bronze</option><option>Copper</option><option>Midnight</option><option>Space Gray</option><option>Midnight Green</option><option>Lavender</option><option>Aqua</option><option>Teal</option><option>Turquoise</option><option>Coral</option><option>Champagne</option><option>Graphite</option><option>Starlight</option><option>Twilight</option><option>Gradient</option><option>Transparent</option><option>Others</option>
                        </select>
                        @if ($errors->has('phone_color')) <p class="text-xs text-red-500 mt-1">{{ $errors->first('phone_color') }}</p> @endif
                    </div>

                    <!-- Device Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Device Type <span class="text-red-500">*</span></label>
                        <select name="device" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Device">Device</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Device & Accessories">Device & Accessories</option>
                        </select>
                        @if ($errors->has('device')) <p class="text-xs text-red-500 mt-1">{{ $errors->first('device') }}</p> @endif
                    </div>

                    <!-- Condition -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Condition <span class="text-red-500">*</span></label>
                        <select name="phone_condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="New - Unboxed">New (Unboxed)</option>
                            <option value="Foreign Used - No Packaging">Foreign Used (No Packaging)</option>
                            <option value="Used - Very Good">Used - Very Good</option>
                            <option value="Used - Good">Used - Good</option>
                            <option value="Used - In Order">Used - In Order</option>
                            <option value="Used - Defect">Used - Defect</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <!-- CARD 4: Pricing & Salary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Financials</h2>
            </div>
            <div class="p-3 md:p-4 space-y-6">

                <!-- Price Section (Logic controlled by JS via #price ID) -->
                <div id="price">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Price</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                        <!-- Amount Input -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-base">₦</span>
                            </div>
                            <input type="text" name="price_display" id="price_display" class="block w-full pl-8 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-dark_green focus:border-dark_green text-base" placeholder="0.00" value="{{ old('price') ? number_format(old('price'), 0, '.', ',') : '' }}">
                            <input type="hidden" name="price" id="price_hidden" value="{{ old('price') }}">
                        </div>

                        <!-- Price Type -->
                        <div>
                            <select name="price_type" class="custom-select block w-full px-4 py-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                                <option value="Fixed">Fixed Price</option>
                                <option value="Negotiable">Negotiable</option>
                                <option value="Give Away">Give Away</option>
                            </select>
                        </div>

                        <!-- Contact for Price -->
                        <div id="services" class="hidden flex items-center h-full pt-1">
                             <input type="hidden" name="contact_price" value="no">
                             <label class="flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="contact_price" value="yes" class="w-5 h-5 text-dark_green rounded border border-gray-300 focus:ring-dark_green">
                                <span class="ml-2 text-sm text-gray-700">Contact for Price</span>
                            </label>
                        </div>
                    </div>
                </div>


                <!-- Salary (Hidden by default, shown via JS) -->
                <div id="salary" class="hidden">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Salary</label>
                     <select name="salary" class="custom-select block w-full md:w-1/2 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                        <option value="">--Select Salary--</option>
                        <option value="Commission">Commission</option>
                        <option value="Below ₦20,000">Below ₦20,000</option>
                        <option value="₦20,000 - ₦40,000">₦20,000 - ₦40,000</option>
                        <option value="₦40,000 - ₦60,000">₦40,000 - ₦60,000</option>
                        <option value="₦60,000 - ₦80,000">₦60,000 - ₦80,000</option>
                        <option value="₦80,000 - ₦100,000">₦80,000 - ₦100,000</option>
                        <option value="₦100,000 - ₦120,000">₦100,000 - ₦120,000</option>
                        <option value="₦120,000 - ₦140,000">₦120,000 - ₦140,000</option>
                        <option value="₦140,000 - ₦160,000">₦140,000 - ₦160,000</option>
                        <option value="₦160,000 - ₦180,000">₦160,000 - ₦180,000</option>
                        <option value="₦180,000 - ₦200,000">₦180,000 - ₦200,000</option>
                        <option value="₦200,000 - ₦220,000">₦200,000 - ₦220,000</option>
                        <option value="₦220,000 - ₦250,000">₦220,000 - ₦250,000</option>
                        <option value="₦250,000 - ₦300,000">₦250,000 - ₦300,000</option>
                        <option value="₦300,000 - ₦350,000">₦300,000 - ₦350,000</option>
                        <option value="₦350,000 - ₦400,000">₦350,000 - ₦400,000</option>
                        <option value="₦400,000 - ₦450,000">₦400,000 - ₦450,000</option>
                        <option value="₦450,000 - ₦500,000">₦450,000 - ₦500,000</option>
                        <option value="Above ₦500,000">Above ₦500,000</option>
                    </select>
                </div>

                <!-- Expected Salary (Hidden by default, shown via JS) -->
                <div id="expectedSalary" class="hidden">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Expected Salary</label>
                     <select name="expected_salary" class="custom-select block w-full md:w-1/2 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                        <option value="">--Select Expected Salary--</option>
                        <option value="Below ₦50,000">Below ₦50,000</option>
                        <option value="₦50,000 - ₦75,000">₦50,000 - ₦75,000</option>
                        <option value="₦75,000 - ₦100,000">₦75,000 - ₦100,000</option>
                        <option value="₦100,000 - ₦120,000">₦100,000 - ₦120,000</option>
                        <option value="₦120,000 - ₦140,000">₦120,000 - ₦140,000</option>
                        <option value="₦140,000 - ₦160,000">₦140,000 - ₦160,000</option>
                        <option value="₦160,000 - ₦180,000">₦160,000 - ₦180,000</option>
                        <option value="₦180,000 - ₦200,000">₦180,000 - ₦200,000</option>
                        <option value="₦200,000 - ₦220,000">₦200,000 - ₦220,000</option>
                        <option value="₦220,000 - ₦250,000">₦220,000 - ₦250,000</option>
                        <option value="₦250,000 - ₦300,000">₦250,000 - ₦300,000</option>
                        <option value="₦300,000 - ₦350,000">₦300,000 - ₦350,000</option>
                        <option value="₦350,000 - ₦400,000">₦350,000 - ₦400,000</option>
                        <option value="₦400,000 - ₦450,000">₦400,000 - ₦450,000</option>
                        <option value="₦450,000 - ₦500,000">₦450,000 - ₦500,000</option>
                        <option value="Above ₦500,000">Above ₦500,000</option>
                    </select>
                </div>

                <!-- Quantity (Restored) -->
                @if($user->acc_type=="Commercial")
                <div id="quantity">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Item Quantity</label>
                    <input type="number" name="quantity" placeholder="Item Quantity" class="block w-full md:w-1/3 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent text-base" value="1" min="1" max="100">
                </div>
                @endif
            </div>
        </div>

        <!-- CARD 5: Shipment & Buy Direct -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lgfont-semibold text-gray-800">Logistics & Payment</h2>
            </div>
            <div class="p-3 md:p-4 space-y-8">

                <!-- Shipment Section -->
                <div id="shipment">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Delivery Options</label>
                    <div class="flex flex-col sm:flex-row gap-4 mb-4">
                        <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-dark_green has-[:checked]:bg-green-50">
                            <input type="radio" name="shipment" value="Ship" class="text-dark_green border-gray-300 focus:ring-dark_green h-4 w-4" onchange="toggleShipping()">
                            <span class="ml-2 text-gray-700 font-medium">Shipping Possible</span>
                        </label>
                        <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-dark_green has-[:checked]:bg-green-50">
                            <input checked type="radio" name="shipment" value="Pickup" class="text-dark_green border-gray-300 focus:ring-dark_green h-4 w-4" onchange="toggleShipping()">
                            <span class="ml-2 text-gray-700 font-medium">Pickup Only</span>
                        </label>
                    </div>

                    <!-- Hidden Shipping Methods -->
                    <div id="shipping" class="hidden pl-0 sm:pl-4 sm:border-l-2 sm:border-gray-200 space-y-2">
                        <p class="text-sm font-semibold text-gray-800 mb-2">Select Available Carriers:</p>
                        @foreach($shippings as $row)
                        <label class="flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input id="shipping-{{ $row->id }}" name="shipping[]" type="checkbox" value="{{ $row->id }}" class="mt-1 h-5 w-5 text-dark_green border-gray-300 rounded focus:ring-dark_green">
                            <div class="ml-3">
                                <div class="flex items-center">
                                    <img class="w-8 h-auto mr-2" src="{{ $row->logo }}" alt="Logo">
                                    <span class="font-bold text-gray-900">{{ $row->company }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Max. {{ $row->weight }} kg, {{ $row->description }}</p>
                            </div>
                        </label>
                        @endforeach
                        <p id="shipping-error" class="text-red-500 text-sm hidden">Please select at least one shipping method.</p>
                    </div>
                </div>

                <!-- Buy Direct Section -->
                <div id="buyDirect" class="bg-blue-50 border border-blue-100 rounded-xl p-5">
                    <label class="block text-md font-semibold text-blue-900 mb-4">Payment Method</label>

                    <div class="space-y-4">
                        <!-- Yes Option -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="radio" name="buy_direct" value="Yes" class="h-4 w-4 text-dark_green border-gray-300 focus:ring-dark_green" required>
                            </div>

                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">Enable "Buy Direct"</span>
                                <div class="mt-2 text-xs text-gray-600 space-y-2">
                                    <div class="flex">
                                        <span class="mr-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-600">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                            </svg>
                                        </span>
                                        <span>Selecting this option allows buyers to purchase your item using ‘Buy Direct.’ <br><a class="font-semibold text-dartk_green" href="/payments-refunds">Learn More</a> </span>
                                    </div>
                                    <p class="flex items-center"><svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Secure payment processing</p>
                                    <p class="flex items-center"><svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> Fixed price (no negotiation)</p>
                                </div>
                            </div>
                        </div>

                        <!-- No Option -->
                        <div class="flex items-start">
                             <div class="flex items-center h-5">
                                <input checked type="radio" name="buy_direct" value="No" class="h-4 w-4 text-dark_green border-gray-300 focus:ring-dark_green">
                            </div>
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">No, do not use "Buy direct"</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 6: Description & Images -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Visuals & Description</h2>
            </div>
            <div class="p-3 md:p-4 space-y-8">

                <!-- Description -->
                <div data-has-editor>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Detailed Description <span class="text-red-500">*</span></label>
                    <input id="content" type="hidden" name="description" value="{{ old('description') }}" required>
                    <div class="prose max-w-none">
                         <trix-editor input="content" class="min-h-[150px] border-gray-300 rounded-lg focus:border-dark_green focus:ring-dark_green"></trix-editor>
                    </div>
                    <div class="flex justify-end mt-1">
                        <span class="text-xs text-gray-400"><span id="word-count">0</span>/3500 characters</span>
                    </div>
                </div>

                <!-- IMPROVED IMAGE UPLOAD SECTION -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Product Photos</label>
                    <div id="image-error" class="hidden mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm font-medium"></div>

                    <!-- Dropzone -->
                    <div class="border-2 border-dashed border-gray-300 rounded-xl hover:bg-gray-50 hover:border-dark_green transition-colors relative group">
                        <label for="imageUpload" class="cursor-pointer flex flex-col items-center justify-center py-6 w-full h-full z-10">
                            <div class="p-4 rounded-full bg-blue-50 text-dark_green mb-3 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Click to upload or drag images here</span>
                            <span class="text-xs text-gray-500 mt-1">PNG, JPG up to 20MB (Max 20 images)</span>
                        </label>
                        <input id="imageUpload" name="images[]" type="file" multiple accept="image/*" class="hidden">
                    </div>

                    <!-- Sorting/Preview Area -->
                    <input type="hidden" name="image_order" id="image_order">
                    <div id="preview" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4 mt-6">
                        <!-- JS injects previews here -->
                    </div>

                    <div class="flex items-center text-xs text-gray-500 mt-3">
                         <img src="{{ asset('frontend/images/swap.png') }}" class="h-5 w-auto mr-2 opacity-60">
                         <span>Drag and drop thumbnails to reorder them.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 7: Location & Contact -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Location & Contact</h2>
            </div>
            <div class="p-3 md:p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- State -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                        <select onchange="toggleLGA(this);" name="state" id="state" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="" selected="selected">-- Select State --</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->name }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- LGA -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">LGA</label>
                        <select name="lga" id="lga" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white select-lga text-base" required>
                             <!-- Populated by JS -->
                        </select>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Profile Name</label>
                        <input type="text" id="name" name="name" value="{{ $user->name }}" readonly class="block w-full px-4 py-3 rounded-lg border  border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed text-base">
                        <p class="text-xs text-gray-400 mt-1">To change this, please edit your profile settings.</p>
                    </div>

                    <!-- Show Contact -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Show Phone Number on Ad?</label>
                        <select name="show_contact" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="pt-6">
            <div class="mb-6 bg-white p-2 rounded-xl">
                 @include('backend.components.post-boost')
            </div>

            <div class="flex flex-col items-center space-y-4 mx-2">
                <p class="text-xs text-center text-gray-500 max-w-lg">
                    By clicking "Post Ad", you agree to our Terms of Use and Privacy Policy. Please ensure your ad does not violate our community guidelines.
                </p>
                <button type="submit" class="bg-dark_green hover:bg-secondary_dark text-white font-semibold py-3 px-8 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full">
                    Post Ad
                </button>
            </div>
        </div>

    </form>
</section>
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="{{ asset('frontend/js/lga.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js'></script>

{{-- Database-Driven Category UI Configuration (2026-01-31) --}}
<script src="{{ asset('dashboard/js/category-ui-manager.js') }}"></script>
<script src="{{ asset('dashboard/js/post-ad-v2.js') }}"></script>
{{-- Old hardcoded version (kept for rollback): <script src="{{ asset('dashboard/js/post-ad.js') }}"></script> --}}

<script src="{{ asset('dashboard/js/word-count.js') }}"></script>
<script src="{{ asset('dashboard/js/sortable.js') }}"></script>
<script src="{{ asset('dashboard/js/submit.js') }}"></script>

@include('dashboard.layouts.footer')
