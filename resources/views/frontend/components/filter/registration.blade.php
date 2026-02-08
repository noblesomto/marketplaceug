<div class="space-y-2">
    @php
        $registrations = ['Registered', 'Unregistered'];
        $contextFilters = [];

        if(isset($brand) && isset($brand->id) && !isset($brands)) {
            $contextFilters['brand'] = $brand->id;
        } elseif(isset($subcat) && isset($subcat->id)) {
            $contextFilters['sub_category'] = $subcat->id;
        } elseif(isset($cat) && isset($cat->id)) {
            $contextFilters['category'] = $cat->id;
        }
    @endphp

    @foreach($registrations as $registration)
        @php
            $count = DB::table('adverts')
                ->join('car_details', 'adverts.id', '=', 'car_details.advert_id')
                ->where('adverts.ad_status', 'active')
                ->where('adverts.sold', 'No')
                ->where('car_details.registration', $registration);

            foreach($contextFilters as $key => $value) {
                $count->where('adverts.' . $key, $value);
            }

            $count = $count->count();
        @endphp

        <label class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50 cursor-pointer transition-all">
            <div class="flex items-center">
                <input type="checkbox"
                       name="car_registration[]"
                       value="{{ $registration }}"
                       class="car-filter-checkbox w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 transition-all">
                <span class="ml-2 text-sm text-gray-700 font-medium">{{ $registration }}</span>
            </div>
            <span class="text-xs text-gray-500 font-medium">({{ $count }})</span>
        </label>
    @endforeach
</div>
