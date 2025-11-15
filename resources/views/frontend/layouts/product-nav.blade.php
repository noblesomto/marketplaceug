
<div id="nav-mobile" class="flex justify-between items-center bg-white h-16 py-2 px-5 border-solid border-b-8 border-b-secondary_dark block lg:hidden">
  <div class=" text-xl">
      <a href="{{ url()->previous() }}" class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" class="size-6 stroke-green-800 mr-3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            <span>Back</span>
        </a>
  </div>
  @php
    $user_id = session('user_id');
    $inWishlist = false;

    if ($user_id) {
        $inWishlist = \App\Models\Wishlist::where('advert_id', $ad->id)
                                          ->where('user_id', $user_id)
                                          ->exists();
    }
@endphp
  <div class="flex justify-start items-center space-x-5">
      <button id="shareBtn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
        </svg>
      </button>
      <a href="#"
       class="wishlist-toggle"
       data-ad-id="{{ $ad->id }}"
       data-in-wishlist="{{ $inWishlist ? 'true' : 'false' }}">
        <svg xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 24 24"
             stroke-width="1.5"
             stroke="currentColor"
             class="size-6 transition-colors duration-200 {{ $inWishlist ? 'fill-dark_green text-dark_green' : 'fill-none text-gray-600 hover:text-dark_green' }}">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
    </a>

  </div>



</div>


