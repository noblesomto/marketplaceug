<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\User;
use App\Models\Advert;
use Carbon\Carbon;
use Mail;
use App\Mail\PayoutMail;
use App\Mail\BuyDirectMail;
use App\Mail\SellerMail;
use App\Models\Lga;
use App\Services\ShippingStatusNotifier;
use App\Services\SellerCancelNotifier;

class ManagePayments extends Controller
{
    public function completed_payments(Request $request)
    {
        $title = "Completed Payments | " . config('global.site_name');
        $page_title = "Completed Payments";

        $payments = Payment::with([
                'advert.media', // This loads the advert and its firstImage
                'user'
            ])
            ->where("payment_status", "paid")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        //dd($payments);
        return view('admin.payments.completed-payments', compact('title', 'page_title', 'payments'));
    }

    public function pending_payments(Request $request)
    {
        $title = "Pending Payments | " . config('global.site_name');
        $page_title = "Pending/Failed Payments";

        $payments = Payment::with(['advert.media', 'user'])
        ->where("payment_status", '!=', "paid")
        ->whereHas('advert') // Only include payments with an advert
        ->orderBy('created_at', 'desc')
        ->paginate(20);
            //dd($payments);
        return view('admin.payments.pending-payments', compact('title', 'page_title', 'payments'));
    }

    public function update_payment(Request $request, $id)
    {
        $request->validate([
            'buyer_status'    => 'required|in:pending,delivered,canceled',
            'shipping_status' => 'required|in:pending,shipped,pickup,delivered,canceled',
            'seller_status'   => 'required|in:pending,shipped,canceled',
        ]);

        $payment = Payment::with(['advert.user', 'user', 'shipping', 'cityLocation.state'])
            ->where('id', $id)
            ->firstOrFail();

        $newBuyerStatus    = $request->input('buyer_status');
        $newShippingStatus = $request->input('shipping_status');
        $newSellerStatus   = $request->input('seller_status');

        $shippingChanged = $newShippingStatus !== ($payment->shipping_status ?? 'pending');
        $sellerChanged   = $newSellerStatus !== ($payment->seller_status ?? 'pending');

        $update = [
            'buyer_status'    => $newBuyerStatus,
            'shipping_status' => $newShippingStatus,
            'seller_status'   => $newSellerStatus,
        ];

        if ($shippingChanged) {
            $update['shipping_status_date'] = Carbon::now();
        }
        if ($sellerChanged) {
            $update['seller_status_date'] = Carbon::now();
        }

        $payment->update($update);

        // Admin overrides trigger the same buyer/seller notifications as the
        // normal shipper and seller-cancel flows, so nobody is left uninformed.
        if ($shippingChanged) {
            ShippingStatusNotifier::notify($payment, $newShippingStatus);
        }

        if ($sellerChanged && $newSellerStatus === 'canceled') {
            SellerCancelNotifier::notify($payment);
        }

        return redirect()->back()->with('status', ['text'=>'Payment Status Updated','type'=>'success']);
    }

    public function confirm_payment(Request $request,$id)
    {
        //dd($request);
        $ship = Payment::with([
            'advert.firstImage',
            'advert.owner',
            'user',
            'shipping',
            'stateRel.gigLogistics'
        ])->where('id', $id)->first();
        $ship_code = Str::upper(Str::random(10));

        //dd($ship->advert->id);
        DB::table('payments')
            ->where('id', $id)
            ->update([
                'payment_status' => 'paid',
                'ship_code' => $ship_code,
            ]);


        $advert = Advert::where('id',$ship->advert->id)->first();

        $advert->update([
            'sold' => 'Yes',
            'sold_date' => Carbon::now(),

        ]);
        $user = User::where('user_id', $ship->user_id)->first();
        $district = Lga::where('id', $ship->city)->first();
        $details = [
            'advert' => $ship->advert->ad_title,
            'buyer' => $user->name,
            'seller' => $ship->advert->owner->name,
            'phone' => $user->phone,
            'shipping' => $ship->shipping->company,
            'shipped_date' => Carbon::now(),
            'city' => $district->name,
            'state' => $ship->stateRel->name,
            'ship_code' => $ship->ship_code,
        ];

        Mail::to($user->email)->send(new BuyDirectMail($details));
        Mail::to($ship->advert->owner->email)->send(new SellerMail($details));

        return redirect()->back()->with('status', ['text'=>'Payment Status Updated','type'=>'success']);
    }

