<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Advert;
use Carbon\Carbon;
use App\Helpers\FileUploadHelper;

class ManageAdverts extends Controller
{

    private function applyAdvertSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('ad_title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%")
              ->orWhere('state', 'LIKE', "%{$searchTerm}%")
              ->orWhere('price', 'LIKE', "%{$searchTerm}%")
              ->orWhere('salary', 'LIKE', "%{$searchTerm}%")
              ->orWhere('expected_salary', 'LIKE', "%{$searchTerm}%")
              ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                  $userQuery->where('name', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('email', 'LIKE', "%{$searchTerm}%");
              });
        });
    }


    public function active_adverts(Request $request)
    {
        $title = "Active Adverts | " . config('global.site_name');
        $page_title = "Active Adverts";

        $query = Advert::with(['user', 'firstImage'])
                ->where("ad_status", 'active')
                ->where("sold", "No");

        // Apply search if present
        if ($request->filled('query')) {
            $query = $this->applyAdvertSearch($query, $request->input('query'));
        }

        $adverts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.advert.adverts', compact('title', 'page_title','adverts'));
    }

    public function disabled_adverts(Request $request)
    {
        $title = "Disabled Adverts | " . config('global.site_name');
        $page_title = "Disabled Adverts";

        $query = Advert::with(['user', 'firstImage'])
                ->where("ad_status", '<>', 'active')
                ->where("sold", "No");

        // Apply search if present
        if ($request->filled('query')) {
            $query = $this->applyAdvertSearch($query, $request->input('query'));
        }

        $adverts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.advert.adverts', compact('title', 'page_title', 'adverts'));
    }

    // all adverts method
    public function all_adverts(Request $request)
    {
        $title = "All Adverts | " . config('global.site_name');
        $page_title = "All Adverts";

        $query = Advert::with(['user', 'firstImage']);

        // Apply search if present
        if ($request->filled('query')) {
            $query = $this->applyAdvertSearch($query, $request->input('query'));
        }

        $adverts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.advert.adverts', compact('title', 'page_title', 'adverts'));
    }

    // sold adverts
    public function sold_adverts(Request $request)
    {
        $title = "Sold Adverts | " . config('global.site_name');
        $page_title = "Sold Adverts";

        $query = Advert::with(['user', 'firstImage'])
                ->where("sold", "Yes");

        // Apply search if present
        if ($request->filled('query')) {
            $query = $this->applyAdvertSearch($query, $request->input('query'));
        }

        $adverts = $query->orderBy('created_at', 'desc')->paginate(20);

         return view('backend.advert.sold-adverts', compact('title', 'page_title','adverts'));
    }

    public function sold_adverts44(Request $request)
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

        // Delete all associated images safely
        foreach ($advert->images ?? [] as $image) {
            if ($image && !empty($image->image)) {
                try {
                    // Use your helper to delete the file
                    FileUploadHelper::delete('images', $image->image);

                    // Delete the image record from database
                    $image->delete();

                } catch (\Exception $imgEx) {
                    throw new \Exception("Failed to delete image {$image->image}: " . $imgEx->getMessage());
                }
            }
        }

        // Delete the advert itself
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




}
