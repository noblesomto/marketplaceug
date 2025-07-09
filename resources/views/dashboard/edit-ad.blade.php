@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
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

    <form action="/user/edit-ad/{{ $advert->id }}" method="POST" role="form" class="" enctype="multipart/form-data">
             @csrf 
        <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Bid/request</div>
            </div>
            <div class="col-span-10 lg:col-span-6 ">
                <div class="flex justify-start w-64">
                  <label class="flex items-center  w-full">
                    <input type="radio" {{ $advert->ad_type === 'Private' ? 'checked' : '' }} name="ad_type" value="Private" class="form-radio text-dark_green accent-dark_green"  required>
                    <span class="ml-2 ">I offer</span>
                  </label>

                  <label class="flex items-center w-64">
                    <input type="radio" {{ $advert->ad_type === 'Commercial' ? 'checked' : '' }} name="ad_type" value="Commercial" class="form-radio text-dark_green accent-dark_green" >
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
            <input type="text" name="ad_title" placeholder="Ad Title" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $advert->ad_title }}" required>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span> With a meaningful title, you sell better.
                  </div>
            </div>
       </div>



       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
    <div class="col-span-10 md:col-span-2">
        <div class="font-semibold">Select Category</div>
    </div>
    <div class="col-span-10 md:col-span-8">
        <div class="grid grid-cols-6 gap-2">
            <!-- Category Dropdown -->
            <div class="col-span-6 md:col-span-2">
                <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required
                        data-selected="{{ $advert->category }}">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $advert->category == $category->id ? 'selected' : '' }}>
                            {{ $category->category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Subcategory Dropdown -->
            <div class="col-span-6 md:col-span-2">
                <select id="subcategory" name="subcategory" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required
                        data-selected="{{ $advert->sub_category }}">
                    <option value="">Select Subcategory</option>
                    @foreach($subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}" {{ $advert->sub_category == $subcategory->id ? 'selected' : '' }}>
                            {{ $subcategory->sub_category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Brand Dropdown -->
            <div class="col-span-6 md:col-span-2">
                <select id="brand" name="brand" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required
                        data-selected="{{ $advert->brand }}">
                    <option value="">Select Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ $advert->brand == $brand->id ? 'selected' : '' }}>
                            {{ $brand->brand }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Model Dropdown (conditionally shown) 
            <div id="divModel" class="col-span-6 md:col-span-2 {{ in_array($advert->sub_category, [2,6]) ? '' : 'hidden' }}">
                <select id="model" name="model" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white"
                        data-selected="{{ $advert->sub_category == 2 ? optional($advert->car_details)->model : ($advert->sub_category == 6 ? optional($advert->phone_details)->model : '') }}" required>
                    <option value="">Select Model</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}" 
                            {{ ($advert->sub_category == 2 && optional($advert->car_details)->model == $model->id) || 
                               ($advert->sub_category == 6 && optional($advert->phone_details)->model == $model->id) ? 'selected' : '' }}>
                            {{ $model->model }}
                        </option>
                    @endforeach
                </select>
            </div>
            -->
        </div>
    </div>
</div>

        <!-- Div 1 (Initially Hidden) -->
    <div id="divCar" class="p-2 {{ $advert->sub_category == 2 ? '' : 'hidden' }}">
        @if($advert->car)
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
                        <input type="text" name="mileage" placeholder="mileage" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $advert->car->mileage }}">
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
                    <option value="Damaged Car" {{ $advert->car->condition == 'Damaged Car' ? 'selected' : '' }}>Damaged Car</option>
                    <option value="Undamaged Car" {{ $advert->car->condition == 'Undamaged Car' ? 'selected' : '' }}>Undamaged Car</option>
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
                            @foreach([
                                'January', 'February', 'March', 'April', 'May', 'June',
                                'July', 'August', 'September', 'October', 'November', 'December'
                            ] as $month)
                                <option 
                                    value="{{ $month }}" 
                                    {{ $advert->car->registration_month === $month ? 'selected' : '' }}
                                >
                                    {{ $month }}
                                </option>
                            @endforeach
                        </select>
                        <select id="registration_year" name="registration_year" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                            <option value="">Choose Year</option>
                            @php
                                $currentYear = date('Y');
                                $years = range($currentYear, $currentYear - 30); // Last 30 years
                            @endphp
                            
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $advert->car->registration_year == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
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
                    @foreach([
                        'Petrol',
                        'Diesel',
                        'Natural gas CNG',
                        'LPG',
                        'Hybrid',
                        'Electric'
                    ] as $fuelType)
                        <option 
                            value="{{ $fuelType }}" 
                            {{ $advert->car->fuel === $fuelType ? 'selected' : '' }}
                        >
                            {{ $fuelType }}
                        </option>
                    @endforeach
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
                    <option value="Automatic" {{ $advert->car->transmission == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                    <option value="Manually" {{ $advert->car->transmission == 'Manually' ? 'selected' : '' }}>Manually</option>
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
                    @foreach([
                        'Small Car',
                        'Station Wagon',
                        'Limousine',
                        'Convertible',
                        'SUV/Off Road Vehicle',
                        'Van/Bus',
                        'Coupe',
                        'Truck',
                        'Others'
                    ] as $vehicleType)
                        <option 
                            value="{{ $vehicleType }}"
                            {{ $advert->car->vehicle_type === $vehicleType ? 'selected' : '' }}
                        >
                            {{ $vehicleType }}
                        </option>
                    @endforeach
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
                <select id="exterior_color" name="exterior_color" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Choose Color</option>
                    @php
                        $colors = [
                            'black' => 'Black',
                            'white' => 'White',
                            'gray' => 'Gray',
                            'silver' => 'Silver',
                            'blue' => 'Blue',
                            'red' => 'Red',
                            'gold' => 'Gold',
                            'green' => 'Green'
                        ];
                    @endphp
                    
                    @foreach($colors as $value => $label)
                        <option value="{{ $value }}" {{ $advert->car->exterior_color == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
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
                    <option value="1 Door" {{ $advert->car->doors == '1 Door' ? 'selected' : '' }}>1 Door</option>
                    <option value="2 Doors" {{ $advert->car->doors == '2 Doors' ? 'selected' : '' }}>2 Doors</option>
                    <option value="3 Doors" {{ $advert->car->doors == '3 Doors' ? 'selected' : '' }}>3 Doors</option>
                    <option value="4 Doors" {{ $advert->car->doors == '4 Doors' ? 'selected' : '' }}>4 Doors</option>
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
                    @php
                        $interiorMaterials = [
                            'Full Grain Leather',
                            'Partial Leather',
                            'Material',
                            'Velour', 
                            'Alcantara'
                        ];
                    @endphp
                    
                    @foreach($interiorMaterials as $material)
                        <option value="{{ $material }}" 
                            @if($advert->car->material_interior == $material) selected @endif>
                            {{ $material }}
                        </option>
                    @endforeach
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
                                @php
                                    $exteriorEquipment = [
                                        'Trailer hitch',
                                        'Parking assistance',
                                        'Alloy wheels',
                                        'Xenon/LED headlights'
                                    ];
                                    $selectedEquipment = json_decode($advert->car->exterior_equipment) ?? [];
                                @endphp

                                @foreach(array_slice($exteriorEquipment, 0, 2) as $equipment)
                                    <div class="flex items-center">
                                        <input id="exterior-{{ Str::slug($equipment) }}" 
                                               type="checkbox" 
                                               name="exterior_equipment[]" 
                                               value="{{ $equipment }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($equipment, $selectedEquipment) ? 'checked' : '' }}>
                                        <label for="exterior-{{ Str::slug($equipment) }}" class="ms-2 font-medium">
                                            {{ $equipment }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($exteriorEquipment, 2) as $equipment)
                                    <div class="flex items-center">
                                        <input id="exterior-{{ Str::slug($equipment) }}" 
                                               type="checkbox" 
                                               name="exterior_equipment[]" 
                                               value="{{ $equipment }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($equipment, $selectedEquipment) ? 'checked' : '' }}>
                                        <label for="exterior-{{ Str::slug($equipment) }}" class="ms-2 font-medium">
                                            {{ $equipment }}
                                        </label>
                                    </div>
                                @endforeach
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
                        @php
                            $interiorEquipment = [
                                'Air conditioning',
                                'Navigation system',
                                'Radio/tuner',
                                'Bluetooth',
                                'Hands-free system',
                                'Sunroof/panoramic roof',
                                'Seat heating',
                                'Cruise control',
                                'Non-smoking vehicle'
                            ];
                            $selectedInterior = json_decode($advert->car->interior) ?? [];
                        @endphp
                        
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($interiorEquipment, 0, 5) as $equipment)
                                    <div class="flex items-center">
                                        <input id="interior-{{ Str::slug($equipment) }}" 
                                               type="checkbox" 
                                               name="interior[]" 
                                               value="{{ $equipment }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($equipment, $selectedInterior) ? 'checked' : '' }}>
                                        <label for="interior-{{ Str::slug($equipment) }}" class="ms-2 font-medium">
                                            {{ $equipment }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($interiorEquipment, 5) as $equipment)
                                    <div class="flex items-center">
                                        <input id="interior-{{ Str::slug($equipment) }}" 
                                               type="checkbox" 
                                               name="interior[]" 
                                               value="{{ $equipment }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($equipment, $selectedInterior) ? 'checked' : '' }}>
                                        <label for="interior-{{ Str::slug($equipment) }}" class="ms-2 font-medium">
                                            {{ $equipment }}
                                        </label>
                                    </div>
                                @endforeach
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
                        @php
                            $securityFeatures = [
                                'Anti-lock braking system (ABS)',
                                'Service history maintained',
                                'Electronic Stability Control',
                                'Airbags',
                                'Alarm system',
                                'Immobilizer',
                                'Traction control',
                                'ISOFIX child seat mounts'
                            ];
                            $selectedSecurity = json_decode($advert->car->security) ?? [];
                        @endphp
                        
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($securityFeatures, 0, ceil(count($securityFeatures)/2)) as $feature)
                                    <div class="flex items-center">
                                        <input id="security-{{ Str::slug($feature) }}" 
                                               type="checkbox" 
                                               name="security[]" 
                                               value="{{ $feature }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($feature, $selectedSecurity) ? 'checked' : '' }}>
                                        <label for="security-{{ Str::slug($feature) }}" class="ms-2 font-medium">
                                            {{ $feature }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($securityFeatures, ceil(count($securityFeatures)/2)) as $feature)
                                    <div class="flex items-center">
                                        <input id="security-{{ Str::slug($feature) }}" 
                                               type="checkbox" 
                                               name="security[]" 
                                               value="{{ $feature }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($feature, $selectedSecurity) ? 'checked' : '' }}>
                                        <label for="security-{{ Str::slug($feature) }}" class="ms-2 font-medium">
                                            {{ $feature }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @endif
    </div>

    <!-- Div 2 (Initially Hidden) -->
    <div id="divPhone" class="mt-4 p-4 {{ $advert->sub_category == 6 ? '' : 'hidden' }}">
        @if($advert->phone)
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
                    @php
                        $phoneColors = [
                            'black' => 'Black',
                            'white' => 'White',
                            'gray' => 'Gray',
                            'silver' => 'Silver',
                            'blue' => 'Blue',
                            'red' => 'Red',
                            'gold' => 'Gold',
                            'green' => 'Green'
                        ];
                    @endphp
                    
                    @foreach($phoneColors as $value => $label)
                        <option value="{{ $value }}" 
                                {{ $advert->phone->color == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
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
                    @php
                        $deviceTypes = [
                            'Device' => 'Device',
                            'Accessories' => 'Accessories',
                            'Device & Accessories' => 'Device & Accessories'
                        ];
                    @endphp
                    
                    @foreach($deviceTypes as $value => $label)
                        <option value="{{ $value }}" 
                                {{ $advert->phone->device == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
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
                    @php
                        $phoneConditions = [
                            'New' => 'New <span class="text-xs">(Unused item with or without original packaging)</span>',
                            'Very Good' => 'Very Good (Well-maintained item with barely visible signs of wear)',
                            'Good' => 'Good (Used item with visible signs of wear)',
                            'In Order' => 'In Order (Used item with clearly visible signs of wear, but still usable)',
                            'Defect' => 'Defect (Defective item suitable for repair or spare parts)'
                        ];
                    @endphp
                    
                    @foreach($phoneConditions as $value => $label)
                        <option value="{{ $value }}" 
                                {{ $advert->phone->condition == $value ? 'selected' : '' }}
                                {!! $value !== 'New' ? 'data-description="'.htmlspecialchars($label).'"' : '' !!}>
                            {!! $label !!}
                        </option>
                    @endforeach
                </select>
                </div>
             
           </div>


        </div>
        @endif
    </div>

    <!-- Shippment -->
    <div id="shipment" class="">
         <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Shipment</div>
            </div>
            <div class="col-span-10 lg:col-span-5 ">
                <div class="flex justify-start ">
                  <label class="flex items-center  w-full">
                    <input type="radio" {{ $advert->shipment === 'Ship' ? 'checked' : '' }} name="shipment" value="Ship" class="form-radio text-dark_green accent-dark_green" onclick="toggleDiv()">
                    <span class="ml-2 ">Shipping Possible</span>
                  </label>

                  <label class="flex items-center w-64">
                    <input {{ $advert->shipment === 'Pickup' ? 'checked' : '' }} type="radio" name="shipment" value="Pickup" class="form-radio text-dark_green accent-dark_green" onclick="toggleDiv()">
                    <span class="ml-2 ">Only Pickup</span>
                  </label>
                </div>
            </div>
      </div>

      <div id="shipping" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200 hidden">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Shipping Method</div>
            </div>
            <div class="col-span-10 lg:col-span-5 ">
                <div>
                    <div class="flex border border-dark_green rounded-lg p-2">
                        <input id="checked-checkbox" name="shipping[]" type="checkbox" value="GUO" class="w-6 h-6 text-dark_green bg-gray-100 rounded border-dark_green focus:ring-ring-dark_green dark:focus:ring-dark_green dark:ring-offset-ring-dark_green focus:ring-2 accent-primary">
                        <label for="checked-checkbox" class="ml-2 text-sm font-medium flex flex-col">
                            <div class="flex items-center">
                                <span><img class="w-10" src="{{ asset('frontend/images/icons/gig.png') }}"> </span>
                                <span class="ml-2 font-bold">GIG Logistics 2kg </span>
                            </div>
                            <p>Max. 2 kg, max. 60 x 30 x 15 cm shipment tracking and liability up to N100,000</p>
                        </label>
                    </div>
                    <div class="flex border border-dark_green rounded-lg p-2 mt-2">
                        <input id="checked-checkbox" name="shipping[]" type="checkbox" value="GUO" class="w-6 h-6 text-dark_green bg-gray-100 rounded border-dark_green focus:ring-ring-dark_green dark:focus:ring-dark_green dark:ring-offset-ring-dark_green focus:ring-2 accent-primary">
                        <label for="checked-checkbox" class="ml-2 text-sm font-medium flex flex-col">
                            <div class="flex items-center">
                                <span><img class="w-10" src="{{ asset('frontend/images/icons/guo.png') }}"> </span>
                                <span class="ml-2 font-bold">GUO Logistics 2kg </span>
                            </div>
                            <p>Max. 2 kg, max. 60 x 30 x 15 cm shipment tracking and liability up to N100,000</p>
                        </label>
                    </div>
                    <div class="flex border border-dark_green rounded-lg p-2 mt-2">
                        <input  id="checked-checkbox" name="shipping[]" type="checkbox" value="Fedex" class="w-6 h-6 text-dark_green bg-gray-100 rounded border-dark_green focus:ring-ring-dark_green dark:focus:ring-dark_green dark:ring-offset-ring-dark_green focus:ring-2 accent-primary">
                        <label for="checked-checkbox" class="ml-2 text-sm font-medium flex flex-col">
                            <div class="flex items-center">
                                <span><img class="w-10" src="{{ asset('frontend/images/icons/fedex.png') }}"> </span>
                                <span class="ml-2 font-bold">Fedex 2kg </span>
                            </div>
                            <p>Max. 2 kg, max. 60 x 30 x 15 cm shipment tracking and liability up to N100,000</p>
                        </label>
                    </div>
                </div>
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
                    <input type="text" name="price" placeholder="" class="w-36 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $advert->price }}" required>
                  </div>
                  <div class="text-base">
                   .00 Naira
                  </div>
            </div>
            </div>
            <div class="col-span-10 md:col-span-3">
                <select id="price" name="price_type" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="Fixed" {{ ($advert->price_type =='Fixed') ? "selected" : ""; }}>Fixed Price</option>
                    <option value="Negotiable" {{ ($advert->price_type =='Negotiable') ? "selected" : ""; }}>Negotiable</option>
                    <option value="Give Away" {{ ($advert->price_type =='Give Away') ? "selected" : ""; }}>Give Away</option>
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
            <input type="number" id="name" name="quantity" placeholder="Item Quantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $advert->quantity }}" min="1" max="20">
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
                    <input type="radio" {{ $advert->buy_direct === 'Yes' ? 'checked' : '' }} name="buy_direct" value="Yes" class="form-radio text-dark_green accent-dark_green"  required>
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
                    <input {{ $advert->buy_direct === 'No' ? 'checked' : '' }} type="radio" name="buy_direct" value="No" class="form-radio text-dark_green accent-dark_green" >
                    <span class="ml-2 ">No, do not use “Buy direct”</span>
                  </label>
                </div>
            </div>
      </div>

     <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Description</div>
            </div>
            <div class="col-span-10 md:col-span-6">
                @if ($errors->has('description'))
                    <span class="text-red-400">{{ $errors->first('description') }}</span>
                @endif
            <textarea rows="10" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>{{ $advert->description }}</textarea>
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
                        <input type="hidden" name="existing_image_order" id="existing_image_order">

                    </label>
                </div>

                <!-- Image Preview Container -->
                <div id="preview" class="grid grid-cols-4 md:grid-cols-4 gap-4">
                    <!-- Display existing images -->
                    @foreach($advert->images as $index => $image)
                        <div class="relative group cursor-move image-container" draggable="true" data-id="{{ $image->id }}">
                            <img src="{{ asset('uploads/images/' . $image->image) }}" class="w-full h-auto rounded-lg shadow">
                            <button type="button" class="absolute top-0 right-0 w-6 h-6 text-red-500 bg-white rounded-full hover:bg-red-100 delete-image flex items-center justify-center" data-id="{{ $image->id }}">&times;</button>
                            <input type="hidden" name="existing_images[]" value="{{ $image->id }}">
                        </div>
                    @endforeach
                </div>
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
    <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
    <div class="col-span-10 md:col-span-2">
        <div class="font-semibold">Location</div>
    </div>
    <div class="col-span-10 md:col-span-3">
        @if ($errors->has('state'))
            <span class="text-danger">{{ $errors->first('state') }}</span>
        @endif
        <select onchange="toggleLGA(this);" name="state" id="state" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
            <option value="" selected="selected">- Select State -</option>
            <option value="Abia" {{ $advert->state == 'Abia' ? 'selected' : '' }}>Abia</option>
            <option value="Adamawa" {{ $advert->state == 'Adamawa' ? 'selected' : '' }}>Adamawa</option>
            <option value="AkwaIbom" {{ $advert->state == 'AkwaIbom' ? 'selected' : '' }}>AkwaIbom</option>
            <option value="Anambra" {{ $advert->state == 'Anambra' ? 'selected' : '' }}>Anambra</option>
            <option value="Bauchi" {{ $advert->state == 'Bauchi' ? 'selected' : '' }}>Bauchi</option>
            <option value="Bayelsa" {{ $advert->state == 'Bayelsa' ? 'selected' : '' }}>Bayelsa</option>
            <option value="Benue" {{ $advert->state == 'Benue' ? 'selected' : '' }}>Benue</option>
            <option value="Borno" {{ $advert->state == 'Borno' ? 'selected' : '' }}>Borno</option>
            <option value="Cross River" {{ $advert->state == 'Cross River' ? 'selected' : '' }}>Cross River</option>
            <option value="Delta" {{ $advert->state == 'Delta' ? 'selected' : '' }}>Delta</option>
            <option value="Ebonyi" {{ $advert->state == 'Ebonyi' ? 'selected' : '' }}>Ebonyi</option>
            <option value="Edo" {{ $advert->state == 'Edo' ? 'selected' : '' }}>Edo</option>
            <option value="Ekiti" {{ $advert->state == 'Ekiti' ? 'selected' : '' }}>Ekiti</option>
            <option value="Enugu" {{ $advert->state == 'Enugu' ? 'selected' : '' }}>Enugu</option>
            <option value="FCT" {{ $advert->state == 'FCT' ? 'selected' : '' }}>FCT</option>
            <option value="Gombe" {{ $advert->state == 'Gombe' ? 'selected' : '' }}>Gombe</option>
            <option value="Imo" {{ $advert->state == 'Imo' ? 'selected' : '' }}>Imo</option>
            <option value="Jigawa" {{ $advert->state == 'Jigawa' ? 'selected' : '' }}>Jigawa</option>
            <option value="Kaduna" {{ $advert->state == 'Kaduna' ? 'selected' : '' }}>Kaduna</option>
            <option value="Kano" {{ $advert->state == 'Kano' ? 'selected' : '' }}>Kano</option>
            <option value="Katsina" {{ $advert->state == 'Katsina' ? 'selected' : '' }}>Katsina</option>
            <option value="Kebbi" {{ $advert->state == 'Kebbi' ? 'selected' : '' }}>Kebbi</option>
            <option value="Kogi" {{ $advert->state == 'Kogi' ? 'selected' : '' }}>Kogi</option>
            <option value="Kwara" {{ $advert->state == 'Kwara' ? 'selected' : '' }}>Kwara</option>
            <option value="Lagos" {{ $advert->state == 'Lagos' ? 'selected' : '' }}>Lagos</option>
            <option value="Nasarawa" {{ $advert->state == 'Nasarawa' ? 'selected' : '' }}>Nasarawa</option>
            <option value="Niger" {{ $advert->state == 'Niger' ? 'selected' : '' }}>Niger</option>
            <option value="Ogun" {{ $advert->state == 'Ogun' ? 'selected' : '' }}>Ogun</option>
            <option value="Ondo" {{ $advert->state == 'Ondo' ? 'selected' : '' }}>Ondo</option>
            <option value="Osun" {{ $advert->state == 'Osun' ? 'selected' : '' }}>Osun</option>
            <option value="Oyo" {{ $advert->state == 'Oyo' ? 'selected' : '' }}>Oyo</option>
            <option value="Plateau" {{ $advert->state == 'Plateau' ? 'selected' : '' }}>Plateau</option>
            <option value="Rivers" {{ $advert->state == 'Rivers' ? 'selected' : '' }}>Rivers</option>
            <option value="Sokoto" {{ $advert->state == 'Sokoto' ? 'selected' : '' }}>Sokoto</option>
            <option value="Taraba" {{ $advert->state == 'Taraba' ? 'selected' : '' }}>Taraba</option>
            <option value="Yobe" {{ $advert->state == 'Yobe' ? 'selected' : '' }}>Yobe</option>
            <option value="Zamfara" {{ $advert->state == 'Zamfara' ? 'selected' : '' }}>Zamafara</option>
        </select>
    </div>
    <div class="col-span-10 md:col-span-3">
        @if ($errors->has('lga'))
            <span class="text-red-400">{{ $errors->first('lga') }}</span>
        @endif
        <select name="lga" id="lga" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white select-lga" required>
            @if($advert->state && $advert->lga)
                <option value="{{ $advert->lga }}" selected>{{ $advert->lga }}</option>
            @endif
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
                <option value="No" {{ $advert->show_contact == 'No' ? 'selected' : '' }}>No</option>
                <option value="Yes" {{ $advert->show_contact == 'Yes' ? 'selected' : '' }}>Yes</option>
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

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Get all elements
    const categorySelect = document.getElementById('category');
    const subcategorySelect = document.getElementById('subcategory');
    const brandSelect = document.getElementById('brand');
    const modelSelect = document.getElementById('model');
    const divCar = document.getElementById('divCar');
    const divPhone = document.getElementById('divPhone');
    const divModel = document.getElementById('divModel');
    const shipmentDiv = document.getElementById('shipment');

    // Store original values from data attributes
    const originalValues = {
        category: categorySelect.dataset.selected,
        subcategory: subcategorySelect.dataset.selected,
        brand: brandSelect.dataset.selected,
        model: modelSelect ? modelSelect.dataset.selected : null
    };

    // Initialize the form
    initializeForm();

    // Category change handler
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        if (!categoryId) {
            // Clear dependent fields if no category selected
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            toggleSections('');
            return;
        }

        // Fetch subcategories for selected category
        fetch(`/fetch-subcat/${categoryId}`)
            .then(response => response.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                data.forEach(subcat => {
                    const option = document.createElement('option');
                    option.value = subcat.id;
                    option.text = subcat.sub_category;
                    // Preselect if matches original value
                    if (subcat.id == originalValues.subcategory && categoryId == originalValues.category) {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });

                // Trigger change event to load brands if subcategory was preselected
                if (subcategorySelect.value) {
                    subcategorySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error:', error));
    });

    // Subcategory change handler
    subcategorySelect.addEventListener('change', function() {
        const subcategoryId = this.value;
        if (!subcategoryId) {
            // Clear dependent fields if no subcategory selected
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            toggleSections('');
            return;
        }

        // Toggle sections based on subcategory
        toggleSections(subcategoryId);

        // Fetch brands for selected subcategory
        fetch(`/fetch-brand/${subcategoryId}`)
            .then(response => response.json())
            .then(data => {
                brandSelect.innerHTML = '<option value="">Select Brand</option>';
                data.forEach(brand => {
                    const option = document.createElement('option');
                    option.value = brand.id;
                    option.text = brand.brand;
                    // Preselect if matches original value
                    if (brand.id == originalValues.brand && subcategoryId == originalValues.subcategory) {
                        option.selected = true;
                    }
                    brandSelect.appendChild(option);
                });

                // Trigger change event to load models if brand was preselected
                if (brandSelect.value && (subcategoryId == 2 || subcategoryId == 6)) {
                    brandSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error:', error));
    });

    // Brand change handler
    if (brandSelect) {
        brandSelect.addEventListener('change', function() {
            const brandId = this.value;
            const subcategoryId = subcategorySelect.value;
            
            if (!brandId || !(subcategoryId == 2 || subcategoryId == 6)) {
                if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
                return;
            }

            // Fetch models for selected brand
            fetch(`/fetch-model/${brandId}`)
                .then(response => response.json())
                .then(data => {
                    if (!modelSelect) return;
                    
                    modelSelect.innerHTML = '<option value="">Select Model</option>';
                    data.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model.id;
                        option.text = model.model;
                        // Preselect if matches original value
                        if (model.id == originalValues.model && brandId == originalValues.brand) {
                            option.selected = true;
                        }
                        modelSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        });
    }

    // Function to toggle sections
    function toggleSections(subcategoryId) {
        // Car section (subcategory 2)
        if (subcategoryId == 2) {
            if (divCar) divCar.classList.remove('hidden');
            if (divPhone) divPhone.classList.add('hidden');
            if (divModel) divModel.classList.remove('hidden');
            if (shipmentDiv) shipmentDiv.classList.add('hidden');
        } 
        // Phone section (subcategory 6)
        else if (subcategoryId == 6) {
            if (divCar) divCar.classList.add('hidden');
            if (divPhone) divPhone.classList.remove('hidden');
            if (divModel) divModel.classList.remove('hidden');
            if (shipmentDiv) shipmentDiv.classList.add('hidden');
        } 
        // Other sections
        else {
            if (divCar) divCar.classList.add('hidden');
            if (divPhone) divPhone.classList.add('hidden');
            if (divModel) divModel.classList.add('hidden');
            if (shipmentDiv) shipmentDiv.classList.remove('hidden');
        }
    }

    // Initialize form with correct values
    function initializeForm() {
        // If category has a value, trigger change to load subcategories
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>

<!-- JavaScript for Image Upload, Sorting, and Deleting -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const existingPreview = document.getElementById("preview");
    const orderInput = document.getElementById("existing_image_order");

    // Initialize Sortable
    const sortable = new Sortable(existingPreview, {
        animation: 150,
        handle: '.image-container',
        onEnd: updateOrderInput
    });

    // Update image order input
    function updateOrderInput() {
        const ids = Array.from(existingPreview.querySelectorAll(".image-container"))
            .map(el => el.dataset.id);
        orderInput.value = ids.join(',');
    }

    // Delete existing image (frontend only)
    existingPreview.addEventListener("click", function (e) {
        if (e.target.classList.contains("delete-image")) {
            const imageId = e.target.dataset.id;
            const container = e.target.closest(".image-container");
            container.remove();

            // Also remove hidden input so it's not submitted
            const hiddenInput = document.querySelector(`input[name="existing_images[]"][value="${imageId}"]`);
            if (hiddenInput) hiddenInput.remove();

            updateOrderInput();
        }
    });

    // Initial population
    updateOrderInput();
});
</script>


<script>
    function toggleDiv() {
      const selectedOption = document.querySelector('input[name="shipment"]:checked').value;
      const shipping = document.getElementById("shipping");

      if (selectedOption === "Ship") {
        shipping.classList.remove("hidden"); // Show the div
      } else {
        shipping.classList.add("hidden"); // Hide the div
      }
    }
  </script>
<script>
// You'll need to call this when the page loads if there's a selected state
document.addEventListener('DOMContentLoaded', function() {
    @if($advert->state)
        // Trigger the LGA loading for the selected state
        const stateSelect = document.getElementById('state');
        toggleLGA(stateSelect);
        
        // After a small delay (to allow the LGA options to load), set the selected LGA
        setTimeout(() => {
            const lgaSelect = document.getElementById('lga');
            if(lgaSelect) {
                lgaSelect.value = "{{ $advert->lga }}";
            }
        }, 100);
    @endif
});
</script>
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
@include('dashboard.layouts.footer')