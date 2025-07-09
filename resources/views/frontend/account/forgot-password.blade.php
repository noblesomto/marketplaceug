@include('frontend.layouts.header')

<body style="background-color: #666666;">
    
    <div class="limiter ">
        <div class="container-login100">
            <div class="wrap-login100">

                
                <form class="register-form validate-form" action="/forgot-password" method="POST" enctype="">
                    @csrf
                        
                    <span class="login100-form-title p-b-43">
                        <div class="row m-t-40 m-b-20 ">
                            <div class="col-lg-8 offset-2 text-center m-l-r-auto">
                                <a href="/"><img src="{{ asset('assets/images/resources/logo-2.png') }}"></a>
                            </div>
                        </div>
                        Forgot Password
                        <h6>
                            @if(session('status'))
                                <div class="alert alert-{{session('status')['type']}}">
                                    {{session('status')['text']}}
                                </div>
                            @endif
                        </h6>
                    </span>
                    
                    <div class="wrap-input100 validate-input" data-validate = "Email Address is Required">
                        @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                        <input class="input100" type="text" name="email" value="{{ old('email') }}">
                        <span class="focus-input100"></span>
                        <span class="label-input100">Email Address</span>
                    </div>

                    
                    
                    


                    <div class="container-login100-form-btn p-t-20">
                        <button class="login100-form-btn">
                            Submit
                        </button>
                    </div>
                    
                    <div class="text-center p-t-46 p-b-20">
                        <span class="txt2">
                            Don't have an account? <br> <a href="/register">Create Account Here</a>
                        </span>
                    </div>

                    
                </form>

                <div class="login100-more" style="background-image: url('{{ asset('frontend/images/bg.jpg')}} ')  ;">
                </div>
            </div>
        </div>
    </div>
    
    
@include('frontend.layouts.footer')
    
    
