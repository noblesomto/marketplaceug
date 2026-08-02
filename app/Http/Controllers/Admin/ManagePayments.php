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

        $accountNumber = $payment->advert->owner->account_number;
        $bankCode      = $payment->advert->owner->bank_code;
        $amount        = $payment->amount * 100; // Convert to kobo

        if (!$accountNumber || !$bankCode) {
            return back()->with('status', ['type' => 'danger', 'text' => 'User bank details are incomplete.']);
        }

        $paystackSecret = config('services.paystack.secret');

        // 1. Resolve account
        $resolve = Http::withToken($paystackSecret)->get('https://api.paystack.co/bank/resolve', [
            'account_number' => $accountNumber,
            'bank_code'      => $bankCode,
        ]);

        if (!$resolve->ok() || !$resolve['status']) {
            return back()->with('status', ['type' => 'danger', 'text' => 'Failed to resolve account: ' . ($resolve['message'] ?? 'Unknown error')]);
        }

        $accountName = $resolve['data']['account_name'];

        // 2. Create recipient
        $recipient = Http::withToken($paystackSecret)->post('https://api.paystack.co/transferrecipient', [
            'type'           => 'nuban',
            'name'           => $accountName,
            'account_number' => $accountNumber,
            'bank_code'      => $bankCode,
            'currency'       => 'NGN',
        ]);

        if (!$recipient->ok() || !$recipient['status']) {
            return back()->with('status', ['type' => 'danger', 'text' => 'Failed to create transfer recipient.']);
        }

        $recipientCode = $recipient['data']['recipient_code'];

        // 3. Initiate transfer
        $transfer = Http::withToken($paystackSecret)->post('https://api.paystack.co/transfer', [
            'source'    => 'balance',
            'amount'    => $amount,
            'recipient' => $recipientCode,
            'reason'    => 'Payout to seller ID: ' . $payment->advert->owner->user_id,
        ]);

        if ($transfer->ok() && $transfer['status']) {
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

            return redirect('admin/completed-settlements')->with('status', ['type' => 'success', 'text' => 'Payout successful. Seller has been notified.']);
        }

        return back()->with('status', ['type' => 'danger', 'text' => 'Transfer failed: ' . ($transfer['message'] ?? 'Unknown error')]);
    }
}
