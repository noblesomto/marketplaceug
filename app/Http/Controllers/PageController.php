<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Admin;
use App\Models\Advert;
use App\Models\AdvertImage;
use App\Models\Category;
use App\Models\SubCategory;
use Mail;
use Hash;
use App\Mail\RegisterMail;
use App\Mail\NotifyMail;

class PageController extends Controller
{
    public function index(Request $request)
    {   
        $title = config('global.site_name') . " | " . config('global.site_title');
        /**
        $featured = Advert::inRandomOrder()
            ->where('featured', "Yes")
            ->where('sold', 'No')
            ->where('ad_status', 1)
            ->whereHas('boost', function($query) {
                $query->where('boost_status', 'active');
            })
            ->with(['firstImage', 'boost' => function($query) {
                $query->where('boost_status', 'active');
            }])
            ->get()
            ->filter(function ($advert) {
                return $advert->boost->contains(function ($boost) {
                    return $boost->is_active;
                });
            })
            ->take(6) // Take only 6 after filtering
            ->values(); // Reindex collection
        **/
        $featured = Advert::inRandomOrder()
            ->where('ad_status', 1)
            ->where(function($query) {
            $query->where('sold', '!=', 'Yes')
                  ->orWhere(function($query) {
                      $query->where('sold', 'Yes')
                            ->whereNotNull('sold_date')
                            ->where('sold_date', '>=', now()->subDays(7));
                  });
        })
        ->orderBy('views', 'desc')
        ->limit(10)
        ->get();
            //dd($featured);
        $featuredAds = Advert::with('firstImage')
            ->where('ad_status', 1)
            ->where('featured', 'yes')
            ->where(function($query) {
                $query->where('sold', '!=', 'Yes')
                      ->orWhere(function($query) {
                          $query->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(7));
                      });
            })
            ->inRandomOrder()
            ->limit(10)
            ->get(); // no limit here; you can add limit if needed

