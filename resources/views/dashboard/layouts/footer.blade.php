<section class="bg-white py-3 fixed bottom-0 left-0 w-full block lg:hidden">
	<div class="flex justify-between">
		<a href="/">
			<div class="flex flex-col items-center mx-2">
				<div>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
				</div>
				<div class="font-semibold text-xs">Home</div>
			</div>
		</a>

		<a href="/user/favourites">
			<div class="flex flex-col items-center mx-2">
				<div>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
				</div>
				<div class="font-semibold text-xs">Favourites</div>
			</div>
		</a>
		
		<a href="/user/post-ad">
			<div class="flex flex-col items-center mx-2">
				<div>
                    <svg viewBox="0 0 24 24" fill="none" data-title="createAdOutline" stroke="none" role="img" aria-hidden="true" focusable="false" class="shrink-0 fill-current  block align-middle size-5"><path d="M4.65457 10.3114L13.8284 19.4853L19.4853 13.8284L18.7624 13.1056C18.3835 12.7267 18.3931 12.1146 18.7758 11.7395C19.172 11.3513 19.8166 11.3313 20.2087 11.7234L20.8995 12.4142C21.6806 13.1953 21.6806 14.4616 20.8995 15.2427L15.2427 20.8995C14.4616 21.6806 13.1953 21.6806 12.4142 20.8995L3.24035 11.7256C2.78484 11.2701 2.57662 10.6231 2.68099 9.9874L3.55647 4.65491C3.60162 4.37991 3.7319 4.12601 3.92895 3.92895C4.12601 3.7319 4.37991 3.60162 4.65491 3.55647L9.9874 2.68099C10.6231 2.57662 11.2701 2.78484 11.7256 3.24035L12.4934 4.00813C12.8856 4.4003 12.8655 5.04487 12.4773 5.441C12.1023 5.82375 11.4902 5.83334 11.1113 5.45442L10.3114 4.65457L5.45233 5.45233L4.65457 10.3114Z" fill="currentColor"></path><path d="M9.58582 9.58587C10.1716 9.00008 10.1716 8.05033 9.58582 7.46455 9.00003 6.87876 8.05029 6.87876 7.4645 7.46455 6.87871 8.05033 6.87871 9.00008 7.4645 9.58587 8.05029 10.1717 9.00003 10.1717 9.58582 9.58587ZM15.0001 4.99994C15.0001 4.44765 15.4478 3.99994 16.0001 3.99994 16.5523 3.99994 17.0001 4.44765 17.0001 4.99994V6.99994H19.0001C19.5523 6.99994 20.0001 7.44765 20.0001 7.99994 20.0001 8.55222 19.5523 8.99994 19.0001 8.99994H17.0001V10.9999C17.0001 11.5522 16.5523 11.9999 16.0001 11.9999 15.4478 11.9999 15.0001 11.5522 15.0001 10.9999V8.99994H13.0001C12.4478 8.99994 12.0001 8.55222 12.0001 7.99994 12.0001 7.44765 12.4478 6.99994 13.0001 6.99994H15.0001V4.99994Z" fill="currentColor"></path></svg>
                </div>
				<div class="font-semibold text-xs">Post Ad</div>
			</div>
		</a>
		<a href="/user/messages">
		    <div class="flex flex-col items-center mx-2 relative">
		        <!-- Notification badge - hidden by default if count is 0 -->
		        <div class="unread-badge absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs" 
				     style="display: none;">
				    0
				</div>
		        <div>
		            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
		                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
		            </svg>
		        </div>
		        <div class="font-semibold text-xs">Messages</div>
		    </div>
		</a>
		<a href="/user/profile">
			<div class="flex flex-col items-center mx-2">
				<div>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 hover:size-6 hover:fill-dark_green">
	                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
	                </svg>
				</div>
				<div class="font-semibold text-xs">Profile</div>
			</div>
		</a>
	</div>
</section>

<script>
	const openSearchButton = document.getElementById('openSearch');
	const closeSearchButton = document.getElementById('closeSearch');
	const searchOverlay = document.getElementById('searchOverlay');

	// Function to open the search overlay
	function openSearchOverlay() {
	    searchOverlay.classList.remove('hidden');
	}

	// Add click event to both open buttons
	openSearchButton.addEventListener('click', openSearchOverlay);


	closeSearchButton.addEventListener('click', () => {
	    searchOverlay.classList.add('hidden');
	});

	// Close search on overlay click
	searchOverlay.addEventListener('click', (e) => {
	    if (e.target === searchOverlay) {
	        searchOverlay.classList.add('hidden');
	    }
	});        
</script>
<audio id="notificationSound" src="{{ asset('frontend/sound/new-message.mp3') }}" preload="auto"></audio>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userId = {!! json_encode(session('user_id')) !!};
        const notificationSound = document.getElementById('notificationSound');
        const notificationIcon = "{{ asset('frontend/images/message-icon.png') }}";
        let previousCount = null; // Changed from 0 to null

        // Request notification permission
        if ("Notification" in window && Notification.permission !== 'granted') {
            Notification.requestPermission().then(permission => {
                console.log("Notification permission:", permission);
            });
        }

        // Listen for real-time messages via Pusher
        if (userId) {
            Echo.private(`user.${userId}`)
                .listen('.new.message', (e) => {
                    console.log("📩 New message received:", e.message);
                    console.log("Subscribing to: user." + userId);

                    // Play sound
                    if (notificationSound) {
                        notificationSound.play().catch(err => console.warn("Sound failed:", err));
                    }

                    // Show browser notification
                    showNotification("📩 New Message", "You received a new message!");

                    // Update unread message badge
                    updateUnreadMessages();
                });
        }

        // Polling fallback for unread count
        function updateUnreadMessages() {
            fetch("{{ url('/unread-messages-count') }}")
                .then(response => response.json())
                .then(data => {
                    const badges = document.querySelectorAll('.unread-badge');

                    // Update UI
                    badges.forEach(badge => {
                        if (data.count > 0) {
                            badge.style.display = 'flex';
                            badge.textContent = data.count;
                        } else {
                            badge.style.display = 'none';
                        }
                    });

                    // Notify only if count increased
                    if (previousCount !== null && data.count > previousCount) {
                        showNotification("📩 New Message", `You have ${data.count} unread message(s).`);
                        if (notificationSound) {
                            notificationSound.play().catch(e => console.warn('Sound failed:', e));
                        }
                    }

                    previousCount = data.count;
                })
                .catch(error => {
                    console.error("Unread message check failed:", error);
                });
        }

        function showNotification(title, body) {
            if ("Notification" in window) {
                if (Notification.permission === "granted") {
                    new Notification(title, {
                        body: body,
                        icon: notificationIcon
                    });
                } else if (Notification.permission !== "denied") {
                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") {
                            new Notification(title, {
                                body: body,
                                icon: notificationIcon
                            });
                        }
                    });
                }
            }
        }

        // Initial call + polling every 10 seconds
        updateUnreadMessages();
        setInterval(updateUnreadMessages, 10000);
    });
</script>



</body>
</html>
