<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\UserVerification;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Advert;
use App\Models\Payment;

class ManageUsers extends Controller
{
    public function active_users(Request $request)
    {
        $title = "Active Users | " . config('global.site_name');
        $page_title = "Active Users";
        $users = User::where('acc_status', 1)->where('disable_account', "no")->orderBy('created_at', 'desc')->paginate(20);

        return view('backend..users.active-users', compact('title', 'users', 'page_title'));
    }

    public function unverified_users(Request $request)
    {
        $title = "Unverified Users | " . config('global.site_name');
        $page_title = "Unverified Users";
        $users = User::where('acc_status', 0)->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.users.unverified-users', compact('title', 'users', 'page_title'));
    }

    public function disabled_users(Request $request)
    {
        $title = "Disabled Users | " . config('global.site_name');
        $page_title = "Disabled Users";
        $users = User::where('acc_status', 1)->where('disable_account', "yes")->orderBy('created_at', 'desc')->paginate(20);

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

    public function search(Request $request)
    {
        try {
            $query = User::query();
            
            // Search term
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('user_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // Account type filter
            if ($request->has('account_type') && !empty($request->account_type)) {
                $query->where('acc_type', $request->account_type);
            }
            
            // Verification filter
            if ($request->has('verification') && !empty($request->verification)) {
                $query->where('verified', $request->verification);
            }
            
            $users = $query->orderBy('created_at', 'desc')->paginate(10);
            
            return response()->json([
                'success' => true,
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching users: ' . $e->getMessage()
            ], 500);
        }
    }

    public function view_user(Request $request, $id)
    {
        $title = "User Details | " . config('global.site_name');
        $user = User::with('adverts')->where('user_id', $id)->first();
        $active_adverts = Advert::where('ad_status', 1)->where('sold', "No")->where('user_id', $id)->count();
        $sold_adverts = Advert::where('sold', "Yes")->where('user_id', $id)->count();
        $totalRevenue = Payment::where('payment_status', "paid")
                ->where('seller_settlement', "yes")
                ->where('user_id', $id)
                ->sum('amount_paid');
        $pendingRevenue = Payment::where('payment_status', "paid")
            ->where('seller_settlement', "no")
            ->where('user_id', $id)
            ->sum('amount_paid');
        return view('backend.users.view-user', compact('title', 'user', 'active_adverts', 'sold_adverts', 'totalRevenue','pendingRevenue'));
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
