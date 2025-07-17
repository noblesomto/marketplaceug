<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use App\Models\Advert;
use App\Models\Payment;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Models;
use App\Models\Advertising;
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


    public function category(Request $request)
    {   
        $title = "Category - " . config('global.site_name');
        $category = Category::orderBy('category','asc')->get();
        $sub_category = SubCategory::orderBy('sub_category','asc')->get();

        if ($request->isMethod('POST')) {
            $request->validate([
                'category' => 'required',
               ]);
          
            $post = Category::create([
                'category'=> $request->input('category'),
            ]);

            return redirect('/admin/category')->with('status', ['text'=>'Category  Successfully published','type'=>'success']);
        }

        if ($request->isMethod('GET')) {
            return view('backend.category.category', compact('title', 'category', 'sub_category'));
        }
        
    }

    public function delete_category($id) 
    {
        $cat = Category::where('cat_id', $id)->first();
        $cat->delete();
        $subcat = SubCategory::where('cat_id', $id)->first();
        if($subcat !=null){
          $subcat->delete();  
        }
        $brand = Brands::where('cat_id', $id)->first();
        if($brand !=null){
          $brand->delete();  
        }

        return redirect("admin/category")->with('status', ['text'=>'Category was deleted','type'=>'success']);

    }

    public function sub_category(Request $request, $id)
    {   
        $title = "Category - " . config('global.site_name');
        $cat = Category::where('id', $id)->first();
        $subcat = SubCategory::where('cat_id', $id)->orderBy('sub_category','asc')->get();
            if ($request->isMethod('POST')) {
            $request->validate([
                'sub_category' => 'required',
                'category' => 'required',
               ]);
          
            $post = SubCategory::create([
                'cat_id'=> $request->input('category'),
                'sub_category'=> $request->input('sub_category'),
            ]);

            return redirect('/admin/sub-category/'.$id)->with('status', ['text'=>'Sub Category  Successfully published','type'=>'success']);
         }
        if ($request->isMethod('GET')) {
            return view('backend.category.sub-category', compact('title', 'cat','subcat'));
        }
    }

    public function delete_subcategory($id, $cat) 
    {
        $subcat = SubCategory::where('id', $id)->first();
        $subcat->delete();
        $brand = Brands::where('subcat_id', $id)->first();
        if($brand !=null){
          $brand->delete();  
        }

        return redirect("admin/sub-category/".$cat)->with('status', ['text'=>'Sub Category was deleted','type'=>'success']);

    }

    public function brand(Request $request, $id)
    {   
        $title = "Category - " . config('global.site_name');
        $cat = SubCategory::where('id', $id)->first();
        $brand = Brands::where('subcat_id', $id)->orderBy('brand','asc')->get();
        //dd($cat);
            if ($request->isMethod('POST')) {
            $request->validate([
                'sub_category' => 'required',
                'brand' => 'required',
               ]);
          
            $post = Brands::create([
                'subcat_id'=> $request->input('sub_category'),
                'brand'=> $request->input('brand'),
            ]);

            return redirect('/admin/brand/'.$id)->with('status', ['text'=>'Brands  Successfully published','type'=>'success']);
        }
        if ($request->isMethod('GET')) {
            return view('backend.category.brand', compact('title', 'brand','cat'));
        }
        
    }

    public function delete_brand($id,$cat) 
    {

        $brand = Brands::where('id', $id)->first();
        $brand->delete();

        return redirect("admin/brand/".$cat)->with('status', ['text'=>'Brand was deleted','type'=>'success']);

    }

    public function model(Request $request, $id)
    {   
        $title = "Category - " . config('global.site_name');
        $brand = Brands::where('id', $id)->first();
        $model = Models::where('brand_id', $id)->orderBy('model','asc')->get();
        //dd($cat);
            if ($request->isMethod('POST')) {
            $request->validate([
                'brand' => 'required',
                'model' => 'required',
               ]);
          
            $post = Models::create([
                
                'brand_id'=> $request->input('brand'),
                'model'=> $request->input('model'),
            ]);

            return redirect('/admin/model/'.$id)->with('status', ['text'=>'Model  Successfully published','type'=>'success']);
        }
        if ($request->isMethod('GET')) {
            return view('backend.category.model', compact('title', 'model','brand'));
        }
        
    }

    public function delete_model($id,$cat) 
    {

        $brand = Models::where('model_id', $id)->first();
        $brand->delete();

        return redirect("admin/model/".$cat)->with('status', ['text'=>'Model was deleted','type'=>'success']);

    }

    public function fetch_subcat($cat_id)
    {
        $subcat = SubCategory::where('cat_id', $cat_id)->get();
        return response()->json($subcat);
    }

    public function fetch_brand($cat_id)
    {
        $brand = Brands::where('subcat_id', $cat_id)->get();
        return response()->json($brand);
    }

    public function fetch_model($cat_id)
    {
        $model = Models::where('brand_id', $cat_id)->get();
        return response()->json($model);
    }


    public function active_adverts(Request $request)
    {
        $title = "Active Adverts | " . config('global.site_name');
        $page_title = "Active Adverts";
        $adverts = Advert::with(['user', 'firstImage'])
                ->where("ad_status", 1)
                ->where("sold", "No")
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        
        return view('backend.advert.adverts', compact('title', 'page_title','adverts'));
    }

    public function disabled_adverts(Request $request)
    {
        $title = "Disabled Adverts | " . config('global.site_name');
        $page_title = "Disabled Adverts";
        $adverts = Advert::with(['user', 'firstImage'])
                ->where("ad_status", 0)
                ->where("sold", "No")
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        
        return view('backend.advert.adverts', compact('title', 'page_title', 'adverts'));
    }

    public function sold_adverts(Request $request)
    {
        $title = "Sold Adverts | " . config('global.site_name');
        $page_title = "Sold Adverts";
        $adverts = Advert::with(['user', 'firstImage'])
                ->where("sold", "Yes")
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        
        return view('backend.advert.sold-adverts', compact('title', 'page_title','adverts'));
    }

    public function advert_status($id, $status)
    {   
        DB::table('adverts')
                ->where('id', $id)
                ->update([
                    'ad_status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);
     
        return redirect()->back()->with('status', ['text'=>'Advert Status Changed','type'=>'success']);
    }

    public function sold_status($id, $status)
    {   
        DB::table('adverts')
                ->where('id', $id)
                ->update([
                    'sold'=> $status,
                    'updated_at' => Carbon::now(),
                ]);
     
        return redirect()->back()->with('status', ['text'=>'Advert Status Changed','type'=>'success']);
    }



    public function delete_advert($id) 
{
    try {
        \DB::beginTransaction();

        $advert = Advert::with('images')->find($id);
        
        if (!$advert) {
            return redirect()->back()->with('status', [
                'text' => 'Advert not found',
                'type' => 'error'
            ]);
        }

        // Delete all associated images
        foreach ($advert->images as $image) {
            $absolutePath = public_path('uploads/images/' . $image->image);
            
            // Check if file exists before trying to delete
            if (file_exists($absolutePath)) {
                if (!unlink($absolutePath)) {
                    throw new \Exception("Failed to delete image file: " . $absolutePath);
                }
            }
            
            // Optional: Delete the image record from database
            // $image->delete();
        }

        // Delete the advert
        $advert->delete();

        \DB::commit();

        return redirect()->back()->with('status', [
            'text' => 'Advert and all images deleted successfully',
            'type' => 'success'
        ]);

    } catch (\Exception $e) {
        \DB::rollBack();
        
        return redirect()->back()->with('status', [
            'text' => 'Error deleting advert: ' . $e->getMessage(),
            'type' => 'error'
        ]);
    }
}

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

    public function delete_user($user_id)
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
                ->where('user_id', $id)
                ->update([
                    'acc_status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);
     
        return redirect()->back()->with('status', ['text'=>'User Deleted','type'=>'success']);
    }

    public function completed_payments(Request $request)
    {
        $title = "Completed Payments | " . config('global.site_name');
        $page_title = "Completed Payments";
        
        $payments = Payment::with([
                'advert.firstImage', // This loads the advert and its firstImage
                'user'
            ])
            ->where("payment_status", "paid")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        //dd($payments);
        return view('backend.payments', compact('title', 'page_title', 'payments'));
    }

    public function pending_payments(Request $request)
    {
        $title = "Pending Payments | " . config('global.site_name');
        $page_title = "Pending/Failed Payments";
        
        $payments = Payment::with([
                'advert.firstImage', // This loads the advert and its firstImage
                'user'
            ])
            ->where("payment_status",'!=', "paid")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('backend.payments', compact('title', 'page_title', 'payments'));
    }

    public function confirm_delivery($id)
    {   
        DB::table('payments')
                ->where('id', $id)
                ->update([
                    'shipping_status'=> "delivered",
                    'buyer_status'=> "delivered",
                    'updated_at' => Carbon::now(),
                ]);
     
        return redirect()->back()->with('status', ['text'=>'Delivery Status Updated','type'=>'success']);
    }

    public function create_advert(Request $request)
    {   
        $title = "New Adverts - " . config('global.site_name');
        $adverts = Advertising::orderBy('created_at', 'desc')->paginate(20);
        //dd($cat);
            if ($request->isMethod('POST')) {
            $request->validate([
                'company' => 'required',
                'advert_image' => 'required|image|mimes:jpg,png,jpeg,gif|max:3048',
                'url' => 'required|url',
                'duration' => 'required',
               ]);

            $image = $request->file('advert_image');
            $imageName = time().'.'.$image->extension();
            $imageName = str_replace(' ', '-', $imageName);
            $request->file('advert_image')->move('uploads/advertising', $imageName);
          
            $post = Advertising::create([
                'advert_id'=> rand(11111,99999),
                'company'=> $request->input('company'),
                'url'=> $request->input('url'),
                'duration'=> $request->input('duration'),
                'type'=> $request->input('type'),
                'image'=> $imageName,
                'status'=> 1,
            ]);

            return redirect()->back()->with('status', ['text'=>'Advert  Successfully published','type'=>'success']);
        }
        if ($request->isMethod('GET')) {
            return view('backend.advertising.create-advert', compact('title', 'adverts'));
        }
        
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
