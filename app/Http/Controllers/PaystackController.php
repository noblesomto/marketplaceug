<?php

namespace App\Http\Controllers;
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
use App\Models\GigLogistic;
use App\Models\Shipping;

class PaystackController extends Controller
{
    public function initialize(Request $request)
    {

        if (!session()->has('shipping_data')) {
            return redirect()->back()->with('error', 'Shipping information is missing.');
        }
        $shipping = session('shipping_data');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $price = round($shipping['grand_total']);
        $response = Http::withToken(config('services.paystack.secretKey'))
            ->post(config('services.paystack.paymentUrl') . '/transaction/initialize', [
                'email' => $user->email,
                'amount' => $price * 100, // kobo
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'advert_id' => $shipping['ad']->id,
                    'user_id' => $user_id,
                ],
            ]);

        $data = $response->json();

        if ($data['status']) {

            $reference = $data['data']['reference'];


            $post = Payment::create([
                'advert_id'        => $shipping['ad']->id,
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
            ]);


            return redirect($data['data']['authorization_url']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }


    public function callback(Request $request)
    {
        $reference = $request->reference;

        // 🔍 Lookup the booking with the stored reference
        $booking = Payment::where('payment_reference', $reference)->first();

        if (!$booking) {
            return redirect()->route('payment.failed')->with('error', 'Booking not found.');
        }

        // ✅ Verify with Paystack
        $response = Http::withToken(config('services.paystack.secretKey'))
            ->get(config('services.paystack.paymentUrl') . "/transaction/verify/{$reference}");

        $data = $response->json();

        if ($data['status'] && $data['data']['status'] === 'success') {
            $transactionId = $data['data']['id'];
            $amount = $data['data']['amount'];
            $paidAt = $data['data']['paid_at'];
            $advertId = $data['data']['metadata']['advert_id'];
            $ship_code = Str::upper(Str::random(10));

            // 📝 Update booking as paid
            $booking->update([
                'payment_status' => 'paid',
                'trans_id' => $transactionId,
                'amount_paid' => $amount / 100, // convert to naira
                'ship_code' => $ship_code,
            ]);

            $advert = Advert::where('id',$advertId)->first();

            $advert->update([
                'sold' => 'Yes',
                'sold_date' => Carbon::now(),

            ]);

            $user_id = $request->session()->get('user_id');
            $user = User::where('user_id', $user_id)->first();

            $location = GigLogistic::with('state')->where('id', $booking->city)->first();
            $ship = Shipping::where('id', $booking->shipping_method)->first();
            //dd($ship->company);

            $details = [
                'advert' => $advert->ad_title,
                'buyer' => $user->name,
                'phone' => $user->phone,
                'shipping' => $ship->company,
                'address' => $user->address,
                'state' => $location->state->name,
                'city' => $location->city,
                'address' => $location->address,
                'ship_code' => $ship_code,
            ];

            $owner = User::where('user_id', $advert->user_id)->first();
            Mail::to($user->email)->send(new BuyDirectMail($details));
            Mail::to($owner->email)->send(new SellerMail($details));
            return redirect()->route('payment.success');
        }

        return redirect()->route('payment.failed')->with('error', 'Payment not successful.');
    }

    public function failed()
    {   
        $title = "Payment Failed  | " . config('global.site_name');
        return view('frontend.payment-failed', compact('title'));
    }

    public function success()
    {   
        $title = "Payment Successful  | " . config('global.site_name');
        return view('frontend.payment-success', compact('title'));
    }


    public function initialize_boost(Request $request)
    {
        $advertId = $request->advert_id;
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $email = $user->email;

        $response = Http::withToken(config('services.paystack.secretKey'))
            ->post(config('services.paystack.paymentUrl') . '/transaction/initialize', [
                'email' => $email,
                'amount' => $request->amount * 100, // kobo
                'callback_url' => route('boost.callback'),
                'metadata' => [
                    'advert_id' => $advertId,
                    'user_id' => $user_id,
                ],
            ]);

        $data = $response->json();

        if ($data['status']) {

            $reference = $data['data']['reference'];
            $post = AdvertBoost::create([
                'advert_id'=> $advertId,
                'user_id'=> $user_id,
                'payment_reference'=> $reference,
                'amount'=> $request->input('amount'),
                'duration'=> $request->input('duration'),
                'boost_status'=> "pending",
                'payment_status'=> "pending",
            ]);

            return redirect($data['data']['authorization_url']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }

    public function callback_boost(Request $request)
    {
        $reference = $request->reference;

        // 🔍 Lookup the booking with the stored reference
        $boost = AdvertBoost::where('payment_reference', $reference)->first();

        if (!$boost) {
            return redirect()->route('payment.failed')->with('error', 'Advert to boost not found.');
        }

        // ✅ Verify with Paystack
        $response = Http::withToken(config('services.paystack.secretKey'))
            ->get(config('services.paystack.paymentUrl') . "/transaction/verify/{$reference}");

        $data = $response->json();

        if ($data['status'] && $data['data']['status'] === 'success') {
            $transactionId = $data['data']['id'];
            $amount = $data['data']['amount'];
            $paidAt = $data['data']['paid_at'];

            // 📝 Update booking as paid
            $boost->update([
                'payment_status' => 'paid',
                'boost_status' => 'active',
                'trans_id' => $transactionId,
            ]);
            DB::table('adverts')
                ->where('id', $boost->advert_id)
                ->update([
                    'featured'=> "Yes",
                ]);

            return redirect()->route('payment.success');
        }

       return redirect()->route('payment.failed')->with('error', 'Payment was no Successful.');
    }


}
