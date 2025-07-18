<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Models;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManageCategories extends Controller
{
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

}
