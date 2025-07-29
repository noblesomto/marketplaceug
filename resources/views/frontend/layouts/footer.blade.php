<section class="bg-white py-3 fixed bottom-0 left-0 w-full block lg:hidden shadow mt-20">
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
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 hover:fill-dark_green">
	                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
	                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
	                </svg>
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

<section class="px-2 pt-5 bg-white hidden lg:block">
    <div class="max-w-4xl mx-auto">

         <div class="max-w-4xl mx-auto text-center text-sm font-semibold">
            <ul class="flex flex-wrap justify-center space-x-6 text-gray-600">
                <li><a href="/" class="hover:text-blue-500">Home</a></li>
                <li><a href="/about-us" class="hover:text-blue-500">About Us</a></li>
                <li><a href="/faq" class="hover:text-blue-500">FAQ</a></li>
                <li><a href="/contact-us" class="hover:text-blue-500">Contact Us</a></li>
            </ul>
        </div>
        

    <!--Copy Rights -->
    <div class="flex justify-center gap-2 mt-5 pb-10 text-sm text-center border-t-2 border-t-gray-300 pt-4">
        <div class=" ">
            <span class="font-semibold px-2">{{ config('global.site_name') }}. {{ date('Y ') }}</span>
        </div>
        <div class="">
            All rights reserved
        </div>
     
    </div>
   
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
  // Get all price filter forms on the page
  const priceForms = document.querySelectorAll('form[id^="price-filter-form"]');

  // Allow uncheck for radio buttons and sync between forms
  document.querySelectorAll('.price-radio').forEach(radio => {
    radio.addEventListener('click', function() {
      if (this.checked) {
        if (this.dataset.checked === 'true') {
          // Uncheck this radio
          this.checked = false;
          this.dataset.checked = 'false';
          // Uncheck corresponding radios in other forms
          syncRadios(this.name, this.value, false);
        } else {
          // Uncheck all radios first
          document.querySelectorAll(`.price-radio[name="${this.name}"]`).forEach(r => {
            r.checked = false;
            r.dataset.checked = 'false';
          });
          // Check this one
          this.checked = true;
          this.dataset.checked = 'true';
          // Check corresponding radios in other forms
          syncRadios(this.name, this.value, true);
        }
      }
    });
  });

  // Helper function to sync radio buttons across forms
  function syncRadios(name, value, checked) {
    document.querySelectorAll(`.price-radio[name="${name}"][value="${value}"]`).forEach(radio => {
      radio.checked = checked;
      radio.dataset.checked = checked.toString();
    });
  }

  // Submit handler for all forms
  priceForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      fetchAds();
    });
  });

  // Clear buttons - sync all forms
  document.querySelectorAll('[id^="clear-filter"]').forEach(button => {
    button.addEventListener('click', function() {
      // Clear all forms
      priceForms.forEach(form => form.reset());
      // Uncheck all radios
      document.querySelectorAll('.price-radio').forEach(radio => {
        radio.checked = false;
        radio.dataset.checked = 'false';
      });
    });
  });

  // Fetch ads function (unchanged)
  function fetchAds(url = '/filter/adverts') {
    // Use the first form (they should all have the same values)
    const formData = new FormData(priceForms[0]);

    fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      document.getElementById('advert-results').innerHTML = data.html;
      attachPaginationEvents();
      verifyPriceModal.classList.add('hidden');
    });
  }

  // Pagination handling (unchanged)
  function attachPaginationEvents() {
    document.querySelectorAll('#advert-results .pagination a').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        fetchAds(this.href);
      });
    });
  }

  attachPaginationEvents();
});
</script>


</body>
</html>
