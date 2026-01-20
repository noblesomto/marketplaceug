<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\GigLogistic;
use App\Models\User;
use App\Models\Advert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
    public function getCitiesByState($state_id)
    {
        $state = State::find($state_id);

        if (!$state) {
            return response()->json([
                'success' => false,
                'message' => 'State not found'
            ], 404);
        }

        $cities = GigLogistic::where('state_id', $state_id)
            ->select('id', 'city', 'address')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/shipping/calculate",
     *     summary="Calculate shipping cost",
     *     tags={"Shipping"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sender_station", "receiver_station", "ad_price", "ad_title"},
     *             @OA\Property(property="sender_station", type="string", description="Sender station ID"),
     *             @OA\Property(property="receiver_station", type="string", description="Receiver station ID"),
     *             @OA\Property(property="ad_price", type="number", format="float", description="Item price"),
     *             @OA\Property(property="ad_title", type="string", description="Item title"),
     *             @OA\Property(property="ad_des", type="string", description="Item description", nullable=true),
     *             @OA\Property(property="weight", type="number", format="float", description="Item weight in kg", default=5),
     *             @OA\Property(property="quantity", type="integer", description="Quantity", default=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shipping cost calculated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Shipping calculation failed"
     *     )
     * )
     */
    public function calculateShippingCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_station' => 'required|string',
            'receiver_station' => 'required|string',
            'ad_price' => 'required|numeric|min:0',
            'ad_title' => 'required|string',
            'ad_des' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Step 1: Retrieve token from cache or login
            $token = Cache::remember('agility_access_token', 3600, function () {
                $loginResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://thirdpartynode.theagilitysystems.com/login', [
                    'email' => config('services.agility.email', 'Info@marketplace.ng'),
                    'password' => config('services.agility.password', 'Mj:wNWI0'),
                ]);

                if ($loginResponse->failed()) {
                    Log::error('Agility login failed', ['status' => $loginResponse->status()]);
                    throw new \Exception('Unable to retrieve Agility access token.');
                }

                $loginData = $loginResponse->json();
                return $loginData['data']['access-token'] ?? null;
            });

            if (!$token) {
                throw new \Exception('Access token was not retrieved or is null.');
            }

            // Step 2: Build the shipping cost payload
            $payload = [
                "SenderStationId" => $request->sender_station,
                "ReceiverStationId" => $request->receiver_station,
                "VehicleType" => 3,
                "ReceiverLocation" => ["Latitude" => 0.00, "Longitude" => 0.00],
                "SenderLocation" => ["Latitude" => 0, "Longitude" => 0],
                "IsFromAgility" => false,
                "CustomerCode" => config('services.agility.customer_code', 'IND1875642'),
                "CustomerType" => 0,
                "DeliveryOptionIds" => [3],
                "Value" => $request->ad_price,
                "PickUpOptions" => 1,
                "ShipmentItems" => [[
                    "ItemName" => $request->ad_title,
                    "Description" => $request->ad_des ?? '',
                    "SpecialPackageId" => 1,
                    "Quantity" => $request->quantity ?? 1,
                    "Weight" => $request->weight ?? 5,
                    "IsVolumetric" => false,
                    "Length" => 0,
                    "Width" => 0,
                    "Height" => 0,
                    "ShipmentType" => 0,
                    "Value" => $request->ad_price
                ]]
            ];

            // Step 3: Make the shipping cost API request
            $response = Http::withOptions([
                'verify' => storage_path('cacert.pem'),
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'AgilityOfficialClient/1.0',
                'Access-Token' => $token,
                'Request-ID' => (string) Str::uuid(),
            ])->withBody(json_encode($payload), 'application/json')
              ->timeout(25)
              ->post(config('services.agility.url', 'https://thirdpartynode.theagilitysystems.com/api/ShippingCost/GetShippingCost'));

            // Step 4: Handle errors
            if ($response->status() === 440) {
                // Force token to refresh next time
                Cache::forget('agility_access_token');
                throw new \Exception("Agility rejected our token (440).");
            }

            if ($response->failed()) {
                throw new \Exception("Agility API request failed with status " . $response->status());
            }

            $responseData = $response->json();

            // Step 5: Return success response
            return response()->json([
                'success' => true,
                'data' => $responseData['data'] ?? $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('Agility API Error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Shipping cost calculation failed',
                'error' => $e->getMessage()
            ], 500);
        }
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

        $query = GigLogistic::with('state');

        if ($request->has('search')) {
            $query->where('city', 'like', '%' . $request->search . '%');
        }

        if ($request->has('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        $cities = $query->select('id', 'city', 'address', 'state_id')
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
        $city = GigLogistic::with('state')->find($city_id);

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
        $state = State::with(['cities' => function($query) {
            $query->select('id', 'city', 'address', 'state_id');
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
