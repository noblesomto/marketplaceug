<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Advertising;
use Carbon\Carbon;

class ManageAdvertising extends Controller
{
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
}
