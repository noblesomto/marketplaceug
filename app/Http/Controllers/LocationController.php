<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\State;
use App\Models\GigLogistic;
use App\Models\User;
use App\Models\Advert;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


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


    public function getAgilityShippingCost(Request $request)
    {
        // Validate input parameters
        $validated = $request->validate([
            'sender_station' => 'required|integer',
            'reciever_station' => 'required|integer', // Fixed typo
            'ad_price' => 'required|numeric|min:0',
            'ad_title' => 'required|string|max:255',
            'ad_des' => 'nullable|string|max:4000',
        ]);

        try {
            // Step 1: Retrieve token from cache or login
            $token = $this->getAgilityToken();

            if (!$token) {
                throw new \Exception('Unable to retrieve Agility access token.');
            }

            // Step 2: Build the shipping cost payload
            $payload = $this->buildShippingPayload($validated);

            //dd($payload);
            // Step 3: Make the shipping cost API request with retry logic
            $response = $this->makeAgilityApiRequest($token, $payload);

            // Step 4: Return success response
            return response()->json([
                'status' => true,
                'data' => $response->json()['data'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('Agility API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'error' => 'Shipping cost calculation failed. Please try again later.',
            ], 500);
        }
    }

    private function getAgilityToken()
    {
        return Cache::remember('agility_access_token', 3600, function () {
            $loginResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://thirdpartynode.theagilitysystems.com/login', [
                'email' => config('services.agility.email'), // Use env variable
                'password' => config('services.agility.password'), // Use env variable
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
        });
    }

    private function buildShippingPayload(array $validated)
    {
        return [
            "SenderStationId" => $validated['sender_station'],
            "ReceiverStationId" => $validated['reciever_station'],
            "VehicleType" => config('services.agility.vehicle_type', 3),
            "ReceiverLocation" => ["Latitude" => 0.00, "Longitude" => 0.00],
            "SenderLocation" => ["Latitude" => 0, "Longitude" => 0],
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

    private function makeAgilityApiRequest($token, $payload, $isRetry = false)
    {
        $httpOptions = [];

        // Only use custom SSL cert if file exists
        $certPath = storage_path('cacert.pem');
        if (file_exists($certPath)) {
            $httpOptions['verify'] = $certPath;
        }

        $response = Http::withOptions($httpOptions)
            ->withHeaders([
                'access-token' =>  $token,
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
        }

        if ($response->failed()) {
            throw new \Exception("Agility API request failed with status " . $response->status());
        }

        return $response;
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
