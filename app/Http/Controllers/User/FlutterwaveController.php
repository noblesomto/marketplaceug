<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Payment;
use App\Models\User;
use App\Models\Advert;
use App\Models\AdvertBoost;
use Carbon\Carbon;
use Mail;
use App\Mail\BuyDirectMail;
use App\Mail\SellerMail;
use App\Models\State;
use App\Models\Shipping;
use App\Models\Notification;
use App\Jobs\SendAdSoldPushNotification;
use App\Traits\HasUserSession;
use Illuminate\Support\Facades\Log;

class FlutterwaveController extends Controller
{
    use HasUserSession;
    public function initialize(Request $request)
    {

        if (!session()->has('shipping_data')) {
            return redirect()->back()->with('error', 'Shipping information is missing.');
        }
        $shipping = session('shipping_data');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $price = round($shipping['grand_total']);
        $reference = (string) Str::uuid();
        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $reference,
                'amount' => $price,
                'currency' => config('currency.code'),
                'redirect_url' => route('flutterwave.callback'),
                'customer' => ['email' => $user->email],
                'metadata' => [
                    'advert_id' => $shipping['ad']->id,
                    'user_id' => $user_id,
                ],
            ]);

        $data = $response->json();

        if ($data['status'] === 'success') {

        // Check if there is already a pending payment for this advert and user
        $existingPayment = Payment::where('advert_id', $shipping['ad']->id)
            ->where('user_id', $user_id)
            ->where('payment_status', 'pending')
            ->first();

        if (!$existingPayment) {
            // Generate a unique 8-char alphanumeric order code
            do {
                $orderCode = strtoupper(Str::random(8));
            } while (Payment::where('order_code', $orderCode)->exists());

            $post = Payment::create([
                'advert_id'        => $shipping['ad']->id,
                'order_code'       => $orderCode,
                'user_id'          => $user_id,
                'payment_reference'=> $reference,
                'first_name'       => $shipping['first_name'],
                'last_name'        => $shipping['last_name'],
                'phone'            => $shipping['phone'],
                'amount'           => $shipping['ad']->price,
                'commission'       => $shipping['commission'],
                'amount_paid'      => $shipping['grand_total'],
                'shipping_cost'    => $shipping['shipping_cost'],
                'shipping_method'  => $shipping['shipping_method'],
                'city'             => $shipping['reciever_city']->id ?? null,
                'state'            => $shipping['reciever_state']->id ?? null,
                'payment_status'   => 'pending',
                'source'           => 'web',
            ]);
        } else {
            // Reuse the existing pending payment (update reference if needed)
            $existingPayment->update([
                'payment_reference' => $reference, // update new reference
            ]);

            $post = $existingPayment;
        }

        return redirect($data['data']['link']);
    }

        return back()->with('error', 'Payment initialization failed.');
    }


    public function callback(Request $request)
    {
        $reference = $request->tx_ref;

        // 🔍 Lookup the booking with the stored reference
        $booking = Payment::with('advert')->where('payment_reference', $reference)->first();

        if (!$booking) {
            return redirect()->route('payment.failed')->with('error', 'Booking not found.');
        }

        // Idempotency: a browser refresh, back-navigation, or shared link hitting
        // this callback again must not regenerate ship_code or re-send the
        // confirmation emails/notification for an already-processed booking.
        // Mirrors callback_boost()'s short-circuit below.
        if ($booking->payment_status === 'paid') {
            return redirect()->route('buy.direct.success')->with([
                'ad_title'  => $booking->advert->ad_title ?? '',
                'ship_code' => $booking->ship_code,
                'amount'    => number_format($booking->amount_paid),
            ]);
        }

        // ✅ Verify with Flutterwave
        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->get(config('services.flutterwave.paymentUrl') . "/transactions/verify_by_reference", [
                'tx_ref' => $reference,
            ]);

        $data = $response->json();

        if ($data['status'] === 'success' && $data['data']['status'] === 'successful') {
            // Trust nothing about the verified payload until the amount and
            // currency actually match what we expect for this booking —
            // guards against a tampered/replayed reference being accepted
            // just because Flutterwave reports "successful".
            $expectedAmount = $booking->amount_paid;
            if (round((float) $data['data']['amount']) < round((float) $expectedAmount) || ($data['data']['currency'] ?? null) !== config('currency.code')) {
                Log::warning('Flutterwave callback: amount/currency mismatch', [
                    'ref'      => $reference,
                    'expected' => $expectedAmount,
                    'got'      => $data['data']['amount'] ?? null,
                    'currency' => $data['data']['currency'] ?? null,
                ]);
                return redirect()->route('payment.failed')->with('error', 'Payment not successful.');
            }

            $transactionId = $data['data']['id'];
            $amount = $data['data']['amount'];
            $paidAt = $data['data']['paid_at'] ?? null;
            $advertId = $data['data']['metadata']['advert_id'];
            $ship_code = Str::upper(Str::random(10));

            // 📝 Update booking as paid
            $booking->update([
                'payment_status' => 'paid',
                'trans_id' => $transactionId,
                'amount_paid' => $amount,
                'ship_code' => $ship_code,
            ]);

            $advert = Advert::where('id',$advertId)->first();

            $advert->update([
                'sold' => 'Yes',
                'sold_date' => Carbon::now(),

            ]);

            // Resolve the buyer from the booking's own stored user_id rather
            // than session('user_id') — the gateway redirect back to this
            // callback is not guaranteed to land in the same session (it may
            // be gone entirely), and by this point the DB has already been
            // updated as paid, so a session-based lookup risks a fatal error
            // on a purchase that actually succeeded. Mirrors how
            // Api\FlutterwaveController::sendPaymentConfirmationEmails
            // resolves the buyer via User::find($payment->user_id).
            $user = User::where('user_id', $booking->user_id)->first();
            $owner = User::where('user_id', $advert->user_id)->first();

            $location = \App\Models\Lga::with('state')->where('id', $booking->city)->first();
            $ship = Shipping::where('id', $booking->shipping_method)->first();

            $details = [
                'advert' => $advert->ad_title,
                'buyer' => $booking->first_name . " " .$booking->last_name,
                'seller' => $owner->name ?? '—',
                'phone' => $booking->phone,
                'shipping' => $ship->company ?? 'the shipping company',
                'state' => $location->state->name ?? null,
                'city' => $location->name ?? null,
                'address' => $user->address ?? null,
                'ship_code' => $ship_code,
            ];

            if ($user && $user->email) {
                try {
                    Mail::to($user->email)->send(new BuyDirectMail($details));
                } catch (\Exception $e) {
                    Log::error('Buy-direct buyer confirmation email failed: ' . $e->getMessage());
                }
            }

            if ($owner && $owner->email) {
                try {
                    Mail::to($owner->email)->send(new SellerMail($details));
                } catch (\Exception $e) {
                    Log::error('Buy-direct seller notification email failed: ' . $e->getMessage());
                }
            }

            // In-app notification for the seller
            $buyerName = $booking->first_name . ' ' . $booking->last_name;
            if ($owner) {
                try {
                    Notification::create([
                        'user_id'   => $owner->id,
                        'seller_id' => $user->id ?? $owner->id,
                        'advert_id' => $advert->id,
                        'type'      => 'Item Sold',
                        'message'   => "Your advert \"{$advert->ad_title}\" has been purchased by {$buyerName}.",
                        'is_read'   => 0,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Buy-direct in-app notification failed: ' . $e->getMessage());
                }
            }

            // Push notification to seller (queued)
            if ($owner) {
                try {
                    SendAdSoldPushNotification::dispatch($owner, $advert, $buyerName);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch ad sold push notification: ' . $e->getMessage());
                }
            }

            return redirect()->route('buy.direct.success')->with([
                'ad_title'  => $advert->ad_title,
                'ship_code' => $ship_code,
                'amount'    => number_format($booking->amount_paid),
            ]);
        }

        return redirect()->route('payment.failed')->with('error', 'Payment not successful.');
    }

    public function failed()
    {
        $title = "Payment Failed  | " . config('global.site_name');
        return view('public.payment-failed', compact('title'));
    }

    public function success()
    {
        $title = "Payment Successful  | " . config('global.site_name');
        return view('public.payment-success', compact('title'));
    }

    public function buyDirectSuccess()
    {
        $title = "Order Confirmed  | " . config('global.site_name');
        return view('public.buy-direct-success', compact('title'));
    }

    public function resumePayment(Request $request, $paymentId)
    {
        $userId = $request->session()->get('user_id');

        $payment = Payment::with('advert')
            ->where('id', $paymentId)
            ->where('user_id', $userId)
            ->where('payment_status', 'pending')
            ->first();

        if (!$payment) {
            return redirect()->route('user.payments')->with('error', 'Payment not found.');
        }

        if ($payment->advert && $payment->advert->sold === 'Yes') {
            return redirect()->route('user.payments')->with('error', 'Sorry, this item has been sold to another buyer.');
        }

        $user = User::where('user_id', $userId)->first();

        $reference = (string) Str::uuid();
        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref'       => $reference,
                'amount'       => round($payment->amount_paid),
                'currency'     => config('currency.code'),
                'redirect_url' => route('flutterwave.callback'),
                'customer'     => ['email' => $user->email],
                'metadata'     => [
                    'advert_id' => $payment->advert_id,
                    'user_id'   => $userId,
                ],
            ]);

        $data = $response->json();

        if (($data['status'] ?? null) === 'success') {
            $payment->update(['payment_reference' => $reference]);
            return redirect($data['data']['link']);
        }

        return redirect()->route('user.payments')->with('error', 'Could not resume payment. Please try again.');
    }


    public function initialize_boost(Request $request)
    {
        $advertId = $request->advert_id;
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $email = $user->email;
        $amount = (int) str_replace(',', '', $request->amount);
        //dd($amount);

        $reference = (string) Str::uuid();
        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $reference,
                'amount' => $amount,
                'currency' => config('currency.code'),
                'redirect_url' => route('boost.callback'),
                'customer' => ['email' => $email],
                'metadata' => [
                    'advert_id'    => $advertId,
                    'user_id'      => $user_id,
                    'payment_type' => 'boost',
                ],
            ]);

        $data = $response->json();

        if ($data['status'] === 'success') {

            $post = AdvertBoost::create([
                'advert_id'         => $advertId,
                'user_id'           => $user_id,
                'payment_reference' => $reference,
                'amount'            => $amount,
                'boost_type'        => $request->input('boost_name'),
                'duration'          => $request->input('duration'),
                'boost_status'      => 'pending',
                'payment_status'    => 'pending',
            ]);

            return redirect($data['data']['link']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }

    public function callback_boost(Request $request)
    {
        $reference = $request->tx_ref;

        $boost = AdvertBoost::where('payment_reference', $reference)->first();

        if (!$boost) {
            Log::warning('Boost callback: reference not found', ['ref' => $reference]);
            return redirect()->route('payment.failed')->with('error', 'Advert to boost not found.');
        }

        // Idempotency: webhook may have already activated this boost
        if ($boost->payment_status === 'paid' && $boost->boost_status === 'active') {
            return redirect()->route('payment.success');
        }

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->get(config('services.flutterwave.paymentUrl') . "/transactions/verify_by_reference", [
                'tx_ref' => $reference,
            ]);

        $data = $response->json();

        if ($data['status'] === 'success' && $data['data']['status'] === 'successful') {
            $transactionId = $data['data']['id'];

            DB::transaction(function () use ($boost, $transactionId) {
                $boost->update([
                    'payment_status' => 'paid',
                    'boost_status'   => 'active',
                    'trans_id'       => $transactionId,
                    'start_date'     => Carbon::now(),
                ]);

                DB::table('adverts')
                    ->where('id', $boost->advert_id)
                    ->update(['featured' => 'Yes']);
            });

            Log::info('Boost callback: activated', [
                'boost_id'  => $boost->id,
                'advert_id' => $boost->advert_id,
                'trans_id'  => $transactionId,
            ]);

            return redirect()->route('payment.success');
        }

        Log::warning('Boost callback: verification failed', ['ref' => $reference]);
        return redirect()->route('payment.failed')->with('error', 'Payment was not successful.');
    }

    public function initialize_post_boost(Request $request)
    {
        //dd($request);
        $advertId = $request->advert_id;
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $email = $user->email;
        $promotion = $request->session()->get('promotion');
        $duration = $request->input('duration');
        $reference = (string) Str::uuid();
        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/payments', [
                'tx_ref' => $reference,
                'amount' => round($request->amount),
                'currency' => config('currency.code'),
                'redirect_url' => route('boost.callback'),
                'customer' => ['email' => $email],
                'metadata' => [
                    'advert_id'    => $advertId,
                    'user_id'      => $user_id,
                    'payment_type' => 'boost',
                ],
            ]);

        $data = $response->json();

        if($promotion == 'top'){
            $duration = 14;
        } elseif($promotion == 'gallery'){
            $duration = 14;
        } else {
            $duration = 7;
        }

        if ($data['status'] === 'success') {

            $post = AdvertBoost::create([
                'advert_id'=> $advertId,
                'user_id'=> $user_id,
                'payment_reference'=> $reference,
                'amount'=> $request->input('amount'),
                'boost_type'=> $promotion,
                'duration'=> $duration,
                'boost_status'=> "pending",
                'payment_status'=> "pending",
            ]);

            return redirect($data['data']['link']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }


    public function retry_boost_payment(Request $request)
{
    $boost_id = $request->boost_id;
    $user_id = $request->session()->get('user_id');

    // Find the boost record
    $boost = AdvertBoost::where('id', $boost_id)
        ->where('user_id', $user_id) // Ensure user owns this boost
        ->first();

    if (!$boost) {
        return back()->with('error', 'Boost record not found.');
    }

    // Check if payment is already completed
    if ($boost->payment_status === 'paid') {
        return back()->with('error', 'This boost has already been paid for.');
    }

    // Get user email
    $user = User::where('user_id', $user_id)->first();
    $email = $user->email;

    // Initialize payment with NEW reference (don't include reference parameter)
    $reference = (string) Str::uuid();
    $response = Http::withToken(config('services.flutterwave.secretKey'))
        ->post(config('services.flutterwave.paymentUrl') . '/payments', [
            'tx_ref' => $reference,
            'amount' => $boost->amount,
            'currency' => config('currency.code'),
            'redirect_url' => route('boost.callback'),
            'customer' => ['email' => $email],
            'metadata' => [
                'advert_id' => $boost->advert_id,
                'user_id' => $user_id,
                'boost_id' => $boost_id, // Add this to link back to the boost record
            ],
        ]);

    $data = $response->json();

    if ($data['status'] === 'success') {
        // Update the boost record with the new reference
        $boost->update([
            'payment_reference' => $reference,
        ]);

        return redirect($data['data']['link']);
    }

    return back()->with('error', 'Payment initialization failed. Please try again.');
}


}
