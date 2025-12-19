@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.layouts.mobile-back-nav')
@include('frontend.layouts.search')

<section class="w-full max-w-[95rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-3">
      <div class="col-span-2  hidden lg:block">
          @include('frontend.components.advert.side-advert')
      </div>
      <div class="col-span-12 md:col-span-8">
        <div class=" my-5 hidden lg:block">
                 @include('frontend.components.advert.banner-advert')
              </div>
        <div class="grid grid-cols-12 gap-3">
           <div class="col-span-3 hidden lg:block">
             @include('frontend.components.advert.seller-profile')
             <div class="mt-3">
                 @include('frontend.components.advert.side-advert')
             </div>
           </div>
           <div class="col-span-12 lg:col-span-9">

            <div class="mt-2 bg-white p-3 block lg:hidden">
             
                <div class="flex justify-start ">
                    <div class=" bg-gray-200 rounded-full py-4 px-4 mr-2 h-12">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-dark_green text-sm font-semibold"><a href="#">{{ $owner->name }} </a> </div>
                        @if($owner->verified=='yes')
                            <div class="bg-green-100  flex space-x-2 py-1 px-2 rounded-full mt-2">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                                      <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                                      <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                    </svg>
                                </span>
                                <span class="text-xs">Verified Seller</span>
                            </div>
                        @endif



                        @php $labels = feedback_rating_labels($owner->user_id); @endphp
                        <div class="{{ $labels['satisfaction']['color'] }} flex justify-start items-center rounded-full px-2 py-1 text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                                </svg>
                            </span>
                            <span>{{ $labels['satisfaction']['label'] }} Satisfied</span>
                        </div>


                        <div class="{{ $labels['friendly']['color'] }} flex justify-start items-center rounded-full px-2 py-1 text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                            </span>
                            <span>{{ $labels['friendly']['label'] }} Friendly</span>
                        </div>


                        <div class="{{ $labels['reliable']['color'] }} flex justify-start items-center rounded-full px-2 py-1 text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                </svg>
                            </span>
                            <span>{{ $labels['reliable']['label'] }} Reliable</span>
                        </div>


                        <div class="flex justify-start items-center  rounded-full px-2 py-1 text-xs mt-2">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </span>
                            <span>{{ $owner->acc_type }} User</span>
                        </div>

                        <div class="flex justify-start items-center  rounded-full px-2 py-1 text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                                </svg>
                            </span>
                            <span>Active since {{ date('j F Y', strtotime($owner->created_at)) }}</span>
                        </div>

                        <div class="flex justify-start items-center  rounded-full px-2 py-1 text-xs mt-2">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                  <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
                                </svg>
                            </span>
                            <span>{{ countUserFollowers($owner->user_id) }} follower(s)</span>
                        </div>

                    </div>

                </div>
                <div class="border border-gray-200 my-2"></div>
                <div class="flex justify-between">
                    <div class="text-dark_green text-sm">{{ $count_ads }} ads online</div>
                    @if(session()->get('user_id') !='')

                    @if($owner->user_id == $user->user_id)

                    @else
                    <div>
                        <button 
                            
                            data-user-id="{{ $owner->user_id }}"
                            class="follow-button flex justify-start items-center w-full bg-transparent hover:bg-primary text-dark_green font-semibold hover:text-dark_green py-1 px-2 border border-dark_green hover:border-dark_green rounded-full">
                            <span class="mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                            </span>
                            <span>Follow</span>
                        </button>
                    </div>
                    @endif
                    @endif
                </div>
                <div class="border border-gray-200 my-2"></div>

            </div>
            <div class="pb-2" id="ads-container">
                @include('frontend.components.advert.advert-list', ['ads' => $ads])
            </div>
            @if($ads->isEmpty())
                <div class="flex flex-col h-screen items-center bg-white p-10">
                    <span>
                        <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="No Adverts Currently"/>
                    </span>
                    <span>No Item here yet...</span>
                </div>
            @endif


            @if(isset($hasMore) && $hasMore)
                <div class="mt-3 mb-4 px-2 flex justify-center">
                    <button id="load-more-btn"
                            class="bg-dark_green hover:bg-secondary_dark text-white font-semibold py-3 px-8 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full">
                        <span id="load-more-text">Show More</span>
                        <span id="load-more-spinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>
            @endif
            <div class="pb-5"></div>
           </div>
        </div>
      </div>
      <div class="col-span-2 hidden lg:block">
        @include('frontend.components.advert.side-advert')
      </div>
  </div>
</section>




<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const followButtons = document.querySelectorAll('.follow-button');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!followButtons.length) {
        console.error('No follow buttons found');
        return;
    }

    followButtons.forEach(button => {
        const adOwnerId = button.dataset.userId;
        checkFollowingStatus(button, adOwnerId);

        button.addEventListener('click', () => toggleFollow(button, adOwnerId));
    });

    async function checkFollowingStatus(button, userId) {
        try {
            const response = await fetch(`/api/check-following/${userId}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (response.ok) {
                const data = await response.json();
                updateButtonUI(userId, data.isFollowing);
            }
        } catch (error) {
            console.error('Error checking follow status:', error);
        }
    }

    async function toggleFollow(button, userId) {
        try {
            const response = await fetch('/api/toggle-follow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ followee_id: userId })
            });

            const data = await response.json();

            if (response.ok) {
                updateButtonUI(userId, data.isFollowing);

                Swal.fire({
                    icon: 'success',
                    title: data.isFollowing ? 'Followed!' : 'Unfollowed!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Error updating follow status',
                    confirmButtonColor: '#d33'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to update follow status',
                confirmButtonColor: '#d33'
            });
        }
    }

    function updateButtonUI(userId, isFollowing) {
        const allButtons = document.querySelectorAll(`.follow-button[data-user-id="${userId}"]`);

        allButtons.forEach(btn => {
            const iconSvg = isFollowing ? `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                </svg>
            ` : `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
            `;

            btn.innerHTML = `<span class="mr-2">${iconSvg}</span><span>${isFollowing ? 'Unfollow' : 'Follow'}</span>`;

            if (isFollowing) {
                btn.classList.add('bg-secondary_dark', 'text-dark_green');
                btn.classList.remove('hover:bg-secondary_dark');
            } else {
                btn.classList.remove('bg-secondary_dark', 'text-dark_green');
                btn.classList.add('hover:bg-secondary_dark');
            }
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 2;
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('ads-container');

    const sellerId = '{{ $owner->user_id }}';
    const currentAdId = '{{ $ad->id }}';

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');

            fetch(`/seller/${sellerId}/${currentAdId}/load-more?page=${currentPage}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    adsContainer.insertAdjacentHTML('beforeend', data.html);
                    currentPage++;

                    if (!data.hasMore) {
                        loadMoreBtn.style.display = 'none';
                    }

                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error loading more ads:', error);
                    alert('Failed to load more ads. Please try again.');

                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                });
        });
    }
});
</script>
@include('frontend.layouts.footer')


