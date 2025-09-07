<section id="nav-bar" class="w-full md:w-4/6 mx-auto h-12 py-2 mt-4  hidden lg:block">
    <div class="text-sm">
        <div class="grid grid-cols-6 gap-1 md:gap-3">
          <div class="col-span-3 md:col-span-4 flex items-center">
              <a href="/"><img src="{{ asset('frontend/images/logo.png') }}" class="h-9"></a>
          </div>
          @if(session()->get('user_id') =='')
          <div class="col-span-3 md:col-span-2 flex items-center">
            <a href="/register" class="bg-transparent hover:bg-primary text-dark_green font-semibold hover:text-dark_green  py-1 px-2 border-2 border-dark_green hover:border-dark_green rounded-full">
                  Register
            </a>
            <span class="px-2">or</span>
            <a href="/login" class="bg-secondary-200 hover:bg-secondary-100 text-dark_green font-bold py-1 px-2 rounded-full flex justify-around">
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
              </span>
              <span class="mx-1">Login</span>
            </a>

          </div>
          @endif
        </div>
    </div>
</section>

