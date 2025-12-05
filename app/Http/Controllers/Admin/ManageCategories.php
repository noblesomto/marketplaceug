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
            'meta_title' => 'nullable|max:255',
            'meta_description' => 'nullable|max:500',
            'keywords' => 'nullable|max:500',
        ]);

        // Check if it's an update or create
        if ($request->has('category_id') && $request->input('category_id')) {
            // Update existing category
            $category = Category::find($request->input('category_id'));
            if ($category) {
                $category->update([
                    'category' => $request->input('category'),
                    'meta_title' => $request->input('meta_title'),
                    'meta_description' => $request->input('meta_description'),
                    'keywords' => $request->input('keywords'),
                ]);

                $message = 'Category successfully updated';
            }
        } else {
            // Create new category
            $category = Category::create([
                'category' => $request->input('category'),
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'keywords' => $request->input('keywords'),
            ]);

            $message = 'Category successfully published';
        }

        return redirect('/admin/category')->with('status', ['text' => $message, 'type' => 'success']);
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

    public function edit_category($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return redirect('/admin/category')->with('status', ['text'=>'Category not found','type'=>'danger']);
        }

        // Return JSON for AJAX or redirect with data
        return response()->json($category);
    }

    public function sub_category(Request $request, $id)
{
    $title = "Sub Category - " . config('global.site_name');
    $cat = Category::where('id', $id)->first();
    $subcat = SubCategory::where('cat_id', $id)->orderBy('sub_category','asc')->get();

    if ($request->isMethod('POST')) {
        $request->validate([
            'sub_category' => 'required',
            'category' => 'required',
            'meta_title' => 'nullable|max:255',
            'meta_description' => 'nullable|max:500',
            'keywords' => 'nullable|max:500',
        ]);

        // Check if it's an update or create
        if ($request->has('subcategory_id') && $request->input('subcategory_id')) {
            // Update existing subcategory
            $subcategory = SubCategory::find($request->input('subcategory_id'));
            if ($subcategory) {
                $subcategory->update([
                    'cat_id' => $request->input('category'),
                    'sub_category' => $request->input('sub_category'),
                    'meta_title' => $request->input('meta_title'),
                    'meta_description' => $request->input('meta_description'),
                    'keywords' => $request->input('keywords'),
                ]);

                $message = 'Sub Category successfully updated';
            }
        } else {
            // Create new subcategory
            $subcategory = SubCategory::create([
                'cat_id' => $request->input('category'),
                'sub_category' => $request->input('sub_category'),
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'keywords' => $request->input('keywords'),
            ]);

            $message = 'Sub Category successfully published';
        }

        return redirect('/admin/sub-category/'.$id)->with('status', ['text' => $message, 'type' => 'success']);
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
    $title = "Brands - " . config('global.site_name');
    $cat = SubCategory::where('id', $id)->first();
    $brand = Brands::where('subcat_id', $id)->orderBy('brand','asc')->get();

    if ($request->isMethod('POST')) {
        $request->validate([
            'sub_category' => 'required',
            'brand' => 'required',
            'meta_title' => 'nullable|max:255',
            'meta_description' => 'nullable|max:500',
            'keywords' => 'nullable|max:500',
        ]);

        // Check if it's an update or create
        if ($request->has('brand_id') && $request->input('brand_id')) {
            // Update existing brand
            $brand = Brands::find($request->input('brand_id'));
            if ($brand) {
                $brand->update([
                    'subcat_id' => $request->input('sub_category'),
                    'brand' => $request->input('brand'),
                    'meta_title' => $request->input('meta_title'),
                    'meta_description' => $request->input('meta_description'),
                    'keywords' => $request->input('keywords'),
                ]);

                $message = 'Brand successfully updated';
            }
        } else {
            // Create new brand
            $brand = Brands::create([
                'subcat_id' => $request->input('sub_category'),
                'brand' => $request->input('brand'),
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'keywords' => $request->input('keywords'),
            ]);

            $message = 'Brand successfully published';
        }

        return redirect('/admin/brand/'.$request->input('sub_category'))->with('status', ['text' => $message, 'type' => 'success']);
    }

    if ($request->isMethod('GET')) {
        return view('backend.category.brand', compact('title', 'brand','cat'));
    }
}

    public function model(Request $request, $id)
    {
        $title = "Models - " . config('global.site_name');
        $brand = Brands::where('id', $id)->first();
        $model = Models::where('brand_id', $id)->orderBy('model','asc')->get();

        if ($request->isMethod('POST')) {
            $request->validate([
                'brand' => 'required',
                'model' => 'required',
                'meta_title' => 'nullable|max:255',
                'meta_description' => 'nullable|max:500',
                'year_start' => 'nullable|integer|min:1900|max:' . date('Y'),
                'year_end' => 'nullable|integer|min:1900|max:' . date('Y'),
            ]);

            // Check if it's an update or create
            if ($request->has('model_id') && $request->input('model_id')) {
                // Update existing model
                $model = Models::find($request->input('model_id'));
                if ($model) {
                    $model->update([
                        'brand_id' => $request->input('brand'),
                        'model' => $request->input('model'),
                        'meta_title' => $request->input('meta_title'),
                        'meta_description' => $request->input('meta_description'),
                        'year_start' => $request->input('year_start'),
                        'year_end' => $request->input('year_end'),
                    ]);

                    $message = 'Model successfully updated';
                }
            } else {
                // Create new model
                $model = Models::create([
                    'brand_id' => $request->input('brand'),
                    'model' => $request->input('model'),
                    'meta_title' => $request->input('meta_title'),
                    'meta_description' => $request->input('meta_description'),
                    'year_start' => $request->input('year_start'),
                    'year_end' => $request->input('year_end'),
                ]);

                $message = 'Model successfully published';
            }

            return redirect('/admin/model/'.$request->input('brand'))->with('status', ['text' => $message, 'type' => 'success']);
        }

        if ($request->isMethod('GET')) {
            return view('backend.category.model', compact('title', 'model','brand'));
        }
    }

    public function delete_model($id, $cat)
    {
        $model = Models::where('id', $id)->first();
        $model->delete();

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
        //dd($model);
        return response()->json($model);
    }

}
