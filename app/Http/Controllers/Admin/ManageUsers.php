<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\UserVerification;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Advert;

class ManageUsers extends Controller
{
    public function active_users(Request $request)
    {
        $title = "Active Users | " . config('global.site_name');
        $page_title = "Active Users";
        $users = User::where('acc_status', 1)->where('disable_account', "no")->orderBy('created_at', 'desc')->paginate(10);

        return view('backend..users.active-users', compact('title', 'users', 'page_title'));
    }

    public function unverified_users(Request $request)
    {
        $title = "Unverified Users | " . config('global.site_name');
        $page_title = "Unverified Users";
        $users = User::where('acc_status', 0)->orderBy('created_at', 'desc')->paginate(10);

        return view('backend.users.unverified-users', compact('title', 'users', 'page_title'));
    }

    public function disabled_users(Request $request)
    {
        $title = "Disabled Users | " . config('global.site_name');
        $page_title = "Disabled Users";
        $users = User::where('acc_status', 1)->where('disable_account', "yes")->orderBy('created_at', 'desc')->paginate(10);

        return view('backend.users.active-users', compact('title', 'users', 'page_title'));
    }

    public function disable_status($id, $status)
    {
        DB::table('users')
                ->where('user_id', $id)
                ->update([
                    'disable_account'=> $status,
                    'disable_account_date'=> Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'User Status Changed','type'=>'success']);
    }

    public function user_status($id, $status)
    {
        DB::table('users')
                ->where('user_id', $id)
                ->update([
                    'acc_status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'User Status Changed','type'=>'success']);
    }



    public function view_user(Request $request, $id)
    {
        $title = "User Details | " . config('global.site_name');
        $user = User::where('user_id', $id)->first();

        return view('backend.users.view-user', compact('title', 'user'));
    }

    public function delete_user($user_id,$status)
    {

        $user = User::where('user_id', $user_id)->first();
        if ($user){
            $user->delete();
        }

        $adverts = Advert::where('user_id', $user_id)->get();
        if ($adverts){
            $adverts->delete();
        }

        DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'acc_status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'User Deleted','type'=>'success']);
    }

    public function user_verification(Request $request)
    {
        $title = "User Verification | " . config('global.site_name');
        $page_title = "User Verification";
        $users = UserVerification::with('user')->orderBy('created_at', 'desc')->paginate(10);

        return view('backend.users.user-verification', compact('title', 'users', 'page_title'));
    }

    public function verify_status($id, $status, $verify)
    {
        DB::table('user_verifications')
            ->where('user_id', $id)
            ->update([
                'verify_status'=> $status,
                'updated_at' => Carbon::now(),
            ]);

        DB::table('users')
                ->where('user_id', $id)
                ->update([
                    'verified'=> $verify,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'Verification Status Changed','type'=>'success']);
    }
}
