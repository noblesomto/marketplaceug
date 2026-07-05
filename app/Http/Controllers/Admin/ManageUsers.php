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
use Illuminate\Support\Facades\Auth;

class ManageUsers extends Controller
{
    public function active_users(Request $request)
    {
        $title = "Active Users | " . config('global.site_name');
        $page_title = "Active Users";
        $users = User::where('acc_status', 1)->where('disable_account', 'no')->orderBy('created_at', 'desc')->paginate(20);

        $stats = $this->globalUserStats();

        return view('admin.users.active-users', compact('title', 'users', 'page_title', 'stats'));
    }

    public function unverified_users(Request $request)
    {
        $title = "Unverified Users | " . config('global.site_name');
        $page_title = "Unverified Users";
        $users = User::where('acc_status', 0)->where('disable_account', 'no')->orderBy('created_at', 'desc')->paginate(20);

        $stats = $this->globalUserStats();

        return view('admin.users.unverified-users', compact('title', 'users', 'page_title', 'stats'));
    }

    public function disabled_users(Request $request)
    {
        $title = "Disabled Users | " . config('global.site_name');
        $page_title = "Disabled Users";
        // No acc_status filter — show ALL disabled users regardless of verification state
        $users = User::where('disable_account', 'yes')->orderBy('disable_account_date', 'desc')->paginate(20);

        $stats = $this->globalUserStats();

        return view('admin.users.disabled-users', compact('title', 'users', 'page_title', 'stats'));
    }

    public function disable_status(Request $request, $id)
    {
        $request->validate([
            'action'  => 'required|in:disable,enable',
            'reason'  => 'nullable|string|max:500',
        ]);

        $action = $request->input('action');
        $isDisabling = $action === 'disable';

        $admin = Auth::guard('admin')->user();

        DB::table('users')
            ->where('user_id', $id)
            ->update([
                'disable_account'      => $isDisabling ? 'yes' : 'no',
                'disable_account_date' => $isDisabling ? Carbon::now() : null,
                'disabled_by'          => $isDisabling ? ($admin->name ?? 'Admin') : null,
                'disable_reason'       => $isDisabling ? $request->input('reason') : null,
                'updated_at'           => Carbon::now(),
            ]);

        $text = $isDisabling ? 'User account has been disabled.' : 'User account has been re-enabled.';

        return redirect()->back()->with('status', ['text' => $text, 'type' => 'success']);
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

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('user_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->filled('account_type')) {
                $query->where('acc_type', $request->account_type);
            }

            if ($request->filled('verification')) {
                $query->where('verified', $request->verification);
            }

            // Scope to the current admin page context
            $context = $request->input('context', 'active');
            if ($context === 'disabled') {
                $query->where('disable_account', 'yes');
            } elseif ($context === 'unverified') {
                $query->where('acc_status', 0)->where('disable_account', 'no');
            } else {
                // active
                $query->where('acc_status', 1)->where('disable_account', 'no');
            }

            $users = $query->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'success' => true,
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                    'from'         => $users->firstItem(),
                    'to'           => $users->lastItem(),
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
        return view('admin.users.view-user', compact('title', 'user', 'active_adverts', 'sold_adverts', 'totalRevenue','pendingRevenue'));
    }

    public function delete_user($user_id)
    {
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            Advert::where('user_id', $user_id)->delete();
            $user->delete();
        }

        return redirect()->route('admin.active.users')->with('status', ['text' => 'User deleted successfully.', 'type' => 'success']);
    }

    public function user_verification(Request $request)
    {
        $title = "User Verification | " . config('global.site_name');
        $page_title = "User Verification";
        $users = UserVerification::with('user')->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.users.user-verification', compact('title', 'users', 'page_title'));
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

    private function globalUserStats(): array
    {
        return [
            'total'    => User::count(),
            'active'   => User::where('acc_status', 1)->where('disable_account', 'no')->count(),
            'disabled' => User::where('disable_account', 'yes')->count(),
        ];
    }
}
