<?php

namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\State;
use App\Models\GigLogistic;
use App\Models\User;
use App\Models\Advert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class LocationController extends Controller
{
     public function index()
    {
        $states = State::all();
        return view('location.select', compact('states'));
    }

    public function getGIG($state_id)
    {
        $cities = GigLogistic::where('state_id', $state_id)
        ->select('id', 'city', 'address')
        ->get();
        return response()->json($cities);
    }


    public function getShippingCost(Request $request)
    {
        $validated = $request->validate([
            'district_id' => 'required|integer|exists:lgas,id',
            'ad_price' => 'required|numeric|min:0',
        ]);

        $district = \App\Models\Lga::findOrFail($validated['district_id']);

        return response()->json([
            'status' => true,
            'data' => [
                'GrandTotal' => (float) $district->shipping_fee,
                'DeclaredValue' => (float) $validated['ad_price'],
            ],
        ]);
    }

    private function cleanDescription($description)
{
    if (empty($description)) {
        return '';
    }

    // Strip HTML tags and decode HTML entities
    $cleaned = strip_tags($description);
    $cleaned = html_entity_decode($cleaned, ENT_QUOTES, 'UTF-8');

    // Remove extra whitespace and line breaks
    $cleaned = preg_replace('/\s+/', ' ', $cleaned);

    // Trim and limit length if needed
    return trim($cleaned);
}

}
