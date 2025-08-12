<div class="relative ">

    <!-- Button to open the modal -->
    <button id="openModal" class="m-1 md:m-4 px-4 py-2 text-gray-700 rounded">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
      </svg>
    </button>

    <!-- Overlay and Modal -->
    <div id="modalOverlay" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden z-40">
        <div class="fixed inset-y-0 left-0 w-2/4 max-w-md bg-white shadow-lg p-6 z-50 transition-transform transform -translate-x-full" id="leftModal">
            <button id="closeModal" class="text-red-500 text-lg font-semibold mb-4">Close</button>         
            
                <ul class="flex flex-col p-2 w-full gap-4">
                  <li>
                    <a href="/" class="text-gray-700 hover:text-black px-3 py-2 rounded-md text-sm font-medium">Home</a>
                  </li>
                  <li>
                    <a href="/about-us" class="text-gray-700  hover:text-black px-3 py-2 rounded-md text-sm font-medium">About</a>
                  </li>
                  <li>
                    <a href="/how-it-works" class="text-gray-700  hover:text-black px-3 py-2 rounded-md text-sm font-medium">How It Works</a>
                  </li>
                  <li>
                    <a href="/faq" class="text-gray-700 hover:text-black px-3 py-2 rounded-md text-sm font-medium">FAQ</a>
                  </li>
                  <li>
                    <a href="/contact-us" class="text-gray-700  hover:text-black px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                  </li>
                </ul>

                @if(session()->get('user_id') =='')
                <div class="mt-4"><a href="/login" class="btn btn-primary py-2">Login</a></div>
                @else
                <div class="mt-4"><a href="/user/index" class="btn btn-primary py-2">Dashboard</a></div>
                @endif
           
        </div>
    </div>
  </div>

    <script>
        const openModalButton = document.getElementById('openModal');
        const closeModalButton = document.getElementById('closeModal');
        const modalOverlay = document.getElementById('modalOverlay');
        const leftModal = document.getElementById('leftModal');

        openModalButton.addEventListener('click', () => {
            modalOverlay.classList.remove('hidden');
            leftModal.classList.remove('-translate-x-full');
        });

        closeModalButton.addEventListener('click', () => {
            leftModal.classList.add('-translate-x-full');
            setTimeout(() => modalOverlay.classList.add('hidden'), 300);
        });

        // Close modal on overlay click
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                leftModal.classList.add('-translate-x-full');
                setTimeout(() => modalOverlay.classList.add('hidden'), 300);
            }
        });
    </script>
