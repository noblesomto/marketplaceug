<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\User;
use Carbon\Carbon;

class UserManageBoost extends Controller
{
    public function boost_ad(Request $request, $id)
    {
        $title = "Boost Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $advert = Advert::with(['images', 'car', 'phone'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->firstOrFail();

        return view('dashboard.boost-ad', compact('title','user','advert','count_ads', 'price'));
    }

    public function post_boost_ad(Request $request, $id)
    {
        $title = "Boost Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $promotion = $request->session()->get('promotion');
        $count_ads = Advert::where('user_id', $user_id)->count();
        $advert = Advert::with(['images', 'car', 'phone'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->firstOrFail();


        return view('dashboard.post-boost-ad', compact('title','user','advert','count_ads','promotion'));
    }

    public function boosted_ad(Request $request, $id)
    {
        $title = "Boosted Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $advert = Advert::with(['images', 'car', 'phone', 'boost'])
                ->where('id', $id)
                ->where('user_id', $user_id)
                ->firstOrFail();

        return view('dashboard.boosted-ad', compact('title','user','advert','count_ads', 'price'));
    }

    public function make_payment(Request $request, $id)
    {
        $title = "Boosted Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $ad = AdvertBoost::with(['user', 'advert.media'])
                ->where('id', $id)
                ->where('user_id', $user_id)
                ->firstOrFail();

        return view('dashboard.make-payment', compact('title','user','ad','count_ads', 'price'));
    }


    public function upload_proof(Request $request)
    {
        $title = "Boosted Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        $request->validate([
            'boost_id' => 'required|exists:advert_boosts,id',
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:12048',
        ]);

        try {
            $boost_id = $request->boost_id;

            // Find the boost record
            $advertBoost = AdvertBoost::findOrFail($boost_id);

            // Verify this boost belongs to the current user (security check)
            if ($advertBoost->user_id != $user_id) {
                return redirect()->back()->with('status', [
                    'text' => 'Unauthorized action',
                    'type' => 'error'
                ]);
            }

            // Add the payment proof to media library
            $advertBoost->addMediaFromRequest('payment_proof')
                ->toMediaCollection('payment_proof');

            // Update the upload_proof field
            $advertBoost->update([
                'upload_proof' => 'yes',
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Payment Proof Uploaded Successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload payment proof. Please try again.');
        }
    }
}
