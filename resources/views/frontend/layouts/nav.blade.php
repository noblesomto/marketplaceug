
<section class="w-full max-w-7xl mx-auto px-2 h-12 py-2 mt-4  hidden lg:block">
    <div class="text-sm">
        <div class="grid grid-cols-6 gap-1 md:gap-3">
          <div class="col-span-3 md:col-span-4 flex items-center">
              <a href="/"><img src="{{ asset('frontend/images/logo.png') }}" class="h-9"></a>
          </div>
          
          <div class="col-span-3 md:col-span-2 flex items-center justify-end space-x-2">
            @if(session()->get('user_id') =='')
            <a href="/user/post-ad"
                class="font-semibold px-3 text-dark_green  py-2 rounded-lg transition-colors duration-200 flex items-center text-base whitespace-nowrap mr-5 space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="hidden xl:inline">Sell Item</span>
            </a>
            @else
              <a href="/user/post-ad"
                class="font-semibold px-3 bg-secondary_dark text-white  py-2 rounded-lg transition-colors duration-200 flex items-center text-sm whitespace-nowrap mr-5 space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="hidden xl:inline">Sell Item</span>
            </a>
            @endif
            @if(session()->get('user_id') =='')
            <a href="/register" class="btn btn-primary py-1">
                  Register
            </a>
            <span class="px-2">or</span>
            <a href="/login" class="btn btn-secondary py-1 flex items-center">
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
              </span> <span class="mx-1">Login</span>
            </a>
             @endif
          </div>
         
        </div>
    </div>
</section>

