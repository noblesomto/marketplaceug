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
     * Calculate Agility shipping cost (API Version)
     *
     * This is the API-ready version that throws exceptions instead of redirects
     */
    public function calculateShippingCost(Request $request)
    {
        // Validate input parameters
        $validator = Validator::make($request->all(), [
            'sender_station' => 'required|integer',
            'sender_address' => 'required|string',
            'reciever_station' => 'required|integer',
            'reciever_address' => 'required|string',
            'ad_price' => 'required|numeric|min:0',
            'ad_title' => 'required|string|max:255',
            'ad_des' => 'nullable|string|max:4000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            // Step 1: Retrieve token from cache or login
            $token = $this->getAgilityToken();

            if (!$token) {
                throw new \Exception('Unable to retrieve Agility access token.');
            }

            // Step 2: Get sender and receiver locations (with API-safe error handling)
            $senderAddress = $this->getSenderLocation($validated['sender_address']);
            $recieverAddress = $this->getRecieverLocation($validated['reciever_address']);

            // Step 3: Build the shipping cost payload
            $payload = $this->buildShippingPayload($validated, $senderAddress, $recieverAddress);

            // Step 4: Make the shipping cost API request with retry logic
            $response = $this->makeAgilityApiRequest($token, $payload);

            // Step 5: Return success response
            return response()->json([
                'success' => true,
                'data' => $response->json()['data'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('Agility API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $validated ?? [],
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage() ?: 'Shipping cost calculation failed. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get Agility API access token (cached for 1 hour)
     */
    private function getAgilityToken()
    {
        return Cache::remember('agility_access_token', 3600, function () {
            try {
                $loginResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout(30)->post('https://thirdpartynode.theagilitysystems.com/login', [
                    'email' => config('services.agility.email'),
                    'password' => config('services.agility.password'),
                ]);

                if ($loginResponse->failed()) {
                    Log::error('Agility login failed', [
                        'status' => $loginResponse->status(),
                        'response' => $loginResponse->body()
                    ]);
                    return null;
                }

                $loginData = $loginResponse->json();
                return $loginData['data']['access-token'] ?? null;

            } catch (\Exception $e) {
                Log::error('Agility token retrieval exception', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Build Agility shipping payload
     */
    private function buildShippingPayload(array $validated, array $senderAddress, array $recieverAddress)
    {
        return [
            "SenderStationId" => $validated['sender_station'],
            "ReceiverStationId" => $validated['reciever_station'],
            "VehicleType" => config('services.agility.vehicle_type', 3),
            "ReceiverLocation" => [
                "Latitude" => $recieverAddress['latitude'],
                "Longitude" => $recieverAddress['longitude']
            ],
            "SenderLocation" => [
                "Latitude" => $senderAddress['latitude'],
                "Longitude" => $senderAddress['longitude']
            ],
            "IsFromAgility" => false,
            "CustomerCode" => config('services.agility.customer_code'),
            "CustomerType" => 0,
            "DeliveryOptionIds" => [3],
            "Value" => $validated['ad_price'],
            "PickUpOptions" => 1,
            "ShipmentItems" => [[
                "ItemName" => $validated['ad_title'],
                "Description" => $this->cleanDescription($validated['ad_des'] ?? ''),
                "SpecialPackageId" => 1,
                "Quantity" => 1,
                "Weight" => config('services.agility.default_weight', 5),
                "IsVolumetric" => false,
                "Length" => 0,
                "Width" => 0,
                "Height" => 0,
                "ShipmentType" => 0,
                "Value" => $validated['ad_price']
            ]]
        ];
    }

    /**
     * Make Agility API request with retry logic for token expiry
     */
    private function makeAgilityApiRequest($token, $payload, $isRetry = false)
    {
        $httpOptions = [];

        // Only use custom SSL cert if file exists
        $certPath = storage_path('cacert.pem');
        if (file_exists($certPath)) {
            $httpOptions['verify'] = $certPath;
        }

        try {
            $response = Http::withOptions($httpOptions)
                ->withHeaders([
                    'access-token' => $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'AgilityOfficialClient/1.0',
                    'Request-ID' => (string) Str::uuid(),
                ])
                ->withBody(json_encode($payload), 'application/json')
                ->timeout(30)
                ->post(config('services.agility.url'));

            // Handle token expiry with retry
            if ($response->status() === 440 && !$isRetry) {
                Cache::forget('agility_access_token');
                $newToken = $this->getAgilityToken();

                if ($newToken) {
                    return $this->makeAgilityApiRequest($newToken, $payload, true);
                }

                throw new \Exception('Failed to refresh Agility token');
            }

            if ($response->failed()) {
                $errorMessage = $response->json()['message'] ?? 'Agility API request failed';
                throw new \Exception($errorMessage . " (Status: " . $response->status() . ")");
            }

            return $response;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Connection to Agility API failed. Please check your internet connection.');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Clean description for API submission
     */
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

    /**
     * Get sender location coordinates (API-safe version)
     *
     * @throws \Exception if address not found
     */
    private function getSenderLocation($address)
    {
        try {
            // Get Latitude & Longitude using OpenStreetMap (Nominatim)
            $response = Http::withHeaders([
                'User-Agent' => 'MarketplaceNigeria/1.0 (support@marketplacenigeria.com)'
            ])->timeout(15)->get("https://nominatim.openstreetmap.org/search", [
                'q' => $address,
                'format' => 'json',
                'limit' => 1
            ]);

            if ($response->failed()) {
                throw new \Exception('Failed to connect to geocoding service');
            }

            $geoData = $response->json();

            if (empty($geoData)) {
                throw new \Exception("Sender address not found: {$address}");
            }

            $latitude = $geoData[0]['lat'] ?? null;
            $longitude = $geoData[0]['lon'] ?? null;

            if (!$latitude || !$longitude) {
                throw new \Exception("Invalid coordinates for sender address: {$address}");
            }

            return [
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Unable to connect to geocoding service for sender address');
        } catch (\Exception $e) {
            Log::error('Sender location error', [
                'address' => $address,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get receiver location coordinates (API-safe version)
     *
     * @throws \Exception if address not found
     */
    private function getRecieverLocation($address)
    {
        try {
            // Get Latitude & Longitude using OpenStreetMap (Nominatim)
            $response = Http::withHeaders([
                'User-Agent' => 'MarketplaceNigeria/1.0 (support@marketplacenigeria.com)'
            ])->timeout(15)->get("https://nominatim.openstreetmap.org/search", [
                'q' => $address,
                'format' => 'json',
                'limit' => 1
            ]);

            if ($response->failed()) {
                throw new \Exception('Failed to connect to geocoding service');
            }

            $geoData = $response->json();

            if (empty($geoData)) {
                throw new \Exception("Receiver address not found: {$address}");
            }

            $latitude = $geoData[0]['lat'] ?? null;
            $longitude = $geoData[0]['lon'] ?? null;

            if (!$latitude || !$longitude) {
                throw new \Exception("Invalid coordinates for receiver address: {$address}");
            }

            return [
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Unable to connect to geocoding service for receiver address');
        } catch (\Exception $e) {
            Log::error('Receiver location error', [
                'address' => $address,
                'error' => $e->getMessage()
            ]);
            throw $e;
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
