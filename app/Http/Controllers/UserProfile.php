<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Advert;
use App\Models\Bank;
use App\Models\UserVerification;
use App\Helpers\FileUploadHelper;
use Carbon\Carbon;
use Hash;
use Mail;
use App\Mail\VerificationRequestMail;

class UserProfile extends Controller
{
    public function profile(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('dashboard.settings.profile', compact('title','user','count_ads'));

    }

    public function settings(Request $request)
    {
        $title = "Seetings | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('dashboard.settings.settings', compact('title','user','count_ads'));

    }


    public function profile_address(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        if ($request->isMethod('GET')) {
            return view('dashboard.settings.profile-address', compact('title','user','count_ads'));
        }

         if ($request->isMethod('PUT')) {

            $request->validate([
                'name' => 'required',
                'address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:12048',

            ]);

            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time().'.'.$image->extension();
                $imageName = str_replace(' ', '-', $imageName);
                $request->file('profile_image')->move('uploads/profile', $imageName);

                $user = DB::table('users')
                    ->where('user_id', $user_id)
                    ->update([
                        'name'=> $request->input('name'),
                        'address'=> $request->input('address'),
                        'city'=> $request->input('city'),
                        'state'=> $request->input('state'),
                        'profile_picture'=> $imageName,
                    ]);
            }else{
                $user = DB::table('users')
                    ->where('user_id', $user_id)
                    ->update([
                        'name'=> $request->input('name'),
                        'address'=> $request->input('address'),
                        'city'=> $request->input('city'),
                        'state'=> $request->input('state'),
                    ]);
            }



             return redirect()->back()->with('success', 'Profile Information updated successfully!');
        }
    }

    public function profile_info(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('dashboard.settings.profile-info', compact('title','user','count_ads'));
    }

    public function profile_phone(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        $request->validate([
            'phone' => 'required|numeric',
        ]);


        $user = DB::table('users')
            ->where('user_id', $user_id)
            ->update([
                'phone'=> $request->input('phone'),
            ]);


        return redirect()->back()->with('success', 'Profile Information updated successfully!');

    }

    public function get_verified(Request $request)
    {
        $title = "Get Verified | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::with('verification')->where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('dashboard.settings.get-verified', compact('title','user','count_ads'));
    }



    public function submit_verification(Request $request)
    {
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->firstOrFail();

        $request->validate([
            'document_number' => 'required|string|max:255',
            'document_type'   => 'nullable|string|max:255',
            'document_file'   => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:12048',
            'proof_address'   => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:12048',
        ]);

        $existingVerification = UserVerification::where('user_id', $user_id)->first();

        // Upload document
        $filename = FileUploadHelper::upload(
            $request->file('document_file'),
            'verification',
            $existingVerification?->document_file
        );

        // Upload proof address if provided
        $proof_address = $existingVerification?->proof_address;
        if ($request->hasFile('proof_address')) {
            $proof_address = FileUploadHelper::upload(
                $request->file('proof_address'),
                'verification',
                $existingVerification?->proof_address
            );
        }

        UserVerification::updateOrCreate(
            ['user_id' => $user_id],
            [
                'document_number' => $request->document_number,
                'document_type'   => $request->document_type,
                'document_file'   => $filename,
                'proof_address'   => $proof_address,
            ]
        );

        // Send email (can use queue)
        Mail::to(config('global.site_email'))->send(new VerificationRequestMail([
            'user_id'         => $user->user_id,
            'name'            => $user->name,
            'email'           => $user->email,
            'document_number' => $request->document_number,
        ]));

        return redirect()->back()->with('success', 'Verification Information submitted successfully!');
    }



    public function payment_info(Request $request)
    {
        $title = "Payment Information | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $banks = Bank::all();
        if ($request->isMethod('GET')) {
            return view('dashboard.settings.payment-info', compact('title','user','count_ads','banks'));
        }

         if ($request->isMethod('PUT')) {

            //dd($request);
            $request->validate([
                'bank_name' => 'required',
                'paystack_bank_code' => 'required',
                'account_number' => 'required',
                'account_name' => 'required',
            ]);


            $user = DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'bank_name'=> $request->input('bank_name'),
                    'bank_code'=> $request->input('paystack_bank_code'),
                    'account_name'=> $request->input('account_name'),
                    'account_number'=> $request->input('account_number'),
                ]);


             return redirect()->back()->with('success', 'Payment Information updated successfully!');
        }
    }

    public function change_password(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $password = $request->old_password;

        if (Hash::check($password, $user->password)) {

            $user = DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'password'=> Hash::make($request->input('password')),
                ]);

            return redirect()->back()->with('success', 'Password Changed successfully!');
        }else{

            return redirect()->back()->with('error', 'The Current Password Does not match!');
        }

    }

    public function disable_account(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();


        $user = DB::table('users')
            ->where('user_id', $user_id)
            ->update([
                'acc_status'=> 0,
                'disable_account'=> "yes",
                'disable_account_date'=> Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        $request->session()->forget('user_id');
        return redirect("login")->with('success', 'Account Disabled successfully!');

    }

    public function profile_notification(Request $request)
    {
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('dashboard.settings.profile-notification', compact('title','user','count_ads'));
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'notifications' => 'required'
        ]);

        //dd($validated['notifications']);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $user->notification = $validated['notifications'];
        $user->save();

        return response()->json([
            'success' => true,
         ]);
    }
}
