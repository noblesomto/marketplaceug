@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-4/5 lg:w-3/5 mx-auto mt-4 px-2 sm:px-0">
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">

        <div class="flex justify-between">
            <h1 class="text-xl font-semibold text-dark_green">Messages</h1>
            <div class="relative dropdown inline-block">
              <button
                type="button"
                class="dropdown-button inline-flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                aria-haspopup="true"
                aria-expanded="false"
              >
                <i class="bi bi-three-dots text-xl text-gray-600"></i>
              </button>

              <div class="dropdown-menu hidden absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                <div class="py-1">
                  <a
                    href="/user/archived-messages"
                    class="flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-900 transition-colors duration-150"
                  >
                    Archived Messages
                  </a>

                </div>
              </div>
            </div>
        </div>
        @include('public.components.flash-message')
    </div>
    
    <div class="flex flex-col lg:flex-row gap-4 pb-20">
        <!-- Messages List -->
        <div class="w-full lg:w-2/3 bg-white rounded-lg shadow-sm overflow-hidden">
            @if (!$conversations->isEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach($conversations as $conversation)
                        <a href="{{ route('chat.show', ['advertId' => $conversation['advert']->id, 'receiverId' => $conversation['other_user']->user_id]) }}" 
                           class="block hover:bg-gray-50 transition-colors duration-150">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                                <img class="w-10 h-10 rounded-full object-cover" src="{{ $conversation['other_user']->profile_thumbnail_url }}" alt="{{ $conversation['other_user']->name }}">
                                        </div>
                                        <div>
                                            <h3 class="text-base font-medium text-gray-900">{{ $conversation['other_user']->name }}</h3>
                                            <p class="text-sm text-gray-500 truncate max-w-[240px] sm:max-w-md">{{ $conversation['advert']->ad_title }}</p>
                                        </div>
                                    </div>
                                    @if($conversation['unread_count'] > 0)
                                        <span class="bg-dark_green text-white text-xs font-medium px-2.5 py-1 rounded-full">
                                            {{ $conversation['unread_count'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center">
                    <div class="mx-auto w-24 h-24 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No messages yet</h3>
                    <p class="mt-1 text-gray-500">Your conversations will appear here when you start messaging.</p>
                </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div class="w-full lg:w-1/3 hidden xl:block">
            @include('user.components.user-sidebar')
        </div>
    </div>
</section>

@include('user.layouts.footer')
<script>
(function () {
  // Toggle the clicked dropdown; close others
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.dropdown-button');
    const anyDropdown = e.target.closest('.dropdown');

    // If a button was clicked
    if (btn && anyDropdown) {
      const thisMenu = anyDropdown.querySelector('.dropdown-menu');
      const willOpen = thisMenu.classList.contains('hidden');

      // Close all first
      document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));

      // Then toggle this one
      if (willOpen) {
        thisMenu.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
      } else {
        thisMenu.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
      }
      return;
    }

    // Clicked outside any dropdown -> close all
    if (!anyDropdown) {
      document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
      document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(b => b.setAttribute('aria-expanded','false'));
    }
  });

  // Escape key closes all
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
      document.querySelectorAll('.dropdown-button').forEach(b => b.setAttribute('aria-expanded','false'));
    }
  });
})();
</script>
