<?php

namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;

use App\Models\GigLogistic;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\Shipping;
use Mail;
use App\Mail\ShipAdMail;
use App\Mail\PickupAdMail;
use App\Mail\DeliverAdMail;
use App\Mail\CancelAdMail;
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
            'shipping',
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
        $ship = Payment::with([
            'advert.firstImage',
            'advert.owner',
            'user',
            'shipping',
            'stateRel.gigLogistics'
        ])->where('ship_code', $id)->first();
        //dd($ship);
        if($ship){
             DB::table('payments')
                ->where('ship_code', $id)
                ->update([
                    'tracking_id'=> $request->input('tracking_id'),
                    'shipping_status'=> $request->input('shipping_status'),
                    'updated_at' => Carbon::now(),
                    'shipping_status_date' => Carbon::now(),
                ]);

            $user = User::where('user_id', $ship->user_id)->first();
            $city = GigLogistic::where('id', $ship->city)->first();
            $details = [
                'advert' => $ship->advert->ad_title,
                'buyer' => $user->name,
                'phone' => $user->phone,
                'shipping' => $ship->shipping->company,
                'tracking_id' => $request->input('tracking_id'),
                'shipped_date' => Carbon::now(),
                'address' => $city->address,
                'city' => $city->city,
                'state' => $ship->stateRel->name,
            ];

            if($request->input('shipping_status')=="shipped"){
                //dd("shipped");
                Mail::to($user->email)->send(new ShipAdMail($details));
            }elseif($request->input('shipping_status')=="pickup"){
                Mail::to($user->email)->send(new PickupAdMail($details));
            }elseif($request->input('shipping_status')=="delivered"){
                Mail::to($user->email)->send(new DeliverAdMail($details));
            }else{
                Mail::to($user->email)->send(new CancelAdMail($details));
            }

        return redirect()->back()->with('success', 'Shipping Status Has been Updated');
        }else{
           return redirect()->back()->with('error','Opps! You have entered invalid Shipping Code');
        }

    }
}
