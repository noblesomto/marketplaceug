<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Rules\ReCaptcha;
use Mail;
use Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Shipping;
use App\Mail\RegisterMail;
use App\Mail\OTPMail;
use App\Mail\PasswordMail;

class AccountController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('GET')) {
            return $this->showLoginForm();
        }

        return $this->processLoginAttempt($request);
    }

    protected function showLoginForm()
    {
        $title = "Login | " . config('global.site_name');
        return view('frontend.account.login', compact('title'));
    }

    protected function processLoginAttempt(Request $request)
    {
        $request->validate($this->loginValidationRules());

        $user = $this->getUserByEmail($request->email);

        if (!$this->isValidUser($user)) {
            return $this->failedLoginResponse($user);
        }

        if (!$this->isValidPassword($user, $request->password)) {
            return redirect('/login')->with('error', 'Sorry, the password does not match.');
        }

        if ($this->isTrustedDevice($user, $request)) {
            return $this->loginUser($request, $user);
        }

        return $this->triggerOtpLogin($request, $user);
    }

    protected function loginValidationRules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:4',
        ];
    }

    protected function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    protected function isValidUser($user)
    {
        return $user && $user->acc_status == 1 && $user->disable_account !== 'yes';
    }

    protected function failedLoginResponse($user)
    {
        if (!$user) {
            return redirect()->back()->with('error', 'Sorry, the email address does not exist.');
        }

        if ($user->disable_account === 'yes') {
            return redirect()->back()->with('error', 'Sorry, this account has been disabled. Contact admin.');
        }

        if ($user->acc_status == 0) {
            return redirect()->back()->with('error', 'Sorry, the email address is not verified.');
        }

        return redirect()->back()->with('error', 'Invalid login attempt.');
    }

    protected function isValidPassword($user, $password)
    {
        return Hash::check($password, $user->password);
    }

    protected function isTrustedDevice($user, Request $request)
    {
        $deviceHash = $this->generateDeviceHash($request);

        $trustedDevice = $user->trustedDevices()
            ->where('device_hash', $deviceHash)
            ->where('expires_at', '>=', now())
            ->first();

        // fallback: check cookie
        if (!$trustedDevice && $request->hasCookie('trusted_device')) {
            $cookieHash = $request->cookie('trusted_device');
            $trustedDevice = $user->trustedDevices()
                ->where('device_hash', $cookieHash)
                ->where('expires_at', '>=', now())
                ->first();
        }

        if ($trustedDevice) {
            $trustedDevice->update(['last_used_at' => now()]);
            return true;
        }

        return false;
    }

    protected function loginUser(Request $request, $user)
    {
        $request->session()->put('user_id', $user->user_id);
        $request->session()->put('name', $user->name);

        // Handle "remember device" (does both: OTP skip + stay logged in)
        if ($request->has('remember_device')) {
            // 1. Trusted device (OTP skip)
            $this->storeTrustedDevice($request, $user);
            cookie()->queue(cookie('trusted_device', $this->generateDeviceHash($request), 60 * 24 * 30));

            // 2. Persistent login (stay logged in)
            $token = Str::random(60);

            DB::table('users')->where('user_id', $user->user_id)
                ->update(['remember_token' => hash('sha256', $token)]);

            cookie()->queue(cookie(
                'remember_login',      // name
                $token,                // value
                60 * 24 * 30,          // minutes (30 days)
                '/',                   // path
                null,                  // domain (current host)
                false,                 // secure (true if https)
                true                   // httpOnly
            ));

        }

        // Update login activity
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'last_login_ip' => $this->getIp(),
                'last_login_at' => now(),
            ]);

        return $request->session()->has('url.intended')
            ? redirect($request->session()->get('url.intended'))
            : redirect()->action([UserController::class, 'index']);
    }


    protected function triggerOtpLogin(Request $request, $user)
    {
        $otp = rand(111111, 999999);
        $request->session()->put('acc_id', $user->id);

        $request->session()->put('remember_device', $request->has('remember_device'));

        $this->storeOtp($user, $otp);

        return $this->sendOtpEmail($user, $otp);
    }

    protected function storeOtp($user, $otp)
    {
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update(['otp' => $otp]);
    }

    protected function sendOtpEmail($user, $otp)
    {
        $details = [
            'user_id' => $user->user_id,
            'otp' => $otp,
            'name' => $user->name,
            'ip' => $this->getIp(),
        ];

        try {
            Mail::to($user->email)->send(new OTPMail($details));
            return redirect('/authenticate')->with('success', 'Check your email for OTP to login.');
        } catch (\Throwable $e) {
            return redirect('/login')->with('error', 'Error! OTP could not be sent. Try again or contact admin.');
        }
    }

    protected function generateDeviceHash(Request $request)
    {
        return sha1($request->userAgent()); // no IP, keeps hash stable
    }

    protected function storeTrustedDevice(Request $request, $user)
    {
        $deviceHash = $this->generateDeviceHash($request);

        $user->trustedDevices()->updateOrCreate(
            ['device_hash' => $deviceHash],
            [
                'ip_address' => $this->getIp(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
                'expires_at' => now()->addDays(30),
            ]
        );
    }

    // Auto-login from remember cookie
    public static function autoLoginFromCookie(Request $request)
    {
        if (!$request->session()->has('user_id') && $request->hasCookie('remember_login')) {
            $token = $request->cookie('remember_login');
            $user = User::where('remember_token', hash('sha256', $token))->first();

            if ($user) {
                $request->session()->put('user_id', $user->user_id);
                $request->session()->put('name', $user->name);

                // refresh cookie validity
                cookie()->queue(cookie(
                    'remember_login',      // name
                    $token,                // value
                    60 * 24 * 30,          // minutes (30 days)
                    '/',                   // path
                    null,                  // domain (current host)
                    false,                 // secure (true if https)
                    true                   // httpOnly
                ));

                return $user;
            }
        }
        return null;
    }


    public function authenticate(Request $request)
    {
        $title = "OTP Authentication | " . config('global.site_name');
        

        if ($request->isMethod('POST')) {
            $request->validate([
                'otp' => 'required|numeric|min:4',
            ]);
            
            $user_id = $request->session()->get('acc_id');

            if($user_id ==''){
                return redirect("/login")->with('error','Sorry, Your Session has expired. Refresh');
            }

            $otp = $request->otp;

            $login = User::where('otp', $otp)
                        ->where('id', $user_id)
                        ->first();
            //dd($login);
            if ($login) {
                $request->session()->put('user_id', $login->user_id);
                $request->session()->put('name', $login->name);

                DB::table('users')
                    ->where('user_id', $login->user_id)
                    ->update([
                        'last_login_ip' => $this->getIp(),
                        'last_login_at' => now(),
                    ]);

                // ✅ Use $login instead of refetching
                if ($request->session()->has('remember_device') && $request->session()->get('remember_device')) {
                    $this->storeTrustedDevice($request, $login);
                }

                if ($request->session()->has('previous_url')) {
                    $previous_url = $request->session()->get('previous_url');
                    return redirect($previous_url);
                } else {
                    return redirect()->action([UserController::class, 'index']);
                }
            }else{
                return redirect("/authenticate")->with('error','Opps! You have entered invalid OTP ');
            }
      
            
        }

        if ($request->isMethod('GET')) {
            return view('frontend.account.authenticate', compact('title'));
        }
    }


    public function account_status(Request $request)
    {   
        $title = "Account Status  " . config('global.site_title');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
               
        return view('frontend.account.account-status', compact('title','user'));
    }


    public function resend_otp(Request $request)
    {
         
        $user_id = $request->session()->get('acc_id');
        $login = User::where('id', $user_id)
                        ->first();
        //dd($login);
        $otp = rand(111111,999999);
        $email = $login->email;
        $name = $login->first_name;

        DB::table('users')
            ->where('id', $user_id)
            ->update([
                'otp'=> $otp,
            ]);

        $details = [
            'user_id' => $user_id,
            'otp' => $otp,
            'name' => $name,
            'ip' => $this->getIp(),
        ];

        try {
            Mail::to($email)->send(new NotifyMail($details));
            return redirect("/authenticate")->with('status', ['text'=>'Check your email for OTP to login','type'=>'success']);
        } catch (Throwable $e) {
             return redirect("/")->with('status', ['text'=>'Error!, OTP could not be sent, please try again or contact admin','type'=>'danger']);
        }
      
            
    }

    public function register(Request $request, $id = null)
    {   
        $title = "Create an Account | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        if ($request->isMethod('GET')) {
            return view('frontend.account.register', compact('title','user'));
        }

        if ($request->isMethod('POST')) {

            $request->validate([
                'acc_type' => 'required',
                'address' => 'required',
                'state' => 'required',
                'name' => 'required',
                'phone' => 'required|numeric|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'g-recaptcha-response' => ['required', new ReCaptcha],
            ]);
            
            $email = $request->input('email');
            $token  = Str::random(40);

            User::create([
                'name'=> $request->input('name'),
                'email'=> $request->input('email'),
                'phone'=> $request->input('phone'),
                'acc_type'=> $request->input('acc_type'),
                'address'=> $request->input('address'),
                'city'=> $request->input('city'),
                'state'=> $request->input('state'),
                'token'=> $token,
                'acc_status'=> 0,
                'password'=> Hash::make($request->input('password')),
            ]);

            $details = [
                'user_id' => $email,
                'token' => $token,
                'name' =>  $request->input('name'),
            ];
            
            try {
                Mail::to($email)->send(new RegisterMail($details));
                
                return redirect("login")->with('success', 'Great, you have successfully registered, Please verify your email');

            } catch (Throwable $e) {
                
                 return redirect("register")->with('error', 'Error!, Your account details could not be sent, please contact admin');
            }    
        }
    }


    public function verifyaccount($user_id, $token)
    {       
        $user = User::where('email', $user_id)->first();
        $token2 = $user->token;
        if($token == $token2){
            $post = DB::table('users')
            ->where('email', $user_id)
            ->update([
                'acc_status'=> 1,
            ]);
            return redirect("/login")->with('success','Your Email Is verified, Pease Login!');
        }else{

            return redirect("/login")->with('error','Error!, the token does not match');
        }
 
    }

    

    public function forgot_password(Request $request)
    {
        $title = "Forgot Password" . config('global.site_title');

        if ($request->isMethod('POST')) {
            $request->validate([
                'email' => 'required|email',
            ]);
            
            $email = $request->email;

            $login = User::where('email', $email)
                        ->first();
            if ($login) {
                $name = $login->name;
                $user_id = $login->user_id;
                $token = $login->token;


                $details = [
                    'user_id' => $user_id,
                    'token' => $token,
                    'name' => $name,
                ];

                try {
                    Mail::to($email)->send(new PasswordMail($details));
                    return redirect("login")->with('success','Please check your email for link to change password');

                } catch (Throwable $e) {
                
                    return redirect()->back()->with('error','Sorry!, Email Could not be Sent now, Try again later');
                }
      
            }else{
                return redirect()->back()->with('error','Sorry!, This email does not exit on our system... Please register');

            }
        }

        if ($request->isMethod('GET')) {
            return view('frontend.account.forgot-password', compact('title'));
        }
    }

    public function reset_password(Request $request, $user_id, $token)
    {    
        $title = "Reset Password" . config('global.site_title');
        $user = User::where('user_id', $user_id)->first();
        $token2 = $user->token;

        $post = [
                'user_id' => $user_id,
                'token' => $token,
            ];

        if ($request->isMethod('GET')) {

            if($token == $token2){
                return view('frontend.account.reset-password', compact('title','post'));
            }else{
                return redirect("login")->with('danger','Sorry!, There was an error and token does not match, Please contact admin ');
            }
        }

        if ($request->isMethod('POST')) {
            $request->validate([
                'password' => 'required|min:6|confirmed',
            ]);
            
            $user = DB::table('users')
            ->where('user_id', $user_id)
            ->update([
                'password'=> Hash::make($request->input('password')),
            ]);
      
            return redirect("login")->with('success','Your password was successfully updated, Please Login');
        }
       
    }

    protected function getIp(Request $request = null)
    {
        if ($request) {
            return $request->ip();
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function adminlogin(Request $request)
    {
        $title = "Admin Login - " . config('global.site_name');

        if ($request->isMethod('POST')) {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:4',
            ]);

            $email = $request->email;
            $password = $request->password;

            // Find admin by email
            $admin = Admin::where('email', $email)->first();

            // Check if admin exists and password is correct
            if ($admin && Hash::check($password, $admin->password)) {
                $request->session()->put('admin_id', $admin->id);
                return redirect()->action([AdminController::class, 'index']);
            }

            return redirect("admin")->with('status', [
                'text' => 'Invalid email or password. Please try again.',
                'type' => 'danger'
            ]);
        }

        if ($request->isMethod('GET')) {
            return view('frontend.account.admin', compact('title'));
        }
    }

    public function shipper(Request $request)
{
    $title = "Shipper Login - " . config('global.site_name');

    if ($request->isMethod('POST')) {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:4',
        ]);

        $username = $request->username;
        $password = $request->password;

        $login = Shipping::where('username', $username)
           ->first();
        if ($login) {
            if(Hash::check($password, $login->password)){
                $request->session()->put('ship_id', $login->ship_id); // Using the login object's id

                return redirect()->action([ShipperController::class, 'index']);
            }else{
                return redirect()->back()->with('error', 'Error!, Your credentials are not correct');
            }
        }

        return redirect()->back()->with('error', 'Error!, Your credentials are not correct');
    }

    if ($request->isMethod('GET')) {
        return view('frontend.account.shipper', compact('title'));
    }
}



}
