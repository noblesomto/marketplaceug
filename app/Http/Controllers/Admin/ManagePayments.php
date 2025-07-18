<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Payment;
use Carbon\Carbon;

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
        return view('backend.payments', compact('title', 'page_title', 'payments'));
    }

    public function pending_payments(Request $request)
    {
        $title = "Pending Payments | " . config('global.site_name');
        $page_title = "Pending/Failed Payments";

        $payments = Payment::with([
                'advert.firstImage', // This loads the advert and its firstImage
                'user'
            ])
            ->where("payment_status",'!=', "paid")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.payments', compact('title', 'page_title', 'payments'));
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
}
