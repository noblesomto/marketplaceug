<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Advert;

class ManageUsers extends Controller
{
       public function active_users(Request $request)
    {
        $title = "Active Users | " . config('global.site_name');
        $page_title = "Active Users";
        $users = User::where('acc_status', 1)->orderBy('created_at', 'desc')->paginate(10);

        return view('backend.users', compact('title', 'users', 'page_title'));
    }

    public function disabled_users(Request $request)
    {
        $title = "Disabled Users | " . config('global.site_name');
        $page_title = "Disabled Users";
        $users = User::where('acc_status', 0)->orderBy('created_at', 'desc')->paginate(10);

        return view('backend.users', compact('title', 'users', 'page_title'));
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

        return view('backend.view-user', compact('title', 'user'));
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
}
