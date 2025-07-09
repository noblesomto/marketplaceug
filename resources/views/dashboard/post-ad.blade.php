@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm mb-10">
    <div class="border-b-2 border-b-gray-200 pt-10 px-2 font-bold text-dark_green mb-2">
        Ad Details
        @include('frontend.components.flash-message')
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <p>There were some issues with your submission:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>

    <form method="POST" action="/user/post-ad" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Bid/request</div>
            </div>
            <div class="col-span-10 lg:col-span-6 ">
                <div class="flex justify-start w-64">
                  <label class="flex items-center  w-full">
                    <input type="radio" name="ad_type" value="Private" class="form-radio text-dark_green accent-dark_green"  required>
                    <span class="ml-2 ">I offer</span>
                  </label>

                  <label class="flex items-center w-64">
                    <input type="radio" name="ad_type" value="Commercial" class="form-radio text-dark_green accent-dark_green" >
                    <span class="ml-2 ">I'm looking for</span>
                  </label>
                </div>
            </div>
      </div>


      <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Title</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('ad_title'))
                    <span class="text-red-400">{{ $errors->first('ad_title') }}</span>
                @endif
            <input type="text" name="ad_title" placeholder="Ad Title" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ old('ad_title') }}" required>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span> With a meaningful title, you sell better.
                  </div>
            </div>
       </div>



       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Select Category </div>
            </div>
            <div class="col-span-10 md:col-span-8">

                <div class="grid grid-cols-6 gap-2">
                    <div class="col-span-6 md:col-span-2">
                        @if ($errors->has('category'))
                            <span class="text-red-400">{{ $errors->first('category') }}</span>
                        @endif
                        <label for="category" class="block text-gray-700">Category:</label>
                        <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required>
                            <option value="">Category</option>
                           @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        @if ($errors->has('subcategory'))
                            <span class="text-red-400">{{ $errors->first('subcategory') }}</span>
                        @endif
                        <label for="subcategory" class="block text-gray-700" onchange="showHideDiv()">Sub Category:</label>
                        <select id="subcategory" name="subcategory" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required>
                            <option value="" >Select Subcategory</option>
                        </select>
                    </div>

                     <div class="col-span-6 md:col-span-2">
                        @if ($errors->has('brand'))
                            <span class="text-red-400">{{ $errors->first('brand') }}</span>
                        @endif
                        <label for="brand" class="block text-gray-700">Select Brand:</label>
                        <select id="brand" name="brand" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required>
                            <option value="">Select Options</option>
                        </select>
                    </div>
                    <!--
                    <div id="divModel" class="col-span-6 md:col-span-2 hidden">
                        @if ($errors->has('model'))
                            <span class="text-red-400">{{ $errors->first('model') }}</span>
                        @endif
                        <label for="model" class="block text-gray-700">Select Model:</label>
                        <select id="model" name="model" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                            <option value="">Select Model</option>
                        </select>
                    </div>
                    -->

                </div>
            </div>
    
       </div>

        <!-- Div 1 (Initially Hidden) -->
    <div id="divCar" class="p-2 hidden">
        <div class="">
            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Mileage *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('mileage'))
                        <span class="text-red-400">{{ $errors->first('mileage') }}</span>
                    @endif
                    <div class="flex w-2/4">
                        <input type="text" name="mileage" placeholder="mileage" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ old('mileage') }}">
                        <span class="mt-3">Km</span>
                    </div>
                </div>
           </div>

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Vehicle Condition *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('condition'))
                        <span class="text-red-400">{{ $errors->first('condition') }}</span>
                    @endif
                <select id="pr" name="condition" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Damaged Car">Damaged Car</option>
                    <option value="Undamaged Car">Undamaged Car</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">First Registration *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('month'))
                        <span class="text-red-400">{{ $errors->first('month') }}</span>
                    @endif
                    @if ($errors->has('year'))
                        <span class="text-red-400">{{ $errors->first('year') }}</span>
                    @endif
                    <div class="flex w-2/4">
                        <select id="pr" name="month" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                            <option value="">Month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                        <input type="text" name="year" placeholder="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ old('year') }}">
                    </div>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Fuel Type *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('fuel'))
                        <span class="text-red-400">{{ $errors->first('fuel') }}</span>
                    @endif
                <select id="pr" name="fuel" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Natural gas CNG">Natural gas CNG</option>
                    <option value="LPG">LPG</option>
                    <option value="Hybrid">Hybrid</option>
                    <option value="Electric">Electric</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Transmission *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('transmission'))
                        <span class="text-red-400">{{ $errors->first('transmission') }}</span>
                    @endif
                <select id="pr" name="transmission" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Automatic">Automatic</option>
                    <option value="Manually">Manually</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Vehicle Type *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('vehicle_type'))
                        <span class="text-red-400">{{ $errors->first('vehicle_type') }}</span>
                    @endif
                <select id="pr" name="vehicle_type" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
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
           </div>

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Exterior Color *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('exterior_color'))
                        <span class="text-red-400">{{ $errors->first('exterior_color') }}</span>
                    @endif
                <select id="Phonecolor" name="exterior_color" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Choose Color</option>
                    <option value="black">Black</option>
                      <option value="white">White</option>
                      <option value="gray">Gray</option>
                      <option value="silver">Silver</option>
                      <option value="blue">Blue</option>
                      <option value="red">Red</option>
                      <option value="gold">Gold</option>
                      <option value="green">Green</option>
                </select>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Number of Doors *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('doors'))
                        <span class="text-red-400">{{ $errors->first('doors') }}</span>
                    @endif
                <select id="pr" name="doors" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="1 Door">1 Door</option>
                    <option value="2 Doors">2 Doors</option>
                    <option value="3 Doors">3 Doors</option>
                    <option value="4 Doors">4 Doors</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Material Interior *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('material_interior'))
                        <span class="text-red-400">{{ $errors->first('material_interior') }}</span>
                    @endif
                <select id="pr" name="material_interior" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Full Grain Leather">Full Grain Leather</option>
                    <option value="Partial Leather">Partial Leather</option>
                    <option value="Material">Material</option>
                    <option value="Velour">Velour</option>
                    <option value="Alcantara">Alcantara</option>
                </select>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Exterior Equipment *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="exterior_equipment[]" value="Trailer hitch" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Trailer hitch</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="exterior_equipment[]" value="Parking assistance" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Parking assistance</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="exterior_equipment[]" value="Alloy wheels" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Alloy wheels</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="exterior_equipment[]" value="Xenon/LED headlights" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Xenon/LED headlights</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Interior Equipment *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Air conditioning" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Air conditioning</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Navigation system" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Navigation system</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Radio/tuner" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Radio/tuner</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Bluetooth" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Bluetooth</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Hands-free system" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Hands-free system</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Sunroof/panoramic roof" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Sunroof/panoramic roof</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Seat heating" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Seat heating</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Cruise control" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Cruise control</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Non-smoking vehicle" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Non-smoking vehicle</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Security *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="security[]" value="Anti-lock braking system (ABS)" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Anti-lock braking system (ABS)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="security[]" value="Service history maintained" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Service history maintained</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           </div>

        </div>
    </div>

    <!-- Div 2 (Initially Hidden) -->
    <div id="divPhone" class="mt-4 p-4 hidden">
        <div class="w-1/4">

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-4">
                    <div class="font-semibold">Phone Color *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('phone_color'))
                        <span class="text-red-400">{{ $errors->first('phone_color') }}</span>
                    @endif
                <select id="Phonecolor" name="phone_color" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Choose Color</option>
                    <option value="black">Black</option>
                      <option value="white">White</option>
                      <option value="gray">Gray</option>
                      <option value="silver">Silver</option>
                      <option value="blue">Blue</option>
                      <option value="red">Red</option>
                      <option value="gold">Gold</option>
                      <option value="green">Green</option>
                </select>
                </div>
           </div>

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-4">
                    <div class="font-semibold">Device *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('device'))
                        <span class="text-red-400">{{ $errors->first('device') }}</span>
                    @endif
                <select id="pr" name="device" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Device">Device</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Device & Accessories">Device & Accessories</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-4">
                    <div class="font-semibold">Condition *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('phone_condition'))
                        <span class="text-red-400">{{ $errors->first('phone_condition') }}</span>
                    @endif
                <select id="pr" name="phone_condition" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="New">New <span class="text-xs" > (Unused item with or without original packaging)</span>  </option>
                    <option value="Very Good">Very Good (Well-maintained item with barely visible signs of wear  )</option>
                    <option value="Good">Good (Used item with visible signs of wear )</option>
                    <option value="In Order">In Order (Used item with clearly visible signs of wear, but still usable)</option>
                    <option value="Defect">Defect (Defective item suitable for repair or spare parts)</option>
                </select>
                </div>
             
           </div>


        </div>
    </div>

    <!-- Shipment Section -->
    <div id="shipment" class="">
        <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Shipment</div>
            </div>
            <div class="col-span-10 lg:col-span-5">
                <div class="flex justify-start">
                    <label class="flex items-center w-64">
                        <input type="radio" name="shipment" value="Ship" class="form-radio text-dark_green accent-dark_green" onchange="toggleShipping()">
                        <span class="ml-2">Shipping Possible</span>
                    </label>
                    <label class="flex items-center w-64">
                        <input checked type="radio" name="shipment" value="Pickup" class="form-radio text-dark_green accent-dark_green" onchange="toggleShipping()">
                        <span class="ml-2">Only Pickup</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Shipping Methods (Hidden by default) -->
        <div id="shipping" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200 hidden">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Shipping Method</div>
            </div>
            <div class="col-span-10 lg:col-span-5">
                <div id="shipping-methods">
                    @foreach($shippings as $row)
                    <div class="flex border border-dark_green rounded-lg p-2 mt-2">
                        <input 
                            id="shipping-{{ $row->id }}" 
                            name="shipping[]" 
                            type="checkbox" 
                            value="{{ $row->id }}" 
                            class="w-6 h-6 text-dark_green bg-gray-100 rounded border-dark_green focus:ring-dark_green accent-primary"
                        >
                        <label for="shipping-{{ $row->id }}" class="ml-2 text-sm font-medium flex flex-col">
                            <div class="flex items-center">
                                <span><img class="w-10" src="{{ asset('uploads/shipping/'.$row->logo) }}"></span>
                                <span class="ml-2 font-bold">{{ $row->company }}</span>
                            </div>
                            <p>Max. {{ $row->weight }} kg, {{ $row->description }}</p>
                        </label>
                    </div>
                    @endforeach
                </div>
                <!-- Error message (hidden by default) -->
                <p id="shipping-error" class="mt-2 text-red-500 hidden">Please select at least one shipping method.</p>
            </div>
        </div>
    </div>



       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Price</div>
            </div>
            <div class="col-span-10 md:col-span-3">
               
            <div class="flex justify-start items-center">
                <div class="">
                    @if ($errors->has('price'))
                        <span class="text-red-400">{{ $errors->first('price') }}</span>
                    @endif
                    <input type="text" name="price" placeholder="" class="w-36 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ old('price') }}" required>
                  </div>
                  <div class="text-base">
                   .00 Naira
                  </div>
            </div>
            </div>
            <div class="col-span-10 md:col-span-3">
                <select id="price" name="price_type" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="Fixed">Fixed Price</option>
                    <option value="Negotiable">Negotiable</option>
                    <option value="Give Away">Give Away</option>
                </select>
            </div>
       </div>

       @if($user->acc_type=="Commercial")
       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Item Quantity</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('quantity'))
                    <span class="text-red-400">{{ $errors->first('quantity') }}</span>
                @endif
            <input type="number" id="name" name="quantity" placeholder="Item Quantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="1" min="1" max="20">
            </div>
            <div class="col-span-10 md:col-span-3">
                
            </div>
       </div>
       @endif

        <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Bid/request</div>
            </div>
            <div class="col-span-10 lg:col-span-6 ">
                <div class="flex flex-col text-base">
                  <label class="flex items-center  w-full">
                    <input type="radio" name="buy_direct" value="Yes" class="form-radio text-dark_green accent-dark_green"  required>
                    <span class="ml-2 ">Yes, I would like to use the benefits of “Buy Direct” for free</span>
                    
                  </label>
                  <div class="my-2 w-full border border-gray-300 p-2 rounded-lg text-sm">
                        <div class="flex">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                            </span>
                            <span>This item can be paid for using the new “Buy Now” feature. <br><a class="font-semibold text-dartk_green" href="">Learn More</a> </span>
                        </div>
                        <div class="flex text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span><strong class="font-semibold">No negotiation </strong>- your price is what counts. </span>
                        </div>
                        <div class="flex text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span>Our buyer protection provides security, creates trust and <strong class="font-semibold">increases your chances of selling.</strong> </span>
                        </div>
                    </div>

                  <label class="flex items-center w-64">
                    <input checked type="radio" name="buy_direct" value="No" class="form-radio text-dark_green accent-dark_green" >
                    <span class="ml-2 ">No, do not use “Buy direct”</span>
                  </label>
                </div>
            </div>
      </div>

     <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Description</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('description'))
                    <span class="text-red-400">{{ $errors->first('description') }}</span>
                @endif
            <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>{{ old('description') }}</textarea>
            </div>
            <div class="col-span-10 md:col-span-3">
                
            </div>
       </div>
   

    <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Pictures (recommended)</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('images[]'))
                    <span class="text-red-400">{{ $errors->first('images[]') }}</span>
                @endif
                <div class="mb-2 hidden text-sm text-red-500" id="image-error"></div>
                <div class="flex justify-start border-dashed border-2 border-gray-300">
                    <!-- Camera Icon for File Upload -->
                    <div class="flex items-center">
                        <label for="imageUpload" class="cursor-pointer">
                            <div class="flex items-center justify-center px-3 py-1 m-2 text-gray-700 border border-gray-300 hover:bg-primary transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </div>
                            <input name="images[]" type="file" id="imageUpload" multiple class="hidden" accept="image/*">
                            <input type="hidden" name="image_order" id="image_order">

                        </label>
                    </div>

                    <!-- Image Preview Container -->
                    <div id="preview" class="grid grid-cols-4 md:grid-cols-4 gap-4 mt-4"></div>
                </div>
                <div class="text-xs flex justify-start items-center">
                    <img src="{{ asset('frontend/images/swap.png') }}" class="h-6 mx-2">
                   Move to Change the Order
                  </div>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span>  Up to 20 images with a maximum size of 12 MB. Your pictures become perfect with our photo tips.
                  </div>
            </div>
       </div>

    <div id="image-error" class="hidden mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm font-medium"></div>


    <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Location</div>
            </div>
            <div class="col-span-10 md:col-span-3">
                @if ($errors->has('state'))
                <span class="text-danger">{{ $errors->first('state') }}</span>
            @endif
            <select onchange="toggleLGA(this);" name="state" id="state" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                <option value="" selected="selected">-- Select State --</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->name }}">{{ $state->name }}</option>
                        @endforeach
            </select>
            </div>
            <div class="col-span-10 md:col-span-3">
                @if ($errors->has('lga'))
                    <span class="text-red-400">{{ $errors->first('lga') }}</span>
                @endif
                <select name="lga" id="lga" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white select-lga" required>
                </select>
            </div>
       </div>

    <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Name</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('name'))
                <span class="text-danger">{{ $errors->first('name') }}</span>
            @endif
            <input type="text" id="name" name="name" placeholder="Profile Name" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $user->name }}" readonly>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span> Enter your name so that you increase the reliability of your ad.
                  </div>
            </div>
       </div>

       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Show Contact?</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('show_contact'))
                <span class="text-danger">{{ $errors->first('show_contact') }}</span>
            @endif
            <select name="show_contact" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                <option value="No">No</option>
                <option value="Yes">Yes</option>
            </select>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span>Do you want users to see your contact details on the advert page?
                  </div>
            </div>
       </div>

       <div class="mt-5 py-3 border-b border-b-gray-200">
           <div class="font-semibold">Publish your ad</div>        
       </div>

       <div class="text-xs my-3">
           Our terms of use apply. Information about processing You can find your data in our privacy policy.
       </div>

       <div class="flex justify-start mb-10">
            <button type="submit" class="btn btn-secondary py-2 px-6">Submit</button>
       </div>


    </form>
