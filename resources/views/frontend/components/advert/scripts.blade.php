<!-- Share Modal HTML -->
<div id="shareModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" id="shareOverlay"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Share this Ad</h3>
                <button id="closeModalShare" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex justify-center gap-4 py-4">
                 @include('frontend.components.social-share', ['url' => url()->current(), 'title' => $ad->ad_title])
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;



        // --- 2. MODALS ---
        function toggleModal(modalID, show) {
            const el = document.getElementById(modalID);
            if(el) {
                if(show) el.classList.remove('hidden');
                else el.classList.add('hidden');
            }
        }

        document.getElementById('openModalShare')?.addEventListener('click', () => toggleModal('shareModal', true));
        document.getElementById('closeModalShare')?.addEventListener('click', () => toggleModal('shareModal', false));
        document.getElementById('shareOverlay')?.addEventListener('click', () => toggleModal('shareModal', false));

        // --- 3. FOLLOW LOGIC ---
        const followBtn = document.getElementById('followButton');
        if (followBtn) {
            const ownerId = followBtn.dataset.userId;

            // Initial Check
            fetch(`/api/check-following/${ownerId}`).then(r => r.json()).then(data => {
                updateFollowUI(data.isFollowing);
            }).catch(e => console.error(e));

            followBtn.addEventListener('click', async () => {
                try {
                    const res = await fetch('/api/toggle-follow', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                        body: JSON.stringify({ followee_id: ownerId })
                    });
                    if(res.status === 401) window.location.href = '/login';
                    const data = await res.json();
                    updateFollowUI(data.isFollowing);
                    Swal.fire({icon: 'success', title: data.isFollowing ? 'Followed' : 'Unfollowed', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false});
                } catch(e) { Swal.fire('Error', 'Something went wrong', 'error'); }
            });

            function updateFollowUI(isFollowing) {
                if(isFollowing) {
                    followBtn.innerHTML = 'Unfollow';
                    followBtn.classList.replace('bg-transparent', 'bg-dark_green');
                    followBtn.classList.replace('text-dark_green', 'text-white');
                } else {
                    followBtn.innerHTML = 'Follow Seller';
                    followBtn.classList.replace('bg-dark_green', 'bg-transparent');
                    followBtn.classList.replace('text-white', 'text-dark_green');
                }
            }
        }

        // --- 4. SHOW CONTACT ---
        const contactBtn = document.getElementById('showContact');
        if(contactBtn) {
            contactBtn.addEventListener('click', function() {
                const box = document.getElementById('contactPhone');
                box.classList.toggle('hidden');
                this.innerText = box.classList.contains('hidden') ? 'Call' : 'Hide Number';
            });
        }
    });
</script>
