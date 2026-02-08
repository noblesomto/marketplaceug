<div class="space-y-2">
    @php
        $conditions = ['Local used', 'Foreign used', 'Brand new'];
        $contextFilters = [];

        // Only add filters if the variable exists AND we're on the appropriate page
        // Don't mix brand filter with other contexts
        // Check for $brands (plural) to avoid using loop variable from brand list
        if(isset($brand) && isset($brand->id) && !isset($brands)) {
            // Brand page: filter by brand only (brand already implies category and subcategory)
            $contextFilters['brand'] = $brand->id;
        } elseif(isset($subcat) && isset($subcat->id)) {
            // Subcategory page: filter by subcategory (already implies category)
            $contextFilters['sub_category'] = $subcat->id;
        } elseif(isset($cat) && isset($cat->id)) {
            // Category page: filter by category only
            $contextFilters['category'] = $cat->id;
        }

    @endphp

    @foreach($conditions as $condition)
        @php
            $query = DB::table('adverts')
                ->join('car_details', 'adverts.id', '=', 'car_details.advert_id')
                ->where('adverts.ad_status', 'active')
                ->where('adverts.sold', 'No')
                ->where('car_details.condition', $condition);

            foreach($contextFilters as $key => $value) {
                $query->where('adverts.' . $key, $value);
            }

            $count = $query->count();
        @endphp

        <label class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50 cursor-pointer transition-all">
            <div class="flex items-center">
                <input type="checkbox"
                       name="car_condition[]"
                       value="{{ $condition }}"
                       class="car-filter-checkbox w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 transition-all">
                <span class="ml-2 text-sm text-gray-700 font-medium">{{ $condition }}</span>
            </div>
            <span class="text-xs text-gray-500 font-medium">({{ $count }})</span>
        </label>
    @endforeach
</div>
