<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\State;
use Illuminate\Http\Request;

class DistrictShippingController extends Controller
{
    public function index()
    {
        $title = "District Shipping Fees | " . config('global.site_name');
        $regions = State::with(['lgas' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();

        return view('admin.settings.shipping.districts', compact('title', 'regions'));
    }

    public function update(Request $request, Lga $lga)
    {
        $request->validate([
            'shipping_fee' => 'required|numeric|min:0',
        ]);

        $lga->update(['shipping_fee' => $request->input('shipping_fee')]);

        return redirect()->back()->with('status', [
            'text' => "Shipping fee updated for {$lga->name}.",
            'type' => 'success',
        ]);
    }
}
