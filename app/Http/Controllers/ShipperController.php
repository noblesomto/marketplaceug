<?php

namespace App\Http\Controllers;

use App\Models\GigLogistic;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Shipping;
use Carbon\Carbon;

class ShipperController extends Controller
{
    public function index(Request $request)
    {
        $title = "Shippper Section -  " . config('global.site_name');

        return view('shipper.index', compact('title'));


    }

    public function get_shipping(Request $request)
    {
        $request->validate([
            'ship_code' => 'required|size:10|alpha_num',
        ]);

        //dd($request);
        $ship_code = $request->ship_code;
        $ship = Payment::where('ship_code', $ship_code)->first();

        if($ship){
            return redirect("/shipper/order-details/".$ship_code);
        }else{
           return redirect()->back()->with('error','Opps! You have entered invalid Shipping Code');
        }

    }

    public function order_details(Request $request, $id)
    {
        $title = "Shippper Section -  " . config('global.site_name');
        $ship = Payment::with([
            'advert.firstImage',
            'advert.owner',
            'user',
            'stateRel.gigLogistics'
        ])->where('ship_code', $id)->first();

        //dd($ship->stateRel->gigLogistics);
        $city = GigLogistic::where('id', $ship->city)->first();
        //dd($city);
        return view('shipper.order-details', compact('title','ship','city'));

    }

    public function update_shipping(Request $request, $id)
    {
        $request->validate([
            'tracking_id' => 'required',
        ]);

        //dd($request);
        $ship_code = $request->ship_code;
        $ship = Payment::where('ship_code', $ship_code)->first();

        if($ship){
             DB::table('payments')
                ->where('ship_code', $id)
                ->update([
                    'tracking_id'=> $request->input('tracking_id'),
                    'shipping_status'=> $request->input('shipping_status'),
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('success', 'Shipping Status Has been Updated');
        }else{
           return redirect()->back()->with('error','Opps! You have entered invalid Shipping Code');
        }

    }
}
