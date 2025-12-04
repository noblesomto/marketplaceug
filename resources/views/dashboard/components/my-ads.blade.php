<div class="pb-10 mb-10">
    @if (!$ads->isEmpty())
      @foreach ($ads as $row)
          <div class="bg-white mb-2 border-b border-b-gray-300 shadow p-2 mb-1">
             <div class="flex w-full">
                  <div class="w-2/6 mr-3 relative bg-gray-50 h-24 lg:h-40 overflow-hidden">
                    @if($row->hasMedia('images'))
                        <img src="{{ $row->getFirstMediaUrl('images', 'thumbnail') }}"
                             alt="{{ $row->ad_title ?? 'Image' }}"
                             class="w-full h-full object-cover">
                    @else
                        <img src="{{ asset('frontend/images/default.png') }}"
                             alt="Default image"
                             class="w-full h-full object-cover">
                    @endif
                    <div class="absolute bottom-3 right-3 bg-black w-6 h-5 text-xs text-white flex justify-center items-center">{{ $row->getMedia('images')->count() }}</div>
                  </div>
                  <div class="w-4/6 relative space-y-2">
                    <div class="flex justify-between items-center text-xs">
                      <div class="flex justify-start items-center text-sm md:mr-5">
                        <span class="mr-3 hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        </span>
                        <div>
                          <span class="text-xs">{{ $row->state }}</span>
                        </div>
                        </div>

                      <div>
                        <div class="flex justify-start mr-5 text-xs md:mt-2">
                          <span class="mr-3 hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        </span>  <span class="text-xs" >{{ date('d.m.Y', strtotime($row->created_at)) }}</span></div>
                      </div>
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
                                href="javascript:void(0)"
                                data-delete-url="/user/delete-ad/{{ $row->id }}"
                                class="delete-ad-btn flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-900 transition-colors duration-150"
                              >
                                <i class="bi bi-trash mr-3 text-red-400"></i> Delete Ad
                              </a>

                            </div>
                          </div>
                        </div>
                    </div>
                    <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}">
                        <div class="font-medium leading-5 md:font-bold text-base md:text-xl md:mt-2"> {{ Str::limit($row->ad_title, 50) }}</div>
                    </a>
                    <div class="text-sm mt-2 hidden lg:block text-gray-600">
                        {!! Str::limit(strip_tags($row->description), 80) !!}

                    </div>
                    @if($row->category==3)
                    <div class="text-dark_green font-bold text-base my-2">
                        {{ $row->salary }}
                    </div>
                    @elseif($row->category==18)
                        <div class="text-dark_green font-bold text-base my-2">
                            {{ $row->expected_salary }}
                        </div>
                    @elseif($row->contact_price=="yes")
                        <div class="text-dark_green font-bold text-base my-2">
                            Contact For Price
                        </div>
                    @else
                    <div class="flex justify-start text-dark_green font-bold text-base my-2">
                      <div class="mr-4">₦ {{ number_format($row->price, 0, '.', ',') }} </div>
                      <div>{{ $row->price_type }}</div>
                    </div>
                    @endif

                    @if(!in_array($row->category, [1, 3, 11, 18]))
                    <div class="mt-2">
                        <div class="flex justify-between text-sm ">
                          @if($row->shipment=="Ship")
                          <span class="bg-gray-100 p-1 mr-2">Shipping Possible</span>
                          @endif
                        </div>
                        @if($row->sold=="Yes")
                        <div class="p-1 mr-2 flex items-center gap-4">
                            <span>View:</span>
                            <div class="bg-gray-50 inline-block p-1">
                                <span><i class="bi bi-truck"></i></span>
                                <span><a title="View & Update Shipping Status" href="/user/ad-shipping/{{ $row->id }}">Shipping Status</a></span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                  </div>
              </div>
              <div class="w-full">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-4">
                       @if($row->sold == "Yes")
                            <span class="flex items-center gap-2 bg-red-100 text-red-800 p-1 rounded cursor-not-allowed" title="This advert is already sold">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </span>
                                <span>Sold</span>
                            </span>

                            <a class="flex items-center gap-2 bg-gray-100 p-1 rounded cursor-not-allowed" >
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </span>
                            <span>Edit Ad</span>
                        </a>
                        @if($row->featured=="Yes")
                            <a class="flex items-center gap-2 bg-green-300 p-1 rounded cursor-not-allowed" >
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                    </svg>
                                </span>
                                <span>Ad Boosted</span>
                        </a>
                        @else
                            <a class="flex items-center gap-2 bg-gray-100 p-1 rounded cursor-not-allowed" >
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                    </svg>
                                </span>
                                <span>Boost Ad</span>
                            </a>
                        @endif

                        <div >
                           @if($row->ad_status=='active')
                                <a title="Click to Change Status" class="flex items-center gap-2 bg-green-200 p-1 rounded cursor-not-allowed" >
                                    <span>Ad is Active</span>
                                    <span class="">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                </span>
                                </a>
                            @elseif($row->ad_status=='disabled')
                                <a title="Click to Change Status" class="flex items-center gap-2 bg-red-200 p-1 rounded cursor-not-allowed" >
                                    <span>Disabled</span>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                </span>
                                </a>
                            @else
                                <a title="Click to Change Status" class="flex items-center gap-2 bg-red-500 p-1 rounded cursor-not-allowed" >
                                    <span>Banned</span>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                </span>
                                </a>
                            @endif

                        </div>

                        @else
                            <a class="flex items-center gap-2 bg-gray-100 p-1 rounded mark-sold-btn"
                               href="javascript:void(0)"
                               data-mark-sold-url="/user/mark-sold/{{ $row->id }}"
                               title="Mark Advert Sold">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </span>
                                <span>Mark Sold</span>
                            </a>

                            <a class="flex items-center gap-2 bg-gray-100 p-1 rounded" href="/user/edit-ad/{{ $row->id }}">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </span>
                            <span>Edit Ad</span>
                        </a>
                        @if($row->featured=="Yes")
                            <a class="flex items-center gap-2 bg-green-300 p-1 rounded" href="/user/boosted-ad/{{ $row->id }}">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                    </svg>
                                </span>
                                <span>Ad Boosted</span>
                        </a>
                        @else
                            <a class="flex items-center gap-2 bg-gray-100 p-1 rounded" href="/user/boost-ad/{{ $row->id }}">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                    </svg>
                                </span>
                                <span>Boost Ad</span>
                            </a>
                        @endif

                        <div >
                           @if($row->ad_status=='active')
                                <a title="Click to Change Status"
                                   class="flex items-center gap-2 bg-green-200 p-1 rounded ad-status-btn"
                                   href="javascript:void(0)"
                                   data-status-url="/user/ad-status/disabled/{{ $row->id }}"
                                   data-current-status="active"
                                   data-new-status="disabled">
                                    <span>Ad is Active</span>
                                    <span class="">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                </span>
                                </a>
                            @elseif($row->ad_status=='disabled')
                                <a title="Click to Change Status"
                                   class="flex items-center gap-2 bg-red-200 p-1 rounded ad-status-btn"
                                   href="javascript:void(0)"
                                   data-status-url="/user/ad-status/active/{{ $row->id }}"
                                   data-current-status="disabled"
                                   data-new-status="active">
                                    <span>Disabled</span>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                </span>
                                </a>

                            @else
                                <a title="Click to Change Status" class="flex items-center gap-2 bg-red-200 p-1 rounded cursor-not-allowed" >
                                    <span>Ad Banned</span>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                </span>
                                </a>
                            @endif

                        </div>
                        @endif



                    </div>
              </div>
          </div>

      @endforeach
      @else
        <div class="flex flex-col items-center bg-white">
            <span>
                <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="external-Empty-empty-state-(outline)-outline-andi-nur-abdillah"/>
            </span>
            <span>No Posts here...</span>

        </div>
    @endif
