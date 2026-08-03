<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\Lga;
use App\Models\User;
use App\Models\Advert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Locations
 *
 * APIs for locations, states, cities, and shipping calculations
 */
class LocationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/locations/states",
     *     summary="Get all states",
     *     tags={"Locations"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all states",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/State"))
     *         )
     *     )
     * )
     */
    public function getStates()
    {
        $states = State::all();

        return response()->json([
            'success' => true,
            'data' => $states
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/locations/states/{state_id}/cities",
     *     summary="Get cities by state ID",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="state_id",
     *         in="path",
     *         required=true,
     *         description="State ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of cities for the state",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/City"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="State not found"
     *     )
     * )
     */
    public function getLGAsByState($state)
    {
        $stateModel = State::where('id', $state)->orWhere('name', $state)->first();

        if (!$stateModel) {
            return response()->json(['success' => false, 'message' => 'State not found'], 404);
        }

        $lgas = Lga::where('state_id', $stateModel->id)
            ->orderBy('name')
            ->pluck('name');

        return response()->json([
            'success' => true,
            'data' => [
                'state' => $stateModel->name,
                'lgas'  => $lgas,
            ],
        ]);
    }

    public function getCitiesByState($state_id)
    {
        $state = State::find($state_id);

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'State not found'
            ], 404);
        }

        $cities = Lga::where('state_id', $state_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Calculate shipping cost based on destination district's flat fee
     */
    public function calculateShippingCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district_id' => 'required|integer|exists:lgas,id',
            'ad_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $district = \App\Models\Lga::findOrFail($validated['district_id']);

        return response()->json([
            'success' => true,
            'data' => [
                'GrandTotal' => (float) $district->shipping_fee,
                'DeclaredValue' => (float) $validated['ad_price'],
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/locations/cities",
     *     summary="Search cities",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for city name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="state_id",
     *         in="query",
     *         description="Filter by state ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of results to return",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of cities matching search criteria",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/City"))
     *         )
     *     )
     * )
     */
    public function searchCities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|min:2',
            'state_id' => 'nullable|integer|exists:states,id',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Lga::with('state');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        $cities = $query->select('id', 'name', 'state_id')
            ->limit($request->limit ?? 10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/locations/cities/{city_id}",
     *     summary="Get city details",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="city_id",
     *         in="path",
     *         required=true,
     *         description="City ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="City details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/CityDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="City not found"
     *     )
     * )
     */
    public function getCity($city_id)
    {
        $city = Lga::with('state')->find($city_id);

        if (!$city) {
            return response()->json([
                'success' => false,
                'message' => 'City not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $city
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/locations/states/{state_id}/details",
     *     summary="Get state details with cities",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="state_id",
     *         in="path",
     *         required=true,
     *         description="State ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State details with cities",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/StateWithCities")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="State not found"
     *     )
     * )
     */
    public function getStateWithCities($state_id)
    {
        $state = State::with(['lgas' => function($query) {
            $query->select('id', 'name', 'state_id');
        }])->find($state_id);

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'State not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $state
        ]);
    }
}
