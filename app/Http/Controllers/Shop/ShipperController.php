<?php

namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;

use App\Models\GigLogistic;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\Notification;
use App\Jobs\SendShippingUpdatePushNotification;
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
        $title = "Shipper Portal — " . config('global.site_name');
        return view('shipper.index', compact('title'));
    }

    public function get_shipping(Request $request)
    {
        $request->validate([
            'ship_code' => 'required|size:10|alpha_num',
        ], [
            'ship_code.required'  => 'Please enter the 10-digit shipping code.',
            'ship_code.size'      => 'The shipping code must be exactly 10 characters.',
            'ship_code.alpha_num' => 'The shipping code must contain only letters and numbers.',
        ]);

        $ship_code = strtoupper(trim($request->ship_code));
        $ship = Payment::where('ship_code', $ship_code)->where('payment_status', 'paid')->first();

        if ($ship) {
            return redirect()->route('shipper.order.details', $ship_code);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'No order found for that shipping code. Please check and try again.');
    }

    public function order_details(Request $request, $id)
    {
        $title = "Order Details — " . config('global.site_name');

        $ship = Payment::with([
            'advert.media',
            'advert.user',
            'user',
            'shipping',
            'cityLocation.state',
        ])->where('ship_code', $id)->where('payment_status', 'paid')->first();

        if (!$ship) {
            return redirect()->route('shipper.index')
                ->with('error', 'No paid order found for shipping code "' . $id . '". Please check and try again.');
        }

        return view('shipper.order-details', compact('title', 'ship'));
    }

    public function update_shipping(Request $request, $id)
    {
        $request->validate([
            'tracking_id'     => 'required|string|max:100',
            'shipping_status' => 'required|in:pending,shipped,pickup,delivered,canceled',
        ], [
            'tracking_id.required'     => 'Please enter a tracking ID.',
            'shipping_status.required' => 'Please select a shipping status.',
            'shipping_status.in'       => 'Invalid shipping status selected.',
        ]);

        $ship = Payment::with([
            'advert',
            'user',
            'shipping',
            'cityLocation.state',
        ])->where('ship_code', $id)->where('payment_status', 'paid')->firstOrFail();

        DB::table('payments')
            ->where('ship_code', $id)
            ->update([
                'tracking_id'          => $request->input('tracking_id'),
                'shipping_status'      => $request->input('shipping_status'),
                'updated_at'           => Carbon::now(),
                'shipping_status_date' => Carbon::now(),
            ]);

        $buyer = $ship->user;
        $city  = $ship->cityLocation;

        $details = [
            'advert'      => $ship->advert->ad_title,
            'buyer'       => $buyer->name ?? ($ship->first_name . ' ' . $ship->last_name),
            'phone'       => $ship->phone,
            'shipping'    => $ship->shipping->company ?? '—',
            'tracking_id' => $request->input('tracking_id'),
            'shipped_date'=> Carbon::now(),
            'address'     => $city->address ?? '—',
            'city'        => $city->city ?? '—',
            'state'       => $city->state->name ?? '—',
        ];

        $status = $request->input('shipping_status');

        try {
            $email = $buyer->email ?? null;
            if ($email) {
                if ($status === 'shipped') {
                    Mail::to($email)->send(new ShipAdMail($details));
                } elseif ($status === 'pickup') {
                    Mail::to($email)->send(new PickupAdMail($details));
                } elseif ($status === 'delivered') {
                    Mail::to($email)->send(new DeliverAdMail($details));
                } elseif ($status === 'canceled') {
                    Mail::to($email)->send(new CancelAdMail($details));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Shipper email failed: ' . $e->getMessage());
        }

        // Reload the payment so the push job has fresh data
        $ship->refresh();

        // In-app notification for the buyer
        $company = $ship->shipping?->company ?? 'the shipping company';
        $notificationMessages = [
            'shipped'   => "Your order \"{$ship->advert->ad_title}\" has been shipped by {$company} and is on its way.",
            'pickup'    => "Your order \"{$ship->advert->ad_title}\" is ready for pickup at your nearest {$company} centre.",
            'delivered' => "Your order \"{$ship->advert->ad_title}\" has been delivered by {$company}. Please confirm receipt.",
            'canceled'  => "{$company} has an update on the shipment of \"{$ship->advert->ad_title}\". Please check your order details.",
        ];

        try {
            Notification::create([
                'user_id'   => $buyer->id,
                'seller_id' => null,
                'advert_id' => $ship->advert_id,
                'type'      => 'Shipping Update',
                'message'   => $notificationMessages[$status] ?? "The shipping status of your order has been updated to: {$status}.",
                'is_read'   => 0,
            ]);
        } catch (\Exception $e) {
            \Log::error('Shipper in-app notification failed: ' . $e->getMessage());
        }

        // Push notification to buyer's mobile device
        try {
            SendShippingUpdatePushNotification::dispatch($buyer, $ship, $status);
        } catch (\Exception $e) {
            \Log::error('Shipper push notification dispatch failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Shipping status updated successfully.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('ship_id');
        return redirect()->route('shipper.login')->with('success', 'You have been logged out.');
    }
}
