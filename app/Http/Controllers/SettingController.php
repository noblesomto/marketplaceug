<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Shipping;
use App\Models\User;
use App\Models\State;
use App\Models\GigLogistic;

class SettingController extends Controller
{
    public function shipping(Request $request)
    {   
        $title = "Setup Shipping | " . config('global.site_name');
        $shippings = Shipping::get();

        //dd($shippings);
        if ($request->isMethod('GET')) {
            return view('backend.settings.shipping', compact('title','shippings'));
        }

         if ($request->isMethod('POST')) {

            $request->validate([
                'company' => 'required',
                'weight' => 'required|numeric',
                'price' => 'required|numeric',
                'description' => 'required',
                'logo' => 'required|image|mimes:jpg,png,jpeg,gif|max:10288',

            ]);


                $image = $request->file('logo');
                $imageName = time() . '.jpg';
                $imageName = str_replace(' ', '-', $imageName);
                $request->file('logo')->move('uploads/shipping', $imageName);
     
                Shipping::create([
                    'ship_id'=> rand(11111,99999),
                    'company'=> $request->input('company'),
                    'weight'=> $request->input('weight'),
                    'price'=> $request->input('price'),
                    'description'=> $request->input('description'),
                    'logo'=> $imageName,
                ]);
  

            return redirect()->back()->with('status', ['text'=>'Company  Successfully published','type'=>'success']);
        }
    }

    public function gig_locations(Request $request)
    {   
        $title = "New Location | " . config('global.site_name');
        $state = State::get();
        $gig = GigLogistic::with('state')->paginate(10);

        //dd($shippings);
        if ($request->isMethod('GET')) {
            return view('backend.settings.gig.locations', compact('title','state','gig'));
        }

         if ($request->isMethod('POST')) {

            $request->validate([
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',

            ]);



                GigLogistic::create([
                    'state_id'=> $request->input('state'),
                    'city'=> $request->input('city'),
                    'address'=> $request->input('address'),
                    'slug' => Str::slug($request->input('city')),
                ]);
  

            return redirect()->back()->with('status', ['text'=>'Location  Successfully published','type'=>'success']);
        }
    }

    public function delete_gig_location($id) 
    {
        $gig = GigLogistic::where('id', $id)->first();
        $gig->delete();

        return redirect()->back()->with('status', ['text'=>'Location was deleted','type'=>'success']);

    }
}
