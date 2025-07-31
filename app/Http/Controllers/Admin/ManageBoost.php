<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\AdvertBoost;
use Carbon\Carbon;

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
        return view('backend.adboost.index', compact('title', 'page_title', 'adverts'));
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

        return view('backend.adboost.completed', compact('title', 'page_title', 'adverts'));
    }

    public function unpaid(Request $request)
    {
        $title = "Unpaid Boost Adverts | " . config('global.site_name');
        $page_title = "Unpaid Boost Adverts";

        $adverts = AdvertBoost::with(['user', 'advert.firstImage'])
            ->whereHas('advert', function ($query) {
                $query->where('ad_status', 1)->where('sold', 'No');
            })
            ->where('payment_status','pending')
            ->where('boost_status','pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.adboost.completed', compact('title', 'page_title', 'adverts'));
    }


}
