<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        /* Custom styles for sticky form */
        .chat-container {
            padding-bottom: 120px; /* Space for the sticky form */
        }

        .sticky-form {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: white;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Mobile adjustments - assume mobile menu is 60px high */
        @media (max-width: 768px) {
            .chat-container {
                padding-bottom: 140px; /* Extra space for mobile menu */
            }

            .sticky-form {
                bottom: 60px; /* Height of mobile footer menu */
            }
        }

        /* Custom dark green color */
        .bg-dark_green { background-color: #0f5132; }
        .text-dark_green { color: #0f5132; }
        .bg-secondary-200 { background-color: #e5e7eb; }

        /* Auto-resize textarea */
        .auto-resize {
            resize: none;
            overflow: hidden;
            min-height: 40px;
            max-height: 120px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Main Content -->
    <div class="chat-container">
        <section class="max-w-2xl mx-auto text-sm pt-2">
            <div class="flex flex-col">
                <div class="pb-5">
                    <!-- Chat Header -->
                    <div class="flex items-center mb-2 px-2 bg-white rounded-lg">
                        <a href="#" class="flex items-center">
                            <div class="mr-2">
                                <img class="w-14 h-14 rounded-full bg-gray-300" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTYiIGhlaWdodD0iNTYiIHZpZXdCb3g9IjAgMCA1NiA1NiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjgiIGN5PSIyOCIgcj0iMjgiIGZpbGw9IiNEMUQ1REIiLz4KPHN2ZyB4PSIxNCIgeT0iMTQiIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJNMTIgMTJDMTQuNDg1MyAxMiAxNi41IDkuOTg1MjggMTYuNSA3LjVDMTYuNSA1LjAxNDcyIDE0LjQ4NTMgMyAxMiAzQzkuNTE0NzIgMyA3LjUgNS4wMTQ3MiA3LjUgNy41QzcuNSA5Ljk4NTI4IDkuNTE0NzIgMTIgMTIgMTJaIiBmaWxsPSIjNjc3NDhEIi8+CjxwYXRoIGQ9Ik0yMSAxOUMxOC44IDEzLjQgMTUuNiAxMSAxMiAxMUM4LjQgMTEgNS4yIDEzLjQgMyAxOSIgc3Ryb2tlPSIjNjc3NDhEIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4KPC9zdmc+" alt="User Avatar">
                            </div>
                            <div class="flex-col">
                                <h4 class="font-semibold text-lg">John Doe</h4>
                                <h6 class="text-base -mt-2 text-sm lg:text-base">MacBook Pro 2021 For Sale</h6>
                            </div>
                        </a>
                    </div>

                    <!-- Chat Messages -->
                    <div id="chat-box" class="chat-box overflow-y-auto rounded-lg mb-2 max-h-[300px] sm:max-h-[350px] md:max-h-[380px] lg:max-h-[400px] bg-white p-2">
                        <!-- Sample messages -->
                        <div class="my-2 mx-1 text-right">
                            <div class="inline-block rounded-lg p-2 bg-dark_green text-white font-semibold">
                                <p>Hi, is this still available?</p>
                                <span class="text-xs font-normal">05 Sep 2025, 2:30pm</span>
                            </div>
                        </div>

                        <div class="my-2 mx-1 text-left">
                            <div class="inline-block rounded-lg p-2 bg-secondary-200 font-semibold">
                                <p>Yes, it's still available. Would you like to see it?</p>
                                <span class="text-xs font-normal">05 Sep 2025, 2:35pm</span>
                            </div>
                        </div>

                        <div class="my-2 mx-1 text-right">
                            <div class="inline-block rounded-lg p-2 bg-dark_green text-white font-semibold">
                                <p>Sure! When can I come take a look?</p>
                                <span class="text-xs font-normal">05 Sep 2025, 2:36pm</span>
                            </div>
                        </div>

                        <!-- Add more sample messages to demonstrate scrolling -->
                        <div class="my-2 mx-1 text-left">
                            <div class="inline-block rounded-lg p-2 bg-secondary-200 font-semibold">
                                <p>How about tomorrow at 3 PM?</p>
                                <span class="text-xs font-normal">05 Sep 2025, 2:40pm</span>
                            </div>
                        </div>

                        <div class="my-2 mx-1 text-right">
                            <div class="inline-block rounded-lg p-2 bg-dark_green text-white font-semibold">
                                <p>Perfect! I'll see you then.</p>
                                <span class="text-xs font-normal">05 Sep 2025, 2:42pm</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sample content to show scrolling -->
                    <div class="bg-white p-4 rounded-lg mb-4">
                        <h3 class="font-semibold mb-2">Product Details</h3>
                        <p class="text-gray-600 mb-2">This is a sample product description to demonstrate how the sticky form works with scrollable content.</p>
                        <p class="text-gray-600 mb-2">The form will remain fixed at the bottom while you scroll through the chat messages and other content.</p>
                        <p class="text-gray-600">On mobile devices, it will position itself above the mobile navigation menu.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Sticky Chat Form -->
    <div class="sticky-form">
        <form id="chat-form" class="max-w-2xl mx-auto p-4">
            <input type="hidden" name="advert_id" value="123">
            <input type="hidden" name="receiver_id" value="456">

            <!-- Offer Toggle (show only for buyers) -->
            <div class="space-y-4 mb-4">
                <!-- Toggle Switch -->
                <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" id="toggleSwitch" class="sr-only">
                        <div class="w-11 h-6 bg-gray-300 rounded-full shadow-inner transition toggle-bg"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform toggle-dot"></div>
                    </div>
                    <span class="ml-3 text-gray-700">Send the seller an offer?</span>
                </label>

                <!-- Conditionally Visible Input -->
                <div id="extraInputWrapper" class="hidden">
                    <label for="extraInput" class="block text-base font-medium text-gray-700">Enter Amount</label>
                    <input
                        type="text"
                        id="extraInput"
                        name="amount"
                        placeholder="Enter Amount"
                        maxlength="11"
                        pattern="[0-9]*"
                        disabled
                        class="mt-1 block h-10 w-full rounded-lg border border-gray-300 focus:outline-none focus:border-blue-400 px-2"
                    >
                </div>
            </div>

            <!-- Message Input -->
            <div id="send-message" class="flex items-center bg-gray-50 p-2 rounded-lg border">
                <div class="flex-shrink-0 mr-2">
                    <div class="relative inline-block">
                        <input
                            type="file"
                            id="fileUpload"
                            class="hidden"
                            multiple
                            accept="image/*"
                        >
                        <label for="fileUpload" class="cursor-pointer">
                            <i class="bi bi-camera text-2xl text-gray-600"></i>
                        </label>
                        <span id="fileBadge"
                            class="hidden absolute -top-2 -right-2 bg-yellow-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                            0
                        </span>
                    </div>
                </div>

                <div class="flex-1 mr-2">
                    <textarea
                        id="chat-input"
                        name="message"
                        rows="1"
                        class="auto-resize w-full p-2 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-400 text-gray-700 resize-none"
                        placeholder="Type your message..."
                        autocomplete="off"
                    ></textarea>
                </div>

                <div class="flex-shrink-0">
                    <button type="submit" id="sendBtn" class="p-2">
                        <i class="bi bi-send-fill text-2xl text-dark_green"></i>
                    </button>
                </div>
            </div>

            <!-- Warning message -->
            <div class="mt-2 text-center">
                <span class="text-red-500 text-sm">** Please avoid making payment before inspecting the item</span>
            </div>
        </form>
    </div>

    <!-- Mobile Footer Menu Simulation (for demonstration) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-800 text-white z-40">
        <div class="flex justify-around py-3">
            <div class="text-center">
                <i class="bi bi-house text-xl"></i>
                <div class="text-xs">Home</div>
            </div>
            <div class="text-center">
                <i class="bi bi-search text-xl"></i>
                <div class="text-xs">Search</div>
            </div>
            <div class="text-center">
                <i class="bi bi-chat text-xl"></i>
                <div class="text-xs">Messages</div>
            </div>
            <div class="text-center">
                <i class="bi bi-person text-xl"></i>
                <div class="text-xs">Profile</div>
            </div>
        </div>
    </div>

    <script>
        // Toggle switch functionality
        const toggleSwitch = document.getElementById('toggleSwitch');
        const extraInputWrapper = document.getElementById('extraInputWrapper');
        const extraInput = document.getElementById('extraInput');
        const toggleBg = document.querySelector('.toggle-bg');
        const toggleDot = document.querySelector('.toggle-dot');

        toggleSwitch.addEventListener('change', function() {
            if (this.checked) {
                extraInputWrapper.classList.remove('hidden');
                extraInput.disabled = false;
                toggleBg.classList.add('bg-blue-500');
                toggleBg.classList.remove('bg-gray-300');
                toggleDot.classList.add('translate-x-5');
            } else {
                extraInputWrapper.classList.add('hidden');
                extraInput.disabled = true;
                extraInput.value = '';
                toggleBg.classList.remove('bg-blue-500');
                toggleBg.classList.add('bg-gray-300');
                toggleDot.classList.remove('translate-x-5');
            }
        });

        // Auto-resize textarea
        const textarea = document.getElementById('chat-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // File upload counter
        const fileUpload = document.getElementById('fileUpload');
        const fileBadge = document.getElementById('fileBadge');

        fileUpload.addEventListener('change', function() {
            const fileCount = this.files.length;
            if (fileCount > 0) {
                fileBadge.textContent = fileCount;
                fileBadge.classList.remove('hidden');
            } else {
                fileBadge.classList.add('hidden');
            }
        });

        // Form submission
        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const message = textarea.value.trim();
            if (message || fileUpload.files.length > 0) {
                // Add message to chat (demo)
                const chatBox = document.getElementById('chat-box');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'my-2 mx-1 text-right';
                messageDiv.innerHTML = `
                    <div class="inline-block rounded-lg p-2 bg-dark_green text-white font-semibold">
                        <p>${message}</p>
                        <span class="text-xs font-normal">${new Date().toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        })}</span>
                    </div>
                `;
                chatBox.appendChild(messageDiv);
                chatBox.scrollTop = chatBox.scrollHeight;

                // Clear form
                textarea.value = '';
                textarea.style.height = 'auto';
                fileUpload.value = '';
                fileBadge.classList.add('hidden');
            }
        });

        // Auto-scroll chat to bottom on load
        document.addEventListener('DOMContentLoaded', function() {
            const chatBox = document.getElementById('chat-box');
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    </script>
</body>
</html>
