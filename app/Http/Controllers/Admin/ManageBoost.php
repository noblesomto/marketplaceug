<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\AdvertBoost;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ManageBoost extends Controller
{
    public function active(Request $request)
    {
        $title = "Active Boost Adverts | " . config('global.site_name');
        $page_title = "Active Boost Adverts";

        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->whereNotNull('start_date')
            ->where('payment_status','paid')
            ->where('boost_status','active')
            ->whereRaw("DATE_ADD(start_date, INTERVAL CAST(duration AS UNSIGNED) DAY) > ?", [now()])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            //dd($adverts);
            return view('admin.adboost.index', compact('title', 'page_title', 'adverts'));
    }

    public function status($id, $status)
    {
        $advert = AdvertBoost::where('id',$id)->first();
        $advert_id = $advert->advert_id;
        if($status=='pending'){
            $boost_status = "pending";
            $featured = "No";
        }else{
            $boost_status = "active";
            $featured = "Yes";
        }
        //dd($featured);
        DB::table('advert_boosts')
                ->where('id', $id)
                ->update([
                    'boost_status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        DB::table('adverts')
                ->where('id', $advert_id)
                ->update([
                    'featured'=> $featured,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'Advert Boost Updated','type'=>'success']);
    }

    public function completed(Request $request)
    {
        $title = "Completed Boost Adverts | " . config('global.site_name');
        $page_title = "Completed Boost Adverts";

        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->whereNotNull('start_date')
            ->where('payment_status','paid')
            ->where('boost_status','completed')
            ->whereRaw("DATE_ADD(start_date, INTERVAL CAST(duration AS UNSIGNED) DAY) < ?", [now()])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.adboost.completed', compact('title', 'page_title', 'adverts'));
    }

    public function unpaid(Request $request)
    {
        $title = "Unpaid Boost Adverts | " . config('global.site_name');
        $page_title = "Unpaid Boost Adverts";

        // No proof uploaded yet — nothing for an admin to confirm here, see confirmPayment().
        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->where('payment_status','pending')
            ->where('boost_status','pending')
            ->where('upload_proof', 'no')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.adboost.unpaid', compact('title', 'page_title', 'adverts'));
    }

    public function confirmPayment(Request $request)
    {
        $title = "Confirm Payment | " . config('global.site_name');
        $page_title = "Confirm Payment";

        // Manual bank-transfer proof was uploaded and is still awaiting admin review.
        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->where('payment_status', 'pending')
            ->where('boost_status', 'pending')
            ->where('upload_proof', 'yes')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.adboost.confirm-payment', compact('title', 'page_title', 'adverts'));
    }

    public function paid(Request $request)
    {
        $title = "Paid Boost Adverts | " . config('global.site_name');
        $page_title = "Paid Boost Adverts";

        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)
                      ->where('sold', 'No');
            })
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.adboost.paid', compact('title', 'page_title', 'adverts'));
    }


    public function payment($id, $status)
    {
        $boost = AdvertBoost::where('id', $id)->first();
        $advert_id = $boost->advert_id;

        // Always set start_date = now so the user gets their full boost duration from today
        $startDate  = Carbon::now();
        $expiresAt  = $startDate->copy()->addDays((int) $boost->duration);
        $alreadyExpired = $expiresAt->isPast();

        DB::table('advert_boosts')
            ->where('id', $id)
            ->update([
                'payment_status' => 'paid',
                'boost_status'   => $alreadyExpired ? 'completed' : 'active',
                'trans_id'       => 'manual-' . $id,
                'start_date'     => $startDate,
                'updated_at'     => Carbon::now(),
            ]);

        if (!$alreadyExpired) {
            DB::table('adverts')
                ->where('id', $advert_id)
                ->update(['featured' => 'Yes', 'updated_at' => Carbon::now()]);
        }

        return redirect()->back()->with('status', ['text' => 'Advert Boost Updated', 'type' => 'success']);
    }

    public function verifyAndActivate($id)
    {
        $boost = AdvertBoost::findOrFail($id);

        if ($boost->payment_status === 'paid') {
            return redirect()->back()->with('status', ['text' => 'This boost is already active.', 'type' => 'info']);
        }

        if (!$boost->payment_reference) {
            return redirect()->back()->with('status', ['text' => 'No payment reference on record — cannot verify with Paystack.', 'type' => 'error']);
        }

        $response = Http::withToken(config('services.paystack.secretKey'))
            ->get(config('services.paystack.paymentUrl') . "/transaction/verify/{$boost->payment_reference}")
            ->json();

        if (!($response['status'] ?? false) || ($response['data']['status'] ?? '') !== 'success') {
            $paystackMessage = $response['data']['gateway_response'] ?? ($response['message'] ?? 'Payment not confirmed');
            return redirect()->back()->with('status', [
                'text' => "Paystack says: {$paystackMessage}. Boost not activated.",
                'type' => 'error',
            ]);
        }

        $transactionId = $response['data']['id'];

        DB::transaction(function () use ($boost, $transactionId) {
            $boost->update([
                'payment_status' => 'paid',
                'boost_status'   => 'active',
                'trans_id'       => $transactionId,
                'start_date'     => Carbon::now(),
            ]);

            Advert::where('id', $boost->advert_id)->update(['featured' => 'Yes']);
        });

        Log::info('Admin verify-and-activate: boost activated', [
            'boost_id'  => $boost->id,
            'advert_id' => $boost->advert_id,
            'trans_id'  => $transactionId,
        ]);

        return redirect()->back()->with('status', ['text' => 'Payment verified with Paystack — boost is now active.', 'type' => 'success']);
    }

}
