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
use App\Models\AdminLoginAttempts;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\NigerianPhoneNumber;
use App\Rules\AllowedName;
use Illuminate\Support\HtmlString;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


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
            return redirect()->back()->with('error', new HtmlString(
                'Sorry, the email address is not verified. ' .
                '<a href="'.route('activation.resend', ['email' => $user->email]).'" class="text-blue-600 underline">Resend activation email</a>'
            ));
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
        // ✅ FIXED: Set session with longer lifetime
        $request->session()->put('user_id', $user->user_id);
        $request->session()->put('name', $user->name);

        // ✅ FIXED: Extend session lifetime to 7 days if user wants to stay logged in
        if ($request->has('remember_device')) {
            config(['session.lifetime' => 10080]); // 7 days in minutes
        }

        // Handle "remember device" (does both: OTP skip + stay logged in)
        if ($request->has('remember_device')) {
            // 1. Trusted device (OTP skip) - extended to 90 days
            $this->storeTrustedDevice($request, $user);

            // ✅ FIXED: Determine if HTTPS is being used
            $isSecure = $request->secure();

            cookie()->queue(cookie(
                'trusted_device',
                $this->generateDeviceHash($request),
                60 * 24 * 90,  // ✅ CHANGED: 90 days instead of 30
                '/',
                null,
                $isSecure,     // ✅ FIXED: Only true on HTTPS
                true,          // httpOnly
                false,         // raw
                'Lax'          // sameSite
            ));

            // 2. Persistent login (stay logged in)
            $token = Str::random(60);
            DB::table('users')->where('user_id', $user->user_id)
                ->update(['remember_token' => hash('sha256', $token)]);

            cookie()->queue(cookie(
                'remember_login',
                $token,
                60 * 24 * 90,         // ✅ CHANGED: 90 days instead of 30
                '/',
                null,
                $isSecure,            // ✅ FIXED: Only true on HTTPS
                true,                 // httpOnly
                false,                // raw
                'Lax'                 // sameSite
            ));
        }

        // Update login activity
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'last_login_ip' => $this->getIp(),
                'last_login_at' => now(),
            ]);

        // Redirect to intended URL or default profile page
        return redirect()->intended(action([UserProfile::class, 'profile']));
    }

    protected function triggerOtpLogin(Request $request, $user)
    {
        $otp = rand(111111, 999999);
        $request->session()->put('acc_id', $user->id);

        // ✅ ADDED: Flag to prevent auto-login during OTP verification
        $request->session()->put('otp_pending', true);

        // ✅ SECURITY: Store IP address for validation during OTP verification
        $request->session()->put('otp_ip', $request->ip());

        $request->session()->put('remember_device', $request->has('remember_device'));

        $this->storeOtp($user, $otp);

        // ✅ CRITICAL FIX: Clear any existing remember_login cookie to prevent OTP bypass
        // This ensures the auto-login middleware won't log them in while waiting for OTP
        cookie()->queue(cookie()->forget('remember_login'));

        return $this->sendOtpEmail($user, $otp);
    }

    protected function storeOtp($user, $otp)
    {
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10), // ✅ SECURITY: OTP expires in 10 minutes
            ]);
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
            // ✅ ADDED: Log the error for debugging
            Log::error('OTP Email Failed', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
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
                'expires_at' => now()->addDays(90), // ✅ CHANGED: 90 days instead of 30
            ]
        );
    }

    // ✅ IMPROVED: Auto-login from remember cookie with better security
    public static function autoLoginFromCookie(Request $request)
    {
        // Skip if already logged in
        if ($request->session()->has('user_id')) {
            return null;
        }

        // ✅ ADDED: Skip if waiting for OTP verification
        if ($request->session()->has('acc_id') || $request->session()->has('otp_pending')) {
            return null;
        }

        if ($request->hasCookie('remember_login')) {
            $token = $request->cookie('remember_login');
            $user = User::where('remember_token', hash('sha256', $token))
                ->where('acc_status', 1)
                ->where(function($query) {
                    $query->whereNull('disable_account')
                          ->orWhere('disable_account', '!=', 'yes');
                })
                ->first();

            if ($user) {
                $request->session()->put('user_id', $user->user_id);
                $request->session()->put('name', $user->name);

                // ✅ FIXED: Set longer session lifetime
                config(['session.lifetime' => 10080]); // 7 days

                // Update last login
                DB::table('users')
                    ->where('user_id', $user->user_id)
                    ->update([
                        'last_login_at' => now(),
                        'last_login_ip' => $request->ip(),
                    ]);

                // ✅ FIXED: Refresh cookie with proper secure flag
                $isSecure = $request->secure();
                cookie()->queue(cookie(
                    'remember_login',
                    $token,
                    60 * 24 * 90,         // 90 days
                    '/',
                    null,
                    $isSecure,            // Dynamic based on HTTPS
                    true,                 // httpOnly
                    false,                // raw
                    'Lax'                 // sameSite
                ));

                return $user;
            } else {
                // ✅ ADDED: Clear invalid cookie
                cookie()->queue(cookie()->forget('remember_login'));
            }
        }
        return null;
    }


    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Login failed, please try again.');
        }

        // Try to find user by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            // If no user exists, create new one (status active by default)
            $user = User::create([
                'name'       => $socialUser->getName() ?? $socialUser->getNickname(),
                'email'      => $socialUser->getEmail(),
                $provider . '_id' => $socialUser->getId(),
                'acc_status' => 1, // mark verified
                'acc_type'=> "Private",
                'password'   => bcrypt(Str::random(16)), // random password
            ]);
        } else {
            // Update provider ID if missing
            if (!$user->{$provider . '_id'}) {
                $user->update([
                    $provider . '_id' => $socialUser->getId(),
                ]);
            }
        }

        // Use your existing login process (skip password + OTP since provider is trusted)
        return $this->loginSocialUser($user);
    }

    protected function loginSocialUser($user)
    {
        // Put the same session values as normal login
        session()->put('user_id', $user->user_id);
        session()->put('name', $user->name);

        // ✅ ADDED: Set longer session lifetime for social login too
        config(['session.lifetime' => 10080]); // 7 days

        // Update login activity
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'last_login_ip' => $this->getIp(),
                'last_login_at' => now(),
            ]);

        return session()->has('url.intended')
            ? redirect(session()->get('url.intended'))
            : redirect()->action([UserProfile::class, 'profile']);
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

            // ✅ SECURITY 1: IP Address Validation - verify IP hasn't changed
            $otpIp = $request->session()->get('otp_ip');
            if ($otpIp && $otpIp !== $request->ip()) {
                $request->session()->forget(['acc_id', 'otp_pending', 'otp_ip']);
                return redirect('/login')->with('error', 'Security error: Your IP address changed. Please login again.');
            }

            // ✅ SECURITY 2: Rate Limit OTP Attempts (max 5 attempts)
            $cacheKey = 'otp_attempts:' . $user_id;
            $attempts = Cache::get($cacheKey, 0);

            if ($attempts >= 5) {
                return redirect("/authenticate")->with('error', 'Too many failed attempts. Please request a new OTP.');
            }

            $otp = $request->otp;

            // ✅ SECURITY 3: Check OTP expiration
            $login = User::where('otp', $otp)
                        ->where('id', $user_id)
                        ->where('otp_expires_at', '>=', now()) // Check expiration
                        ->first();

            if ($login) {
                $request->session()->put('user_id', $login->user_id);
                $request->session()->put('name', $login->name);

                // ✅ Clear OTP pending flag after successful verification
                $request->session()->forget('otp_pending');
                $request->session()->forget('acc_id');
                $request->session()->forget('otp_ip');

                // ✅ SECURITY 4: Clear OTP from database after successful use
                DB::table('users')
                    ->where('user_id', $login->user_id)
                    ->update([
                        'otp' => null,
                        'otp_expires_at' => null,
                        'last_login_ip' => $this->getIp(),
                        'last_login_at' => now(),
                    ]);

                // ✅ Clear failed attempt counter on success
                Cache::forget($cacheKey);

                // ✅ Set longer session lifetime
                if ($request->session()->has('remember_device') && $request->session()->get('remember_device')) {
                    config(['session.lifetime' => 10080]); // 7 days
                }

                // ✅ Store trusted device and set cookies after OTP verification
                if ($request->session()->has('remember_device') && $request->session()->get('remember_device')) {
                    $this->storeTrustedDevice($request, $login);

                    $isSecure = $request->secure();

                    // Set trusted device cookie
                    cookie()->queue(cookie(
                        'trusted_device',
                        $this->generateDeviceHash($request),
                        60 * 24 * 90,
                        '/',
                        null,
                        $isSecure,
                        true,
                        false,
                        'Lax'
                    ));

                    // Set remember login cookie
                    $token = Str::random(60);
                    DB::table('users')->where('user_id', $login->user_id)
                        ->update(['remember_token' => hash('sha256', $token)]);

                    cookie()->queue(cookie(
                        'remember_login',
                        $token,
                        60 * 24 * 90,
                        '/',
                        null,
                        $isSecure,
                        true,
                        false,
                        'Lax'
                    ));
                }

                if ($request->session()->has('previous_url')) {
                    $previous_url = $request->session()->get('previous_url');
                    return redirect($previous_url);
                } else {
                    return redirect()->action([UserProfile::class, 'profile']);
                }
            } else {
                // ✅ SECURITY 2: Increment failed attempt counter
                Cache::put($cacheKey, $attempts + 1, now()->addHour()); // 1 hour expiry

                $remainingAttempts = 5 - $attempts - 1;

                if ($remainingAttempts > 0) {
                    return redirect("/authenticate")->with('error', 'Invalid or expired OTP. ' . $remainingAttempts . ' attempt(s) remaining.');
                } else {
                    return redirect("/authenticate")->with('error', 'Too many failed attempts. Please request a new OTP.');
                }
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

        if (!$user_id) {
            return redirect("/login")->with('error', 'Session expired. Please login again.');
        }

        // ✅ SECURITY: Rate limit OTP resend requests (max 3 per hour)
        $rateLimitKey = 'resend-otp:' . $user_id;

        if (Cache::has($rateLimitKey) && Cache::get($rateLimitKey) >= 3) {
            return redirect("/authenticate")->with('error', 'Too many OTP requests. Please try again in 1 hour.');
        }

        $login = User::where('id', $user_id)->first();

        if (!$login) {
            return redirect("/login")->with('error', 'User not found. Please login again.');
        }

        $otp = rand(111111, 999999);
        $email = $login->email;
        $name = $login->name;
        DB::table('users')
            ->where('id', $user_id)
            ->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(15), // ✅ SECURITY: OTP expires in 10 minutes
            ]);

        // ✅ SECURITY: Clear failed OTP attempts when new OTP is sent
        Cache::forget('otp_attempts:' . $user_id);

        // ✅ SECURITY: Increment resend counter
        $currentCount = Cache::get($rateLimitKey, 0);
        Cache::put($rateLimitKey, $currentCount + 1, now()->addHour());

        $details = [
            'user_id' => $user_id,
            'otp' => $otp,
            'name' => $name,
            'ip' => $this->getIp(),
        ];

        try {
            Mail::to($email)->send(new OTPMail($details));
            return redirect("/authenticate")->with('success', 'New OTP sent! Check your email. Code expires in 10 minutes.');
        } catch (\Throwable $e) {
            Log::error('Resend OTP failed', [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);
            return redirect("/authenticate")->with('error', 'Error! OTP could not be sent. Please try again or contact admin.');
        }
    }

    public function register(Request $request, $id = null)
    {
        $title = "Create an Account | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        if ($request->isMethod('GET')) {
            return view('frontend.account.register', compact('title', 'user'));
        }

        if ($request->isMethod('POST')) {
            try {
                $validatedData = $request->validate([
                    'acc_type' => 'required',
                    'name' => ['required', 'string', 'max:100', new AllowedName],
                    'phone' => [
                        'required',
                        new NigerianPhoneNumber(),
                    ],
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:6',
                    'g-recaptcha-response' => 'required',
                ], [
                    'g-recaptcha-response.required' => 'Security verification is required.',
                ]);

                // Verify reCAPTCHA v3
                $recaptchaResponse = $request->input('g-recaptcha-response');
                $recaptchaSecret = config('services.recaptcha.secret_key');

                $verifyResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $recaptchaSecret,
                    'response' => $recaptchaResponse,
                    'remoteip' => $request->ip()
                ]);

                $recaptchaData = $verifyResponse->json();

                // Check if verification was successful and score is acceptable
                if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
                    Log::warning('reCAPTCHA verification failed', [
                        'email' => $validatedData['email'],
                        'score' => $recaptchaData['score'] ?? 'N/A',
                        'ip' => $request->ip(),
                        'error_codes' => $recaptchaData['error-codes'] ?? []
                    ]);

                    return redirect()->back()
                        ->withInput($request->except('password'))
                        ->withErrors(['g-recaptcha-response' => 'Security verification failed. Please try again.']);
                }

            } catch (ValidationException $e) {
                throw $e;
            }

            $email = $validatedData['email'];
            $token = Str::random(40);

            DB::beginTransaction();
            try {
                $user = User::create([
                    'name' => ContentHelper::sanitizeName($validatedData['name']),
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'acc_type' => $validatedData['acc_type'],
                    'token' => $token,
                    'acc_status' => 0,
                    'password' => Hash::make($validatedData['password']),
                ]);

                $details = [
                    'user_id' => $email,
                    'token' => $token,
                    'name' => $validatedData['name'],
                ];

                Mail::to($email)->queue(new RegisterMail($details));

                DB::commit();

                return redirect("login")->with([
                    'success' => 'Great, you have successfully registered. Check your email to activate your account. Please also check your spam folder',
                    'resend_email' => $email
                ]);

            } catch (\Exception $e) {
                DB::rollBack();

                // Better error logging and user feedback
                Log::error('Registration failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'ip' => $request->ip()
                ]);

                // Check if it's an email sending error
                if ($e instanceof \Swift_TransportException ||
                    stripos($e->getMessage(), 'mail') !== false) {
                    return redirect("register")
                        ->withInput($request->except('password'))
                        ->with('error', 'Your account was created but the activation email could not be sent. Please contact admin.');
                }

                return redirect("register")
                    ->withInput($request->except('password'))
                    ->with('error', 'Registration failed. Please try again or contact admin if the problem persists.');
            }
        }
    }




    public function resend_email(Request $request)
    {
        $email = $request->query('email');

        $user = User::where('email', $email)->where('acc_status', 0)->first();

        if (!$user) {
            return redirect('login')->with('error', 'Invalid or already verified account.');
        }

        $token = Str::random(40);
        $user->update(['token' => $token]);

        $details = [
            'user_id' => $user->email,
            'token'   => $token,
            'name'    => $user->name,
        ];

        try {

            Mail::to($user->email)->queue(new RegisterMail($details));

            return redirect('login')->with('success', 'We have resent your activation email. Please check your inbox and spam folder');
        } catch (\Exception $e) {
            Log::error('Resend email failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return redirect('login')->with('error', 'Failed to resend activation email. Please try again or contact admin.');
        }
    }

    public function verifyaccount($user_id, $token)
    {
        $user = User::where('email', $user_id)->first();

        if (!$user) {
            return redirect("/login")->with('error','Invalid verification link');
        }

        $token2 = $user->token;

        if($token == $token2){
            $post = DB::table('users')
            ->where('email', $user_id)
            ->update([
                'acc_status'=> 1,
                'token' => null,
            ]);

            return redirect("/login")->with('success','Your Email Is verified, Please Login!');
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
            $login = User::where('email', $email)->first();

            if ($login) {
                $name = $login->name;
                $user_id = $login->user_id;
                $token = Str::random(40);


                // Save the token to the database
                DB::table('users')
                    ->where('user_id', $user_id)
                    ->update([
                        'token' => $token
                    ]);

                $details = [
                    'user_id' => $user_id,
                    'token' => $token,
                    'name' => $name,
                ];

                try {
                    Mail::to($email)->send(new PasswordMail($details));
                    return redirect("login")->with('success', 'Please check your email for link to change password');
                } catch (Throwable $e) {
                    return redirect()->back()->with('error', 'Sorry!, Email could not be sent now. Try again later');
                }
            } else {
                return redirect()->back()->with('error', 'Sorry!, This email does not exist on our system... Please register');
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

        //dd($user);
        if ($request->isMethod('GET')) {

            if($token == $token2){
                return view('frontend.account.reset-password', compact('title','post'));
            }else{
                return redirect("login")->with('error','Sorry!, There was an error and token does not match, Please contact admin ');
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
                'token' => null,
            ]);

            return redirect("login")->with('success','Your password was successfully updated, Please Login');
        }

    }

    protected function getIp(?Request $request = null)
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




    public function adminlogin2(Request $request)
{
    $title = "Admin Login - " . config('global.site_name');

    // Handle Login POST
    if ($request->isMethod('POST')) {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {

            // Store admin ID in session
            $request->session()->put('admin_id', $admin->id);

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . $admin->username);
        }

        return back()->with('error', 'Invalid email or password.');
    }

    // GET: Show Login Form
    return view('frontend.account.admin', compact('title'));
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
                $request->session()->put('ship_id', $login->ship_id);

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
