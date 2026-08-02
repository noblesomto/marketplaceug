<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\AdSetting;
use App\Models\Shipping;
use App\Models\User;
use App\Models\Admin;
use Hash;

class SettingController extends Controller
{
    public function shipping(Request $request)
    {   
        $title = "Setup Shipping | " . config('global.site_name');
        $shippings = Shipping::get();

        //dd($shippings);
        if ($request->isMethod('GET')) {
            return view('admin.settings.shipping', compact('title','shippings'));
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

    public function imageSettings(Request $request)
    {
        $title = "Ad Image Settings | " . config('global.site_name');

        if ($request->isMethod('POST')) {
            $request->validate([
                'max_images'       => 'required|integer|min:1|max:20',
                'min_images'       => 'required|integer|min:1',
                'image_strictness' => 'required|integer|min:1|max:10',
            ]);

            // min_images cannot exceed max_images
            if ($request->min_images > $request->max_images) {
                return back()->withInput()->with('status', [
                    'type' => 'danger',
                    'text' => 'Minimum images cannot be greater than maximum images.',
                ]);
            }

            AdSetting::setValue('max_images',       $request->max_images);
            AdSetting::setValue('min_images',       $request->min_images);
            AdSetting::setValue('image_strictness', $request->image_strictness);

            return back()->with('status', ['type' => 'success', 'text' => 'Image settings updated successfully.']);
        }

        $settings = [
            'max_images'       => (int) AdSetting::getValue('max_images',       8),
            'min_images'       => (int) AdSetting::getValue('min_images',       3),
            'image_strictness' => (int) AdSetting::getValue('image_strictness', 7),
        ];

        return view('admin.settings.ad-image-settings', compact('title', 'settings'));
    }

    public function manageAdmin(Request $request)
    {
        $title = "Manage Admins - " . config('global.site_name');
        $admins = Admin::get();

        if ($request->isMethod('POST')) {
            $request->validate([
                'username' => 'required|string|min:3|max:50|unique:admins,username',
                'email' => 'required|email|unique:admins,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
            //dd($request);
            try {
                $admin = Admin::create([
                    'admin_id' => rand(11111,99999),
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                return redirect()->back()->with('status', [
                    'text' => 'Admin user created successfully!',
                    'type' => 'success'
                ]);

            } catch (\Exception $e) {
                return redirect()->back()->with('status', [
                    'text' => 'Error creating admin user. Please try again.',
                    'type' => 'danger'
                ])->withInput($request->except('password', 'password_confirmation'));
            }
        }

        if ($request->isMethod('GET')) {
            return view('admin.settings.admins.users', compact('title','admins'));
        }
    }
}
