<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Mail;
use App\Mail\PayoutMail;

class ManagePayments extends Controller
{
    public function completed_payments(Request $request)
    {
        $title = "Completed Payments | " . config('global.site_name');
        $page_title = "Completed Payments";

        $payments = Payment::with([
                'advert.firstImage', // This loads the advert and its firstImage
                'user'
            ])
            ->where("payment_status", "paid")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        //dd($payments);
        return view('backend.payments.completed-payments', compact('title', 'page_title', 'payments'));
    }

    public function pending_payments(Request $request)
    {
        $title = "Pending Payments | " . config('global.site_name');
        $page_title = "Pending/Failed Payments";

        $payments = Payment::with(['advert.firstImage', 'user'])
        ->where("payment_status", '!=', "paid")
        ->whereHas('advert') // Only include payments with an advert
        ->orderBy('created_at', 'desc')
        ->paginate(20);
            //dd($payments);
        return view('backend.payments.pending-payments', compact('title', 'page_title', 'payments'));
    }

    public function confirm_delivery($id)
    {
        DB::table('payments')
                ->where('id', $id)
                ->update([
                    'shipping_status'=> "delivered",
                    'buyer_status'=> "delivered",
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'Delivery Status Updated','type'=>'success']);
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
        return view('backend.settlements.pending-settlements', compact('title', 'page_title', 'payments'));
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
        return view('backend.settlements.completed-settlements', compact('title', 'page_title', 'payments'));
    }

    public function confirm_settlement($id)
    {
        $payment = Payment::with('advert.owner','user')->where('id',$id)->first();
        //dd($payment->advert->owner->email);
        DB::table('payments')
                ->where('id', $id)
                ->update([
                    'seller_settlement'=> "yes",
                    'settlement_date' => Carbon::now(),
                ]);

        Mail::to($payment->advert->owner->email)->send(new PayoutMail([
            'seller'         => $payment->advert->owner->name,
            'title'         => $payment->advert->ad_title,
            'amount'            => $payment->amount,
            'date'           => $payment->settlement_date,

        ]));

        return redirect('admin/completed-settlements')->with('status', ['text'=>'Settlement Confirmed','type'=>'success']);
    }

    public function sendPayout(Request $request, $id)
    {
        $payment = Payment::with('advert.owner','user')->where('id',$id)->first();

        // Get stored bank details from user
        $accountName = $payment->advert->owner->account_name;
        $accountNumber = $payment->advert->owner->account_number;
        $bankCode = $payment->advert->owner->bank_code;
        $amount = $payment->amount * 100; // Convert to kobo

        if (!$accountNumber || !$bankCode) {
            return back()->with('error', 'User bank details are incomplete.');
        }

        $paystackSecret = config('services.paystack.secret');

        // 1. Resolve account (optional, validates details)
        $resolve = Http::withToken($paystackSecret)->get("https://api.paystack.co/bank/resolve", [
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
        ]);

        if (!$resolve->ok() || !$resolve['status']) {
            return back()->with('error', 'Failed to resolve account: ' . $resolve['message']);
        }

        $accountName = $resolve['data']['account_name'];

        // 2. Create recipient
        $recipient = Http::withToken($paystackSecret)->post("https://api.paystack.co/transferrecipient", [
            'type' => 'nuban',
            'name' => $accountName,
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
            'currency' => 'NGN',
        ]);

        if (!$recipient->ok() || !$recipient['status']) {
            return back()->with('error', 'Failed to create transfer recipient.');
        }

        $recipientCode = $recipient['data']['recipient_code'];

        // 3. Initiate transfer
        $transfer = Http::withToken($paystackSecret)->post("https://api.paystack.co/transfer", [
            'source' => 'balance',
            'amount' => $amount,
            'recipient' => $recipientCode,
            'reason' => 'Payout to seller ID: ' . $payment->advert->owner->user_id,
        ]);

        if ($transfer->ok() && $transfer['status']) {
            Mail::to($payment->advert->owner->email)->send(new PayoutMail([
                'seller'         => $payment->advert->owner->name,
                'title'         => $payment->advert->ad_title,
                'amount'            => $payment->amount,
                'date'           => $payment->settlement_date,

            ]));
            return back()->with('success', 'Payout successful.');
        }

        return back()->with('error', 'Transfer failed: ' . ($transfer['message'] ?? 'Unknown error'));
    }
}
