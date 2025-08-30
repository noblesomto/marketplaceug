<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\State;
use App\Models\GigLogistic;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\BuyDirectMail;
use App\Mail\SellerMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaystackController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/payments/initialize",
     *     summary="Initialize payment for buy directly",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shipping_data"},
     *             @OA\Property(property="shipping_data", type="object",
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="grand_total", type="number"),
     *                 @OA\Property(property="commission", type="number"),
     *                 @OA\Property(property="shipping_cost", type="number"),
     *                 @OA\Property(property="shipping_method", type="integer"),
     *                 @OA\Property(property="ad", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="price", type="number")
     *                 ),
     *                 @OA\Property(property="reciever_city", type="object",
     *                     @OA\Property(property="id", type="integer")
     *                 ),
     *                 @OA\Property(property="reciever_state", type="object",
     *                     @OA\Property(property="id", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment initialized successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="authorization_url", type="string"),
     *             @OA\Property(property="reference", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function initializePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_data' => 'required|array',
            'shipping_data.first_name' => 'required|string',
            'shipping_data.last_name' => 'required|string',
            'shipping_data.phone' => 'required|string',
            'shipping_data.grand_total' => 'required|numeric',
            'shipping_data.commission' => 'required|numeric',
            'shipping_data.shipping_cost' => 'required|numeric',
            'shipping_data.shipping_method' => 'required|integer',
            'shipping_data.ad.id' => 'required|integer|exists:adverts,id',
            'shipping_data.ad.price' => 'required|numeric',
            'shipping_data.reciever_city.id' => 'nullable|integer',
            'shipping_data.reciever_state.id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $shippingData = $request->shipping_data;
        $price = round($shippingData['grand_total']);

        $response = Http::withToken(config('services.paystack.secretKey'))
            ->post(config('services.paystack.paymentUrl') . '/transaction/initialize', [
                'email' => $user->email,
                'amount' => $price * 100,
                'callback_url' => config('app.url') . '/api/payments/callback',
                'metadata' => [
                    'advert_id' => $shippingData['ad']['id'],
                    'user_id' => $user->user_id,
                    'payment_type' => 'buy_direct'
                ],
            ]);

        $data = $response->json();

        if ($data['status']) {
            $reference = $data['data']['reference'];

            $existingPayment = Payment::where('advert_id', $shippingData['ad']['id'])
                ->where('user_id', $user->user_id)
                ->where('payment_status', 'pending')
                ->first();

            if (!$existingPayment) {
                $payment = Payment::create([
                    'advert_id' => $shippingData['ad']['id'],
                    'user_id' => $user->user_id,
                    'payment_reference' => $reference,
                    'first_name' => $shippingData['first_name'],
                    'last_name' => $shippingData['last_name'],
                    'phone' => $shippingData['phone'],
                    'amount' => $shippingData['ad']['price'],
                    'commission' => $shippingData['commission'],
                    'amount_paid' => $shippingData['grand_total'],
                    'shipping_cost' => $shippingData['shipping_cost'],
                    'shipping_method' => $shippingData['shipping_method'],
                    'city' => $shippingData['reciever_city']['id'] ?? null,
                    'state' => $shippingData['reciever_state']['id'] ?? null,
                    'payment_status' => 'pending',
                ]);
            } else {
                $existingPayment->update(['payment_reference' => $reference]);
                $payment = $existingPayment;
            }

            return response()->json([
                'success' => true,
                'authorization_url' => $data['data']['authorization_url'],
                'reference' => $reference,
                'payment_id' => $payment->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment initialization failed'
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/callback",
     *     summary="Handle payment callback from Paystack",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reference"},
     *             @OA\Property(property="reference", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment failed"
     *     )
     * )
     */
    public function handleCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reference = $request->reference;

        $response = Http::withToken(config('services.paystack.secretKey'))
            ->get(config('services.paystack.paymentUrl') . "/transaction/verify/{$reference}");

        $data = $response->json();

        if ($data['status'] && $data['data']['status'] === 'success') {
            $transactionId = $data['data']['id'];
            $amount = $data['data']['amount'];
            $metadata = $data['data']['metadata'];
            $paymentType = $metadata['payment_type'] ?? 'buy_direct';

            if ($paymentType === 'buy_direct') {
                return $this->handleBuyDirectPayment($reference, $transactionId, $amount, $metadata);
            } elseif ($paymentType === 'boost') {
                return $this->handleBoostPayment($reference, $transactionId, $amount, $metadata);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unknown payment type'
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed'
        ], 400);
    }

    private function handleBuyDirectPayment($reference, $transactionId, $amount, $metadata)
    {
        $payment = Payment::where('payment_reference', $reference)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found'
            ], 404);
        }

        $shipCode = Str::upper(Str::random(10));

        DB::transaction(function () use ($payment, $transactionId, $amount, $shipCode) {
            $payment->update([
                'payment_status' => 'paid',
                'trans_id' => $transactionId,
                'amount_paid' => $amount / 100,
                'ship_code' => $shipCode,
            ]);

            Advert::where('id', $payment->advert_id)->update([
                'sold' => 'Yes',
                'sold_date' => Carbon::now(),
            ]);
        });

        $this->sendPaymentConfirmationEmails($payment, $shipCode);

        return response()->json([
            'success' => true,
            'message' => 'Payment successful',
            'ship_code' => $shipCode
        ]);
    }

    private function sendPaymentConfirmationEmails($payment, $shipCode)
    {
        $advert = Advert::find($payment->advert_id);
        $buyer = User::find($payment->user_id);
        $seller = User::find($advert->user_id);
        $location = GigLogistic::with('state')->find($payment->city);
        $shipping = Shipping::find($payment->shipping_method);

        $details = [
            'advert' => $advert->ad_title,
            'buyer' => $payment->first_name . " " . $payment->last_name,
            'seller' => $seller->name,
            'phone' => $payment->phone,
            'shipping' => $shipping->company,
            'address' => $buyer->address,
            'state' => $location->state->name ?? 'N/A',
            'city' => $location->city ?? 'N/A',
            'address' => $location->address ?? 'N/A',
            'ship_code' => $shipCode,
        ];

        Mail::to($buyer->email)->send(new BuyDirectMail($details));
        Mail::to($seller->email)->send(new SellerMail($details));
    }

    /**
     * @OA\Post(
     *     path="/api/payments/initialize-boost",
     *     summary="Initialize payment for advert boost",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"advert_id", "amount", "boost_type", "duration"},
     *             @OA\Property(property="advert_id", type="integer"),
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="boost_type", type="string"),
     *             @OA\Property(property="duration", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Boost payment initialized successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="authorization_url", type="string"),
     *             @OA\Property(property="reference", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function initializeBoost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advert_id' => 'required|integer|exists:adverts,id',
            'amount' => 'required|numeric|min:0',
            'boost_type' => 'required|string',
            'duration' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $amount = (int) str_replace(',', '', $request->amount);

        $response = Http::withToken(config('services.paystack.secretKey'))
            ->post(config('services.paystack.paymentUrl') . '/transaction/initialize', [
                'email' => $user->email,
                'amount' => $amount * 100,
                'callback_url' => config('app.url') . '/api/payments/callback',
                'metadata' => [
                    'advert_id' => $request->advert_id,
                    'user_id' => $user->user_id,
                    'payment_type' => 'boost',
                    'boost_type' => $request->boost_type,
                    'duration' => $request->duration
                ],
            ]);

        $data = $response->json();

        if ($data['status']) {
            $reference = $data['data']['reference'];

            $boost = AdvertBoost::create([
                'advert_id' => $request->advert_id,
                'user_id' => $user->user_id,
                'payment_reference' => $reference,
                'amount' => $amount,
                'boost_type' => $request->boost_type,
                'duration' => $request->duration,
                'boost_status' => "pending",
                'payment_status' => "pending",
            ]);

            return response()->json([
                'success' => true,
                'authorization_url' => $data['data']['authorization_url'],
                'reference' => $reference,
                'boost_id' => $boost->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Boost payment initialization failed'
        ], 400);
    }

    private function handleBoostPayment($reference, $transactionId, $amount, $metadata)
    {
        $boost = AdvertBoost::where('payment_reference', $reference)->first();

        if (!$boost) {
            return response()->json([
                'success' => false,
                'message' => 'Boost record not found'
            ], 404);
        }

        DB::transaction(function () use ($boost, $transactionId, $amount) {
            $boost->update([
                'payment_status' => 'paid',
                'boost_status' => 'active',
                'trans_id' => $transactionId,
                'start_date' => Carbon::now(),
            ]);

            Advert::where('id', $boost->advert_id)->update([
                'featured' => "Yes",
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Boost payment successful'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{paymentId}",
     *     summary="Get payment details",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="paymentId",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment details",
     *         @OA\JsonContent(ref="#/components/schemas/Payment")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function getPayment($paymentId)
    {
        $payment = Payment::with(['advert', 'user'])->find($paymentId);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/user/{userId}",
     *     summary="Get user's payment history",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User payment history",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment"))
     *         )
     *     )
     * )
     */
    public function getUserPayments($userId, Request $request)
    {
        $payments = Payment::with(['advert'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/boosts/user/{userId}",
     *     summary="Get user's boost history",
     *     tags={"Boosts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User boost history",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AdvertBoost"))
     *         )
     *     )
     * )
     */
    public function getUserBoosts($userId, Request $request)
    {
        $boosts = AdvertBoost::with(['advert'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $boosts
        ]);
    }
}
