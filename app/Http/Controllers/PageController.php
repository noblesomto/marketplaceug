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
use App\Models\Blog;
use Mail;
use Hash;
use App\Mail\RegisterMail;
use App\Mail\NotifyMail;

class PageController extends Controller
{

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
        $title = "Payment and Refunds  | " . config('global.site_name');
        return view('frontend.pages.payments-refunds', compact('title'));
    }

    public function faq()
    {
        $title = "FAQ  | " . config('global.site_name');
        return view('frontend.pages.faq', compact('title'));
    }

    public function how_it_works()
    {
        $title = "How It Works  | " . config('global.site_name');
        return view('frontend.pages.how-it-works', compact('title'));
    }

    public function advertise()
    {
        $title = "Advertise With Us  | " . config('global.site_name');
        return view('frontend.pages.advertise', compact('title'));
    }

    public function blog()
    {
        $title = "Our Blog  | " . config('global.site_name');
        $blogs = Blog::where('status', 'published')->latest()->paginate(10);
        return view('frontend.blog.index', compact('title','blogs'));
    }

    public function blog_details($slug)
    {
        $title = "Our Blog  | " . config('global.site_name');
        $blog = Blog::where('slug',$slug)->first();

        // Check if blog exists
        if (!$blog) {
            abort(404);
        }
        //dd($slug);
        $blog->increment('views');
        $similar = Blog::where('category', $blog->category)
            ->where('slug', '!=', $slug)
            ->where('status','published')
            ->inRandomOrder()
            ->limit(2)
            ->get();
        //dd($similar);
        return view('frontend.blog.blog-details', compact('title','blog','similar'));
    }

    public function page()
    {
        $title = "FAQ  | " . config('global.site_name');
        return view('frontend.page', compact('title'));
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
                'shipping' => '2 bedrooms',
                'shipped_date' => '2025-02-14',
                'checkout' => '2025-03-01',
                'title' => 'Stunning Modern 2-Floor House with 3 Ensuite Rooms',
                'subject' => 'Stunning Modern 2-Floor House with 3 Ensuite Rooms',
                'advert' => 'Stunning Modern 2-Floor House with 3 Ensuite Rooms',
                'address' => 'Entire home in Greater London, United Kingdom',
                'token' => '093033',
                'name' => "noble",
                'buyer' => "noble",
                'seller' => "noble",
                'user_id' => "5244",
                'tracking_id' => "GO5Ka244",
                'date' => "5-2-44",
                'otp'=>"049403",
                'ship_code'=>"049403",
                'currency'=>"USD",
                'email_subject'=>"049403",
                'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis mattis vitae quam vel viverra. Etiam vitae orci sit amet quam euismod tincidunt.',
                'email_body'=>"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis mattis vitae quam vel viverra. Etiam vitae orci sit amet quam euismod tincidunt. Cras eu porttitor nunc. Integer lacinia augue nibh, ac pretium nunc venenatis vitae. Fusce sapien elit, commodo vitae purus id, ultricies placerat lectus. Sed non sagittis augue. Donec in consequat turpis. Donec vel lectus tempor, fringilla dolor at, varius mi. Praesent a nulla maximus, blandit ex non, rutrum augue. Pellentesque sit amet turpis luctus, porta purus vitae, lobortis tellus. Aliquam erat volutpat. Ut eget quam euismod, feugiat eros in, venenatis quam. Nam vitae nibh in augue venenatis porta non in nunc. Vivamus iaculis ut enim nec egestas. Morbi tristique lectus in orci ultricies, id imperdiet massa consectetur. Donec semper diam in laoreet hendrerit.<br><br>

Morbi faucibus pulvinar lectus. Sed vehicula elit ac cursus porttitor. Aenean augue quam, vehicula iaculis vulputate sit amet, pretium et mauris. Maecenas ut scelerisque sem. Morbi in purus non eros ullamcorper placerat. Etiam semper sit amet ex non dictum. Phasellus facilisis mauris vitae tellus finibus, a semper justo bibendum.

Nunc justo velit, dictum sed est ut, porttitor semper odio. Fusce sit amet diam vitae lectus pretium mattis a a quam. Morbi dictum viverra metus. Donec sed lectus nec risus laoreet gravida ut non leo. Mauris sed tellus lorem. Pellentesque sit amet dolor a tortor viverra imperdiet ut sed metus. Vivamus venenatis sem risus, sed aliquet sem lobortis in. Nam quis erat vel tortor aliquam cursus eget id justo. Sed sollicitudin ex pellentesque libero feugiat mollis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Aenean rutrum volutpat placerat. Vestibulum sed congue lorem, sit amet posuere dui. Proin sagittis mi odio, id congue mi viverra at. Pellentesque sit amet tellus eget quam fringilla tincidunt. Morbi aliquam dolor ut nisl semper ultricies.",
            ];
        return view('email.registerMail', compact('title','details'));
    }
}
