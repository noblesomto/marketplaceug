<section id="search" class="mt-5 w-full bg-primary h-12 hidden lg:block">
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
               <svg viewBox="0 0 24 24" fill="none" data-title="createAdOutline" stroke="none" role="img" aria-hidden="true" focusable="false" class="shrink-0 fill-current  block align-middle size-5"><path d="M4.65457 10.3114L13.8284 19.4853L19.4853 13.8284L18.7624 13.1056C18.3835 12.7267 18.3931 12.1146 18.7758 11.7395C19.172 11.3513 19.8166 11.3313 20.2087 11.7234L20.8995 12.4142C21.6806 13.1953 21.6806 14.4616 20.8995 15.2427L15.2427 20.8995C14.4616 21.6806 13.1953 21.6806 12.4142 20.8995L3.24035 11.7256C2.78484 11.2701 2.57662 10.6231 2.68099 9.9874L3.55647 4.65491C3.60162 4.37991 3.7319 4.12601 3.92895 3.92895C4.12601 3.7319 4.37991 3.60162 4.65491 3.55647L9.9874 2.68099C10.6231 2.57662 11.2701 2.78484 11.7256 3.24035L12.4934 4.00813C12.8856 4.4003 12.8655 5.04487 12.4773 5.441C12.1023 5.82375 11.4902 5.83334 11.1113 5.45442L10.3114 4.65457L5.45233 5.45233L4.65457 10.3114Z" fill="currentColor"></path><path d="M9.58582 9.58587C10.1716 9.00008 10.1716 8.05033 9.58582 7.46455 9.00003 6.87876 8.05029 6.87876 7.4645 7.46455 6.87871 8.05033 6.87871 9.00008 7.4645 9.58587 8.05029 10.1717 9.00003 10.1717 9.58582 9.58587ZM15.0001 4.99994C15.0001 4.44765 15.4478 3.99994 16.0001 3.99994 16.5523 3.99994 17.0001 4.44765 17.0001 4.99994V6.99994H19.0001C19.5523 6.99994 20.0001 7.44765 20.0001 7.99994 20.0001 8.55222 19.5523 8.99994 19.0001 8.99994H17.0001V10.9999C17.0001 11.5522 16.5523 11.9999 16.0001 11.9999 15.4478 11.9999 15.0001 11.5522 15.0001 10.9999V8.99994H13.0001C12.4478 8.99994 12.0001 8.55222 12.0001 7.99994 12.0001 7.44765 12.4478 6.99994 13.0001 6.99994H15.0001V4.99994Z" fill="currentColor"></path></svg>
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
                        <a class="m block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            Sunday
                        </a>

                        <a class=" block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            Monday
                        </a>

                        <a class="block border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            Tuesday
                        </a>
                    </div>
                    @else

                        <div class="invisible absolute z-50 flex w-30 flex-col bg-gray-100 mt-1 py-1 px-4 text-gray-800 shadow-xl group-hover:visible">

                        <a href="/user/index" class="flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            <span><i class="bi bi-speedometer2"></i></span>
                            <span>Dashboard</span>
                        </a>

                        <a href="/user/my-ads" class="flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            <span><i class="bi bi-badge-ad"></i></span>
                            <span>My Ads</span>
                        </a>

                        <a href="/user/favourites" class="flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            <span><i class="bi bi-heart"></i></span>
                            <span>Wishlists</span>
                        </a>

                        <a href="/user/payment" class="flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            <span><i class="bi bi-box2"></i></span>
                            <span>Purchases</span>
                        </a>

                        <a href="/user/messages" class="flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2 relative">
                            <span><i class="bi bi-envelope"></i></span>
                            <span>Messages</span>
                            <!-- Notification badge -->
                            <div class="unread-badge absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs"
                                 style="display: none;">
                                0
                            </div>
                        </a>

                        <a href="/user/feedbacks" class="flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            <span><i class="bi bi-chat-right-dots"></i></span>
                            <span>Feedback</span>
                        </a>

                        <a href="/user/profile" class="flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            <span><i class="bi bi-person"></i></span>
                            <span>Profile</span>
                        </a>
                        <a href="/user/logout" class=" flex gap-2 border-b border-gray-100 py-1 font-semibold text-gray-500 hover:text-black md:mx-2">
                            <span><i class="bi bi-box-arrow-right"></i></span>
                            <span>Logout</span>
                        </a>
                    </div>

                    @endif
            </div>
            <div class="mt-1">
                @php
                    $count = getUserNotificationCount();
                @endphp
                <a href="/user/notifications">
                    <div class="flex flex-col items-center mx-2 relative">
                        <!-- Notification badge - hidden by default if count is 0 -->
                        @if($count >= 1)
                        <div class="absolute -top-1 -right-1 bg-dark_green text-white rounded-full w-4 h-4 flex items-center justify-center text-xs" >
                            {{ $count }}
                        </div>
                        @endif
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
          </div>
        </div>
    </div>
    
</section>
