@include('frontend.layouts.header')

<div class="bg-white  pb-20">
  <section class="w-full flex justify-center items-center h-24 border-b-2 border-b-gray-400 shadow-lg shadow-b-2.5 shadow-gray-300">
    <a href="/"><img src="{{ asset('frontend/images/logo.png') }}" class="h-9" alt="Marketplace NG Logo"></a>
  </section>

  <section class="w-full lg:w-2/6 mx-auto bg-white p-6 lg:p-10 mt-1 lg:mt-10 rounded-[20px] shadow-lg">
    <div class="w-full mx-auto">
      @include('frontend.components.flash-message')

      <div class="mb-5">
        <h2 class="font-bold text-lg lg:text-xl mt-5 w-full">Welcome to Marketplace Naija</h2>
        <p class="text-sm lg:text-base mt-3">Log in to find and sell new and used treasures</p>
      </div>

      <!-- Social Login Buttons -->
      <div class="mb-6 space-y-3 hidden">
        <a href="/auth/google" class="w-full bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition duration-200 ease-in-out shadow-sm hover:shadow-md">
          <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continue with Google
        </a>

        <a href="/auth/facebook" class="w-full bg-[#1877F2] hover:bg-[#166FE5] text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition duration-200 ease-in-out shadow-sm hover:shadow-md">
          <svg class="w-5 h-5 mr-3 fill-current" viewBox="0 0 24 24">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
          </svg>
          Continue with Facebook
        </a>
      </div>


      <div class="flex items-center my-6 hidden">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="flex-shrink mx-4 text-gray-600 text-sm">or continue with email</span>
        <div class="flex-grow border-t border-gray-300"></div>
      </div>
      <!-- Divider -->
      <form method="POST" action="/login">
        @csrf

        <div class="mb-6 mt-3 lg:mt-10">
          @if ($errors->has('email'))
            <span class="text-red-900 my-1">{{ $errors->first('email') }}</span>
          @endif
          <label class="block mb-2">Email *</label>
          <input type="email" id="email" name="email" placeholder="Enter your email"
                 class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base md:text-lg"
                 value="{{ old('email') }}" required>
        </div>

        <div class="mb-4 relative">
          @if ($errors->has('password'))
            <span class="text-red-900 my-1">{{ $errors->first('password') }}</span>
          @endif
          <label class="block mb-2">Password *</label>
          <input type="password" id="password" name="password" placeholder="Enter your password"
                 class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base md:text-lg"
                 value="{{ old('password') }}" required>

          <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 mt-6" aria-label="Toggle password visibility">
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 text-base lg:text-lg mt-2">
          <label class="inline-flex items-center mb-3">
                <input type="checkbox" name="remember_device" class="form-checkbox text-indigo-600">
                <span class="ml-2 text-sm">Remember this device for faster login</span>
            </label>
          <div><a class="underline" href="/forgot-password">Forgot Password</a></div>
        </div>

        <div class="mt-8">
          <button type="submit" class="w-full bg-secondary-200 hover:bg-secondary-100 text-lg text-dark_green font-black py-2 px-2 rounded-full flex justify-center items-center">
            <span>Login</span>
            <span class="ml-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
              </svg>
            </span>
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 text-base lg:text-lg mt-4">
          <div>Not registered yet?</div>
          <div><a class="font-black underline" href="/register">Create an account</a></div>
        </div>
      </form>
    </div>
  </section>

</div>

<script>
  function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      eyeIcon.innerHTML = '<path fill-rule="evenodd" d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm-7.5-5a8.24 8.24 0 017.5-5 8.24 8.24 0 017.5 5 8.24 8.24 0 01-7.5 5 8.24 8.24 0 01-7.5-5z" clip-rule="evenodd"/>';
    } else {
      passwordInput.type = 'password';
      eyeIcon.innerHTML = '<path d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>';
    }
  }

  // Prevent zooming on input focus in mobile devices
  document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input[type="email"], input[type="password"], input[type="text"]');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.style.fontSize = '16px';
      });
    });
  });
</script>

@include('frontend.layouts.footer')
