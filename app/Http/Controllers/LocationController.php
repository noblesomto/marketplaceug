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
        // 1. Get token and prepare payload
        $token = trim(config('services.agility.token'));
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

        // 2. Make the request
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

        // 3. Handle API-specific issues
        if ($response->status() === 440) {
            throw new \Exception("Agility rejected our token (440).");
        }

        // 4. Handle general non-200 status codes
        if ($response->failed()) {
            throw new \Exception("Agility API request failed with status " . $response->status());
        }

        // 5. Return JSON data to the calling controller
        return response()->json([
            'status' => true,
            'data' => $response->json()['data'] ?? [],
        ]);

    } catch (\Exception $e) {
        Log::error('Agility API Error', [
            'error' => $e->getMessage(),
        ]);

        // Return failed response to calling controller
        return response()->json([
            'status' => false,
            'error' => 'Shipping cost calculation failed: ' . $e->getMessage(),
        ], 500);
    }
}



}
