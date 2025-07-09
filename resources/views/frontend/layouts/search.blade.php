<section class="mt-5 w-full bg-primary h-12 hidden lg:block">
    <div class="w-4/6 mx-auto">
        <form action="/search" method="POST" class="">
            @csrf
        <div class="grid grid-cols-7 gap-2">
          <div class="col-span-3 mt-2">
              <div class="flex flex-wrap  mb-2">
                <div class="w-ful md:w-2/4 mb-6 md:mb-0">
                  <input class="appearance-none block w-full h-8 text-gray-700 border border-gray-200 rounded py-1 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500 rounded-l-lg" id="grid-city" type="text" name="product" placeholder="What are you looking for?" required>
                </div>
                <div class="w-full md:w-2/4 mb-6 md:mb-0">
                  <div class="relative">
                    <select name="category" class="block appearance-none w-full h-8 bg-gray-200 border border-gray-200 text-gray-700 py-1 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-gray-200 focus:border-gray-500 rounded-r-lg text-sm" id="grid-state" required>
                            <option value="">Select Category</option>
                        @foreach(getCategories() as $category)
                            <option value="{{ $category->id }}">{{ $category->category }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                      <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                  </div>
                </div>
            </div>
          </div>
          <div class="col-span-1 mt-2">
              <div class="flex flex-wrap  mb-2">
              
                <div class="w-full mb-6 md:mb-0">
                  <div class="relative">
                    @php
                        $states = [
                            'Abia', 'Adamawa', 'AkwaIbom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno', 'Cross River',
                            'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT', 'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano',
                            'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo',
                            'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara'
                        ];
                    @endphp

                    <select name="location" class="block appearance-none w-full h-8 bg-gray-200 border border-gray-200 text-gray-700 py-1 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-gray-200 focus:border-gray-500 text-sm" required>
                        <option value="" selected="selected">Select Location</option>
                        @foreach ($states as $state)
                            <option value="{{ $state }}">{{ $state }}</option>
                        @endforeach
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                      <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                  </div>
                </div>
            </div>
          </div>
          <div class="mt-3">
              <button class="bg-dark_green hover:bg-white hover:text-dark_green font-semibold text-white w-28 py-1 px-2 rounded-full text-sm">
                  <i class="fa fa-search"></i> <span class="px-1">Search</span>
                </button>
          </div>

          </form>
          <div class="col-span-2 mt-2 text-sm flex justify-start">
            <a href="/user/post-ad" class="font-semibold  px-3 py-2  mx-2 text-dark_green hover:bg-white flex justify-between">
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 hover:fill-dark_green">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
                <span class="mx-2">Place an ad </span>
            </a> 
             <div class="h-6 mx-1 mt-2 border-r border-dark_green"></div>
                <div class="group cursor-pointer py-2 -mt-2">
                        <a class="menu-hover px-2 py-2 text-sm font-semibold text-dark_green hover:bg-white lg:mx-1 flex justify-between" onClick="">
                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 hover:size-6 hover:fill-dark_green">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                             <span class="mx-2">Mine</span>
                        </a>

                    @if(session()->get('user_id') =='')
                    <div class="invisible absolute z-50 flex w-30 flex-col bg-gray-100 mt-1 py-1 px-4 text-gray-800 shadow-xl group-hover:visible">
                        <a href="/about-us" class="m block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            About Us
                        </a>

                        <a href="/faq" class=" block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            FAQ
                        </a>

                        <a href="/contact-us" class="block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            Contact Us
                        </a>
                    </div>
                    @else

                        <div class="invisible absolute z-50 flex w-30 flex-col bg-gray-100 mt-1 py-1 px-4 text-gray-800 shadow-xl group-hover:visible">

                        <a href="/user/index" class="m block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            Dashboard
                        </a>

                        <a href="/user/my-ads" class="m block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            My Ads
                        </a>

                        <a href="/user/payment" class="m block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            Payments
                        </a>

                        <a href="/user/messages" class="block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2 relative">
                            Messages
                            <!-- Notification badge -->
                            <div class="unread-badge absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs" 
                                 style="display: none;">
                                0
                            </div>
                        </a>

                        <a href="/user/profile" class="block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            Profile 
                        </a>
                        <a href="/user/logout" class=" block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            Logout
                        </a>
                    </div>

                    @endif
            </div>
          </div>
        </div>
    </div>
    
</section>