        // Step 2: Get the rest of the ads ordered by created_at
        $otherAds = Advert::with('firstImage')
            ->where('ad_status', 1)
            ->where('featured', '!=', 'yes') // or ->whereNull('featured') if column can be null
            ->where(function($query) {
                $query->where('sold', '!=', 'Yes')
                      ->orWhere(function($query) {
                          $query->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(7));
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Step 3: Merge and take only 20 results
        $ads = $featuredAds->merge($otherAds)->take(20);
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        
        //dd($categories);
        return view('frontend.index', compact('title','ads','featured','user','categories'));
    }

    public function about()
    {   
        $title = "About Us  | " . config('global.site_name');
        return view('frontend.pages.about', compact('title'));
    }

    public function career()
    {
        $title = "Career  | " . config('global.site_name');
        return view('frontend.pages.career', compact('title'));
    }

    public function privacy()
    {
        $title = "Privacy Policy  | " . config('global.site_name');
        return view('frontend.pages.privacy', compact('title'));
    }

    public function cookie()
    {
        $title = "Cookie Policy  | " . config('global.site_name');
        return view('frontend.pages.cookie', compact('title'));
    }

    public function billing()
    {
        $title = "Billing Policy  | " . config('global.site_name');
        return view('frontend.pages.billing', compact('title'));
    }

    public function copyright()
    {
        $title = "Copyright Policy  | " . config('global.site_name');
        return view('frontend.pages.copyright', compact('title'));
    }

    public function safety()
    {   
        $title = "Tips for your safety  | " . config('global.site_name');
        return view('frontend.pages.safety', compact('title'));
    }

    public function terms()
    {
        $title = "Terms of Use  | " . config('global.site_name');
        return view('frontend.pages.terms', compact('title'));
    }

    public function payments_refunds()
    {
        $title = "Terms of Use  | " . config('global.site_name');
        return view('frontend.pages.payments-refunds', compact('title'));
    }

    public function faq()
    {   
        $title = "FAQ  | " . config('global.site_name');
        return view('frontend.pages.faq', compact('title'));
    }

    public function contact(Request $request)
    {
        $title = 'Contact Us | '.config('global.site_name');

        if ($request->isMethod('GET')) {
            return view('frontend.pages.contact-us', compact('title'));
        }


        if ($request->isMethod('POST')) {
            $request->validate([
                'name' => 'required',
                'phone' => 'required',
                'subject' => 'required',
                'email' => 'required|email',
                'message' => 'required',
                'g-recaptcha-response' => ['required', new ReCaptcha],
            ]);

            $details = [
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
            ];

            $admin_email = config('global.site_email');
            try {
                Mail::to($admin_email)->send(new ContactMail($details));

               return redirect()->back()->with('success', 'Great! Your message was successfully sent, We will get back to you ASAP');
            } catch (Throwable $e) {

                return redirect()->back()->with('error', 'Error!, Your Email could not be sent, please contact admin: '.config('global.site_email'));
            }

        }
    }

    public function shipping()
    {
        $title = "Shipping  | " . config('global.site_name');
        return view('frontend.shipping.shipping', compact('title'));
    }



    public function adminlogin(Request $request)
    {
        $title = "Admin Login" . config('global.site_title');

        if ($request->isMethod('POST')) {
            $request->validate([
                'username' => 'required',
                'password' => 'required|min:4',
            ]);
            
            $username = $request->username;
            $password = $request->password;

            $login = Admin::where('username', $username)
               ->where('password', md5($password))
               ->first();
            if ($login) {
                $admin_id = $login->admin_id;
                $request->session()->put('admin_id', $admin_id);

               return redirect()->action([AdminController::class, 'index']);
            }
      
            return redirect("admin/login")->with('status',['text'=>'Sorry! you enter wrong credentials ','type'=>'danger']);
        }

        if ($request->isMethod('GET')) {
            return view('frontend.admin', compact('title'));
        }
    }

    public function getIp(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function email()
    {   
        $title = "Email" . config('global.site_title');
        $details = [
                'amount' => '93244',
                'email'=>'noblesomto@gmail.com',
                'time' => '03:20',
                'ip' => 'hqn9223',
                'phone' => '090434353',
                'postcode' => '093033',
                'city' => 'Lekki',
                'state' => 'Lagos',
                'bedrooms' => '2 bedrooms',
                'checkin' => '2025-02-14',
                'checkout' => '2025-03-01',
                'advert' => 'Stunning Modern 2-Floor House with 3 Ensuite Rooms',
                'address' => 'Entire home in Greater London, United Kingdom',
                'token' => '093033',
                'name' => "noble",
                'buyer' => "noble",
                'user_id' => "5244",
                'book_id' => "GO5Ka244",
                'date' => "5-2-44",
                'otp'=>"049403",
                'currency'=>"USD",
                'email_subject'=>"049403",
                'email_body'=>"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis mattis vitae quam vel viverra. Etiam vitae orci sit amet quam euismod tincidunt. Cras eu porttitor nunc. Integer lacinia augue nibh, ac pretium nunc venenatis vitae. Fusce sapien elit, commodo vitae purus id, ultricies placerat lectus. Sed non sagittis augue. Donec in consequat turpis. Donec vel lectus tempor, fringilla dolor at, varius mi. Praesent a nulla maximus, blandit ex non, rutrum augue. Pellentesque sit amet turpis luctus, porta purus vitae, lobortis tellus. Aliquam erat volutpat. Ut eget quam euismod, feugiat eros in, venenatis quam. Nam vitae nibh in augue venenatis porta non in nunc. Vivamus iaculis ut enim nec egestas. Morbi tristique lectus in orci ultricies, id imperdiet massa consectetur. Donec semper diam in laoreet hendrerit.<br><br>

Morbi faucibus pulvinar lectus. Sed vehicula elit ac cursus porttitor. Aenean augue quam, vehicula iaculis vulputate sit amet, pretium et mauris. Maecenas ut scelerisque sem. Morbi in purus non eros ullamcorper placerat. Etiam semper sit amet ex non dictum. Phasellus facilisis mauris vitae tellus finibus, a semper justo bibendum.

Nunc justo velit, dictum sed est ut, porttitor semper odio. Fusce sit amet diam vitae lectus pretium mattis a a quam. Morbi dictum viverra metus. Donec sed lectus nec risus laoreet gravida ut non leo. Mauris sed tellus lorem. Pellentesque sit amet dolor a tortor viverra imperdiet ut sed metus. Vivamus venenatis sem risus, sed aliquet sem lobortis in. Nam quis erat vel tortor aliquam cursus eget id justo. Sed sollicitudin ex pellentesque libero feugiat mollis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Aenean rutrum volutpat placerat. Vestibulum sed congue lorem, sit amet posuere dui. Proin sagittis mi odio, id congue mi viverra at. Pellentesque sit amet tellus eget quam fringilla tincidunt. Morbi aliquam dolor ut nisl semper ultricies.",
            ];
        return view('email.buyMail', compact('title','details'));
    }
}