    public function pending_settlements(Request $request)
    {
        $title = "Pending Settlements | " . config('global.site_name');
        $page_title = "Pending Settlements";

        $payments = Payment::with([
                'advert.firstImage',
                'advert.owner',
                'user'
            ])
            ->where("payment_status", "paid")
            ->where("seller_settlement", "no")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        //dd($payments);
        return view('admin.settlements.pending-settlements', compact('title', 'page_title', 'payments'));
    }


    public function completed_settlements(Request $request)
    {
        $title = "Completed Payments | " . config('global.site_name');
        $page_title = "Completed Payments";

        $payments = Payment::with([
                'advert.firstImage',
                'advert.owner',
                'user'
            ])
            ->where("payment_status", "paid")
            ->where("seller_settlement", "yes")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        //dd($payments);
        return view('admin.settlements.completed-settlements', compact('title', 'page_title', 'payments'));
    }

    public function confirm_settlement($id)
    {
        $payment = Payment::with('advert.owner', 'user')->where('id', $id)->first();

        $settledAt = Carbon::now();

        $payment->update([
            'seller_settlement' => 'yes',
            'settlement_date'   => $settledAt,
        ]);

        Mail::to($payment->advert->owner->email)->send(new PayoutMail([
            'seller' => $payment->advert->owner->name,
            'title'  => $payment->advert->ad_title,
            'amount' => $payment->amount,
            'date'   => $settledAt,
        ]));

        return redirect('admin/completed-settlements')->with('status', ['text' => 'Settlement Confirmed', 'type' => 'success']);
    }

    public function sendPayout(Request $request, $id)
    {
        $payment = Payment::with('advert.owner', 'user')->where('id', $id)->first();

        if (!$payment) {
            return back()->with('status', ['type' => 'danger', 'text' => 'Payment not found.']);
        }

        if ($payment->seller_settlement === 'yes') {
            return back()->with('status', ['type' => 'warning', 'text' => 'This payment has already been settled.']);
        }

        $seller = $payment->advert->owner;
        $amount = $payment->amount; // no kobo conversion - UGX has no minor unit

        $payload = [
            'amount'    => $amount,
            'currency'  => config('currency.code'),
            'narration' => 'MarketplaceUG seller payout',
            // Deterministic, tied to the payment id — Flutterwave rejects a
            // replayed/duplicate transfer for the same reference, guarding
            // against double-payout from a back-navigation, double-click,
            // retry, or link prefetch.
            'reference' => 'payout_' . $payment->id,
        ];

        if ($seller->payout_method === 'mobile_money') {
            if (!$seller->mobile_money_number || !$seller->mobile_network) {
                return back()->with('status', ['type' => 'danger', 'text' => 'Seller mobile money details are incomplete.']);
            }
            $payload['type'] = 'mobilemoneyuganda';
            $payload['account_number'] = $seller->mobile_money_number;
            $payload['network'] = $seller->mobile_network;
        } else {
            if (!$seller->account_number || !$seller->bank_code) {
                return back()->with('status', ['type' => 'danger', 'text' => 'Seller bank details are incomplete.']);
            }
            $payload['type'] = 'account';
            $payload['account_bank'] = $seller->bank_code;
            $payload['account_number'] = $seller->account_number;
            $payload['beneficiary_name'] = $seller->account_name;
        }

        $transfer = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/transfers', $payload);

        $data = $transfer->json();

        if ($transfer->ok() && ($data['status'] ?? null) === 'success') {
            $settledAt = Carbon::now();

            $payment->update([
                'seller_settlement' => 'yes',
                'settlement_date'   => $settledAt,
                'payout_trans_id'   => $data['data']['id'] ?? null,
            ]);

            Mail::to($seller->email)->send(new PayoutMail([
                'seller' => $seller->name,
                'title'  => $payment->advert->ad_title,
                'amount' => $payment->amount,
                'date'   => $settledAt,
            ]));

            return redirect('admin/completed-settlements')->with('status', ['type' => 'success', 'text' => 'Payout successful. Seller has been notified.']);
        }

        return back()->with('status', ['type' => 'danger', 'text' => 'Transfer failed: ' . ($data['message'] ?? 'Unknown error')]);
    }
}
