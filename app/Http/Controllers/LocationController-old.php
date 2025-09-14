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
        try {
            // Step 1: Retrieve token from cache or login
            $token = Cache::remember('agility_access_token', 3600, function () {
                $loginResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://thirdpartynode.theagilitysystems.com/login', [
                    'email' => 'Info@marketplace.ng',
                    'password' => 'Mj:wNWI0',
                ]);

                if ($loginResponse->failed()) {
                    Log::error('Agility login failed', ['status' => $loginResponse->status()]);
                    throw new \Exception('Unable to retrieve Agility access token.');
                }

                $loginData = $loginResponse->json();
                return $loginData['data']['access-token'] ?? null;
            });

            //dd($token);
            if (!$token) {
                throw new \Exception('Access token was not retrieved or is null.');
            }

            //dd($token);

            // Step 2: Build the shipping cost payload
            config(['app.timezone' => 'UTC']);

            $payload = [
                "SenderStationId" => $request->sender_station,
                "ReceiverStationId" => $request->reciever_station,
                "VehicleType" => 3,
                "ReceiverLocation" => ["Latitude" => 0.00, "Longitude" => 0.00],
                "SenderLocation" => ["Latitude" => 0, "Longitude" => 0],
                "IsFromAgility" => false,
                "CustomerCode" => "IND1875642",
                "CustomerType" => 0,
                "DeliveryOptionIds" => [3],
                "Value" => $request->ad_price,
                "PickUpOptions" => 1,
                "ShipmentItems" => [[
                    "ItemName" => $request->ad_title,
                    "Description" => $request->ad_des ?? '',
                    "SpecialPackageId" => 1,
                    "Quantity" => 1,
                    "Weight" => 5,
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
              ->post(config('services.agility.url'));

            // Step 4: Handle errors
            if ($response->status() === 440) {
                // Force token to refresh next time
                Cache::forget('agility_access_token');
                throw new \Exception("Agility rejected our token (440).");
            }

            if ($response->failed()) {
                throw new \Exception("Agility API request failed with status " . $response->status());
            }

            // Step 5: Return success response
            return response()->json([
                'status' => true,
                'data' => $response->json()['data'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('Agility API Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'error' => 'Shipping cost calculation failed: ' . $e->getMessage(),
            ], 500);
        }
    }



}
