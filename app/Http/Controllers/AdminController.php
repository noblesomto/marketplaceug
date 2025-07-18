<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use App\Models\Advert;
use Carbon\Carbon;
use App\Models\Reports;
use Illuminate\Support\Facades\Storage;


class AdminController extends Controller
{
    public function index()
    {   
        $title = "Admin Section -  " . config('global.site_name');
        $count_users = User::where('acc_status', 1)->count();
        $count_adverts = Advert::where('ad_status', 1)->count();
        return view('backend.index', compact('title','count_users','count_adverts'));
    }


    public function view_reports(Request $request)
    {
        $title = "Ad Reports | " . config('global.site_name');
        $page_title = "Ad Reports";
        $adverts = Reports::with(['user', 'adverts'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        
        return view('backend.reports', compact('title', 'page_title','adverts'));
    }

    public function report_status($id, $status)
    {   
        DB::table('reports')
                ->where('id', $id)
                ->update([
                    'status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);
     
        return redirect()->back()->with('status', ['text'=>'Report Status Changed','type'=>'success']);
    }

    public function logout(Request $request)
    {   
        $request->session()->forget('admin_id');
        $request->session()->flush();
        return redirect("admin")->with('status', ['text'=>'Logged out Successfully','type'=>'success']);
    }
}