</div>


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

  // SweetAlert2 Delete Confirmation
  document.addEventListener('click', function (e) {
    const deleteBtn = e.target.closest('.delete-ad-btn');

    if (deleteBtn) {
      e.preventDefault();
      const deleteUrl = deleteBtn.getAttribute('data-delete-url');

      Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to delete this advert permanently?",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to delete URL
          window.location.href = deleteUrl;
        }
      });
    }
  });

  // SweetAlert2 Mark Sold Confirmation
  document.addEventListener('click', function (e) {
    const markSoldBtn = e.target.closest('.mark-sold-btn');

    if (markSoldBtn) {
      e.preventDefault();
      const markSoldUrl = markSoldBtn.getAttribute('data-mark-sold-url');

      Swal.fire({
        title: 'Mark as Sold?',
        text: "Are you sure you want to mark this advert as sold?",
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, mark it sold!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to mark sold URL
          window.location.href = markSoldUrl;
        }
      });
    }
  });

  // SweetAlert2 Ad Status Change Confirmation
  document.addEventListener('click', function (e) {
    const statusBtn = e.target.closest('.ad-status-btn');

    if (statusBtn) {
      e.preventDefault();
      const statusUrl = statusBtn.getAttribute('data-status-url');
      const currentStatus = statusBtn.getAttribute('data-current-status');
      const newStatus = statusBtn.getAttribute('data-new-status');

      // Determine dialog content based on the action
      let title, text, icon, confirmButtonColor, confirmButtonText;

      if (newStatus === 'disabled') {
        title = 'Disable Ad?';
        text = 'This will hide your ad from public view. You can reactivate it anytime.';
        confirmButtonColor = '#ef4444';
        confirmButtonText = 'Yes, disable it';
      } else {
        title = 'Activate Ad?';
        text = 'This will make your ad visible to the public again.';
        confirmButtonColor = '#10b981';
        confirmButtonText = 'Yes, activate it';
      }

      Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to status change URL
          window.location.href = statusUrl;
        }
      });
    }
  });
})();
</script>