</section>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>

<script src="{{ asset('frontend/js/lga.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    document.getElementById('category').addEventListener('change', function () {
        var countryId = this.value;
        console.log(countryId);
            if (countryId === "3") {
                document.querySelector('label[for="brand"]').textContent = "Select Job Type:";

            }else{
                document.querySelector('label[for="brand"]').textContent = "Select Option:";
            }

        //console.log(countryId);
        // Fetch states
        axios.get('/fetch-subcat/' + countryId)
            .then(function (response) {
                var stateSelect = document.getElementById('subcategory');
                stateSelect.innerHTML = '<option value="">Select Sub Category</option>'; // Reset state dropdown
                document.getElementById('brand').innerHTML = '<option value="">Select Option</option>'; // Reset city dropdown
                 var divCar = document.getElementById("divCar");
                var divPhone = document.getElementById("divPhone");
                var shipment = document.getElementById("shipment");
                const inputs = divCar.querySelectorAll('input, textarea, select, checkbox');

                // Hide all divs initially
            divCar.classList.add("hidden");
            divPhone.classList.add("hidden");
            //divModel.classList.add("hidden");
            

                response.data.forEach(function (subcat) {
                    var option = document.createElement('option');
                    option.value = subcat.id;
                    option.text = subcat.sub_category;
                    stateSelect.appendChild(option);
            

                });
            })
            .catch(function (error) {
                console.error(error);
            });
    });

    document.getElementById('subcategory').addEventListener('change', function () {
        var stateId = this.value;

        //onsole.log(stateId);
        // Fetch cities
        axios.get('/fetch-brand/' + stateId)
            .then(function (response) {
                var citySelect = document.getElementById('brand');
                citySelect.innerHTML = '<option value="">Select Option</option>'; // Reset city dropdown
                
                response.data.forEach(function (brand) {
                    var option = document.createElement('option');
                    option.value = brand.id;
                    option.text = brand.brand;
                    citySelect.appendChild(option);

                    // Show the relevant div based on the selection
                    if (stateId === "2") {
                        divCar.classList.remove("hidden");
                        //divModel.classList.remove("hidden");
                        shipment.classList.add("hidden");
                    } else if (stateId === "6") {
                        divPhone.classList.remove("hidden");
                        //divModel.classList.remove("hidden");
                    }else{
                        shipment.classList.remove("hidden");
                    }
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    });

/**
    document.getElementById('brand').addEventListener('change', function () {
        var brandId = this.value;

        // Fetch Model
        //console.log(brandId);
        axios.get('/fetch-model/' + brandId)
            .then(function (response) {
                var modelSelect = document.getElementById('model');
                modelSelect.innerHTML = '<option value="">Select Model</option>'; // Reset city dropdown
                
                response.data.forEach(function (model) {
                    var option = document.createElement('option');
                    option.value = model.id;
                    option.text = model.model;
                    modelSelect.appendChild(option);

                });
            })
            .catch(function (error) {
                console.error(error);
            });
    });

**/
</script>

<!-- JavaScript for Image Upload, Sorting, and Deleting -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("imageUpload");
    const preview = document.getElementById("preview");
    const errorBox = document.getElementById("image-error");
    const form = input.closest("form");

    let fileList = [];

    // File input change
    input.addEventListener("change", () => {
        fileList = Array.from(input.files);
        updateFileInput();
        renderPreview();
        errorBox.classList.add("hidden");
    });

    // Initialize Sortable
    new Sortable(preview, {
        animation: 150,
        onEnd: () => {
            const newOrder = [];
            preview.querySelectorAll(".preview-item").forEach(div => {
                const index = parseInt(div.dataset.index);
                newOrder.push(fileList[index]);
            });
            fileList = newOrder;
            updateFileInput();
            renderPreview();
        }
    });

    // Validate on submit
    form?.addEventListener("submit", function (e) {
        if (!fileList.length) {
            e.preventDefault();
            errorBox.textContent = "Please select at least one image before submitting.";
            errorBox.classList.remove("hidden");
            errorBox.scrollIntoView({ behavior: "smooth" });
        }
    });

    // Helper: Reassign file input with updated fileList
    function updateFileInput() {
        const dt = new DataTransfer();
        fileList.forEach(file => dt.items.add(file));
        input.files = dt.files;

        // Also update the image_order hidden input
        const orderInput = document.getElementById("image_order");
        const orderArray = fileList.map((file, index) => index); // Optional: use filenames instead
        orderInput.value = orderArray.join(",");
    }


    // Helper: Preview and delete
    function renderPreview() {
        preview.innerHTML = "";
        fileList.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const wrapper = document.createElement("div");
                wrapper.className = "relative preview-item group";
                wrapper.dataset.index = index;
                wrapper.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-auto object-cover rounded shadow">
                    <button type="button" class="absolute top-1 right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition delete-btn" title="Remove" data-index="${index}">✖</button>
                `;
                preview.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });

        // Attach delete handler after rendering
        setTimeout(() => {
            preview.querySelectorAll(".delete-btn").forEach(btn => {
                btn.addEventListener("click", function () {
                    const index = parseInt(this.dataset.index);
                    fileList.splice(index, 1);
                    updateFileInput();
                    renderPreview(); // fresh preview
                });
            });
        }, 50); // short delay to ensure images load
    }
});
</script>





<script>
    function toggleShipping() {
        const shippingDiv = document.getElementById("shipping");
        const isShipping = document.querySelector('input[name="shipment"]:checked').value === "Ship";

        // Show or hide the shipping section
        shippingDiv.classList.toggle("hidden", !isShipping);

        // Clear error if switching back to Pickup
        if (!isShipping) {
            document.getElementById("shipping-error").classList.add("hidden");
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleShipping(); // On page load

        // Attach onchange manually in case inline one fails
        const shipmentRadios = document.querySelectorAll('input[name="shipment"]');
        shipmentRadios.forEach(radio => {
            radio.addEventListener('change', toggleShipping);
        });

        const form = document.querySelector('form');

        if (form) {
            form.addEventListener('submit', function (e) {
                const isShipping = document.querySelector('input[name="shipment"]:checked').value === "Ship";
                const selectedMethods = document.querySelectorAll('input[name="shipping[]"]:checked');
                const errorMsg = document.getElementById("shipping-error");

                // If shipping selected but no shipping method checked
                if (isShipping && selectedMethods.length === 0) {
                    e.preventDefault(); // Prevent form submission
                    errorMsg.classList.remove("hidden"); // Show error
                    document.getElementById('shipping').scrollIntoView({ behavior: 'smooth' });
                } else {
                    errorMsg.classList.add("hidden"); // Hide error if valid
                }
            });
        }
    });
</script>



    
@include('dashboard.layouts.footer')
