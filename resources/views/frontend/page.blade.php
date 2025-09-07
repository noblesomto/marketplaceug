<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Chat Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        /* Custom CSS for full-height chat layout */
        .chat-layout {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            flex-shrink: 0; /* Don't shrink */
        }

        .chat-main {
            flex: 1; /* Take all available space */
            min-height: 0; /* Important for flex child overflow */
            display: flex;
            flex-direction: column;
        }

        .chat-box {
            flex: 1; /* Take all available space in main area */
            overflow-y: auto;
            min-height: 0; /* Important for overflow to work */
        }

        .chat-footer {
            flex-shrink: 0; /* Don't shrink */
        }

        /* Custom scrollbar for chat */
        .chat-box::-webkit-scrollbar {
            width: 6px;
        }

        .chat-box::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .chat-box::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .chat-box::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Auto-resize textarea */
        .auto-resize {
            resize: none;
            overflow: hidden;
            min-height: 40px;
            max-height: 120px;
        }

        /* Custom colors */
        .bg-dark_green { background-color: #0f5132; }
        .text-dark_green { color: #0f5132; }

        /* Image preview container */
        .image-preview-container {
            max-height: 120px;
            overflow-y: auto;
        }

        .image-preview-container::-webkit-scrollbar {
            height: 4px;
        }

        .image-preview-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }

        .image-preview-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        /* Drag and drop feedback */
        .drag-over {
            border: 2px dashed #3b82f6;
            background-color: rgba(59, 130, 246, 0.05);
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .chat-layout {
                height: 100vh; /* Full viewport height on mobile */
            }
        }

        /* Ensure proper spacing on larger screens */
        @media (min-width: 769px) {
            .chat-container {
                max-width: 42rem; /* max-w-2xl equivalent */
                margin: 0 auto;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="chat-layout">
        <!-- Chat Header -->
        <div class="chat-header bg-white border-b border-gray-200">
            <div class="chat-container">
                <div class="flex items-center p-4 gap-4">
                    <a href="/user/messages" class="flex-shrink-0">
                        <i class="bi bi-chevron-left text-dark_green text-xl"></i>
                    </a>
                    <a href="#" class="flex items-center min-w-0 flex-1">
                        <div class="flex-shrink-0 mr-3">
                            <img class="w-12 h-12 rounded-full bg-gray-300 object-cover"
                                 src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIHZpZXdCb3g9IjAgMCA0OCA0OCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjQiIGN5PSIyNCIgcj0iMjQiIGZpbGw9IiNEMUQ1REIiLz4KPHN2ZyB4PSIxMiIgeT0iMTIiIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJNMTIgMTJDMTQuNDg1MyAxMiAxNi41IDkuOTg1MjggMTYuNSA3LjVDMTYuNSA1LjAxNDcyIDE0LjQ4NTMgMyAxMiAzQzkuNTE0NzIgMyA3LjUgNS4wMTQ3MiA3LjUgNy41QzcuNSA5Ljk4NTI4IDkuNTE0NzIgMTIgMTIgMTJaIiBmaWxsPSIjNjc3NDhEIi8+CjxwYXRoIGQ9Ik0yMSAxOUMxOC44IDEzLjQgMTUuNiAxMSAxMiAxMUM4LjQgMTEgNS4yIDEzLjQgMyAxOSIgc3Ryb2tlPSIjNjc3NDhEIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4KPC9zdmc+"
                                 alt="User Avatar">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-semibold text-lg text-gray-900 truncate">John Doe</h4>
                            <h6 class="text-sm text-gray-600 truncate -mt-1">MacBook Pro 2021 For Sale</h6>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Chat Main Area -->
        <div class="chat-main bg-gray-50">
            <div class="chat-container h-full flex flex-col">
                <!-- Chat Messages -->
                <div id="chat-box" class="chat-box bg-white p-4">
                    <!-- Sample Messages -->
                    <div class="flex mb-4 justify-end">
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-200 text-gray-800 rounded-br-md">
                                <div class="text-sm leading-relaxed">Hi, is this MacBook still available?</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-right mr-1">
                                Sep 5, 2:30 PM
                            </div>
                        </div>
                        <div class="flex-shrink-0 ml-2">
                            <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center shadow-sm">
                                <span class="text-white text-sm font-semibold">ME</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex mb-4 justify-start">
                        <div class="flex-shrink-0 mr-2">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center shadow-sm">
                                <span class="text-gray-800 text-sm font-semibold">JD</span>
                            </div>
                        </div>
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-50 text-gray-800 rounded-bl-md">
                                <div class="text-sm leading-relaxed">Yes, it's still available! Are you interested in seeing it?</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-left ml-1">
                                Sep 5, 2:35 PM
                            </div>
                        </div>
                    </div>

                    <div class="flex mb-4 justify-end">
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-200 text-gray-800 rounded-br-md">
                                <div class="text-sm leading-relaxed">Definitely! When would be a good time to meet?</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-right mr-1">
                                Sep 5, 2:36 PM
                            </div>
                        </div>
                        <div class="flex-shrink-0 ml-2">
                            <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center shadow-sm">
                                <span class="text-white text-sm font-semibold">ME</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex mb-4 justify-start">
                        <div class="flex-shrink-0 mr-2">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center shadow-sm">
                                <span class="text-gray-800 text-sm font-semibold">JD</span>
                            </div>
                        </div>
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-50 text-gray-800 rounded-bl-md">
                                <div class="text-sm leading-relaxed">How about tomorrow at 3 PM? I can meet you at the coffee shop downtown.</div>
                                <!-- Sample images -->
                                <div class="grid gap-2 mt-3 grid-cols-2">
                                    <div class="relative group">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjgwIiB2aWV3Qm94PSIwIDAgMTAwIDgwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRTVFN0VCIiByeD0iOCIvPgo8cGF0aCBkPSJNMzUgMzBMMzggMzNMMzUgMzZNNDUgMzBMNDggMzNMNDUgMzZNNTUgMzBMNTggMzNMNTUgMzZNNjUgMzBMNjggMzNMNjUgMzYiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHN2ZyB4PSIzNSIgeT0iNDUiIHdpZHRoPSIzMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJtMTUgNiAyIDIgNi02IiIgc3Ryb2tlPSIjNjc3NDhEIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4KPC9zdmc+" alt="Sample image" class="w-full h-24 object-cover rounded-lg cursor-pointer">
                                        <div class="pointer-events-none absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-all duration-200 rounded-lg"></div>
                                    </div>
                                    <div class="relative group">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjgwIiB2aWV3Qm94PSIwIDAgMTAwIDgwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRkVGM0M3IiByeD0iOCIvPgo8cGF0aCBkPSJNMzUgMzBMMzggMzNMMzUgMzZNNDUgMzBMNDggMzNMNDUgMzZNNTUgMzBMNTggMzNMNTUgMzZNNjUgMzBMNjggMzNMNjUgMzYiIHN0cm9rZT0iI0Q5NzcwNiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHN2ZyB4PSIzNSIgeT0iNDUiIHdpZHRoPSIzMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJtMTUgNiAyIDIgNi02IiIgc3Ryb2tlPSIjRDk3NzA2IiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4KPC9zdmc+" alt="Sample image" class="w-full h-24 object-cover rounded-lg cursor-pointer">
                                        <div class="pointer-events-none absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-all duration-200 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-left ml-1">
                                Sep 5, 2:40 PM
                            </div>
                        </div>
                    </div>

                    <div class="flex mb-4 justify-end">
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-200 text-gray-800 rounded-br-md">
                                <div class="text-sm leading-relaxed">Perfect! See you there at 3 PM tomorrow. 👍</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-right mr-1">
                                Sep 5, 2:42 PM
                            </div>
                        </div>
                        <div class="flex-shrink-0 ml-2">
                            <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center shadow-sm">
                                <span class="text-white text-sm font-semibold">ME</span>
                            </div>
                        </div>
                    </div>

                    <!-- Add more messages to demonstrate scrolling -->
                    <div class="flex mb-4 justify-start">
                        <div class="flex-shrink-0 mr-2">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center shadow-sm">
                                <span class="text-gray-800 text-sm font-semibold">JD</span>
                            </div>
                        </div>
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-50 text-gray-800 rounded-bl-md">
                                <div class="text-sm leading-relaxed">Great! I'll bring the laptop and all the accessories. Looking forward to meeting you!</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-left ml-1">
                                Sep 5, 2:45 PM
                            </div>
                        </div>
                    </div>

                    <div class="flex mb-4 justify-end">
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-200 text-gray-800 rounded-br-md">
                                <div class="text-sm leading-relaxed">Perfect! See you there at 3 PM tomorrow. 👍</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-right mr-1">
                                Sep 5, 2:42 PM
                            </div>
                        </div>
                        <div class="flex-shrink-0 ml-2">
                            <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center shadow-sm">
                                <span class="text-white text-sm font-semibold">ME</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex mb-4 justify-start">
                        <div class="flex-shrink-0 mr-2">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center shadow-sm">
                                <span class="text-gray-800 text-sm font-semibold">JD</span>
                            </div>
                        </div>
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-50 text-gray-800 rounded-bl-md">
                                <div class="text-sm leading-relaxed">Great! I'll bring the laptop and all the accessories. Looking forward to meeting you!</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-left ml-1">
                                Sep 5, 2:45 PM
                            </div>
                        </div>
                    </div>

                    <div class="flex mb-4 justify-end">
                        <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                            <div class="rounded-2xl px-4 py-3 bg-gray-200 text-gray-800 rounded-br-md">
                                <div class="text-sm leading-relaxed">Perfect! See you there at 3 PM tomorrow. 👍</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-right mr-1">
                                Sep 5, 2:42 PM
                            </div>
                        </div>
                        <div class="flex-shrink-0 ml-2">
                            <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center shadow-sm">
                                <span class="text-white text-sm font-semibold">ME</span>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <!-- Sticky Chat Footer -->
        <div class="chat-footer bg-white border-t border-gray-200 shadow-lg">
            <div class="chat-container">
                <form id="chat-form" class="p-4">
                    <input type="hidden" name="advert_id" value="123">
                    <input type="hidden" name="receiver_id" value="456">

                    <!-- Offer Toggle (conditional) -->
                    <div class="space-y-4 mb-4">
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" id="toggleSwitch" class="sr-only">
                                <div class="w-11 h-6 bg-gray-300 rounded-full shadow-inner transition toggle-bg"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform toggle-dot"></div>
                            </div>
                            <span class="ml-3 text-gray-700">Send the seller an offer?</span>
                        </label>

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

                    <!-- Image Preview Container -->
                    <div id="imagePreviewContainer" class="image-preview-container hidden flex flex-wrap gap-2 p-2 bg-gray-50 rounded-lg mb-2"></div>

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
                                <span id="fileBadge" class="hidden absolute -top-2 -right-2 bg-yellow-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">0</span>
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('toggleSwitch');
            const wrapper = document.getElementById('extraInputWrapper');
            const amountInput = document.getElementById('extraInput');
            const chatInput = document.getElementById('chat-input');
            const chatForm = document.getElementById('chat-form');
            const sendBtn = document.getElementById('sendBtn');
            const fileUpload = document.getElementById('fileUpload');
            const fileBadge = document.getElementById('fileBadge');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const chatBox = document.getElementById('chat-box');

            let selectedFiles = [];

            // Auto-scroll chat to bottom on load
            if (chatBox) {
                setTimeout(() => {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }, 100);
            }

            // Toggle switch functionality
            if (toggle && wrapper && amountInput) {
                const toggleBg = document.querySelector('.toggle-bg');
                const toggleDot = document.querySelector('.toggle-dot');

                toggle.addEventListener('change', function () {
                    if (this.checked) {
                        wrapper.classList.remove('hidden');
                        amountInput.disabled = false;
                        amountInput.required = true;
                        toggleBg.classList.add('bg-blue-500');
                        toggleBg.classList.remove('bg-gray-300');
                        toggleDot.classList.add('translate-x-5');

                        if (amountInput.value) {
                            chatInput.value = `Would you accept ₦${amountInput.value}`;
                        }
                    } else {
                        wrapper.classList.add('hidden');
                        amountInput.disabled = true;
                        amountInput.required = false;
                        amountInput.value = '';
                        toggleBg.classList.remove('bg-blue-500');
                        toggleBg.classList.add('bg-gray-300');
                        toggleDot.classList.remove('translate-x-5');

                        if (chatInput.value.startsWith('Would you accept')) {
                            chatInput.value = '';
                        }
                    }
                });

                amountInput.addEventListener('input', function() {
                    if (toggle.checked && this.value) {
                        chatInput.value = `Would you accept ₦${this.value}`;
                    }
                });
            }

            // File upload handling
            if (fileUpload) {
                fileUpload.addEventListener('change', function(e) {
                    selectedFiles = Array.from(e.target.files);
                    updateFileDisplay();
                });
            }

            function updateFileDisplay() {
                const fileCount = selectedFiles.length;

                if (fileCount > 0) {
                    fileBadge.textContent = fileCount;
                    fileBadge.classList.remove('hidden');
                    imagePreviewContainer.classList.remove('hidden');

                    imagePreviewContainer.innerHTML = '';
                    selectedFiles.forEach((file, index) => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const previewDiv = document.createElement('div');
                                previewDiv.className = 'relative inline-block';
                                previewDiv.innerHTML = `
                                    <img src="${e.target.result}" alt="Preview" class="w-16 h-16 object-cover rounded-lg border">
                                    <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold hover:bg-red-600" onclick="removeImage(${index})">×</button>
                                `;
                                imagePreviewContainer.appendChild(previewDiv);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                } else {
                    fileBadge.classList.add('hidden');
                    imagePreviewContainer.classList.add('hidden');
                }
            }

            // Global remove image function
            window.removeImage = function(index) {
                selectedFiles.splice(index, 1);
                updateFileDisplay();

                const dt = new DataTransfer();
                selectedFiles.forEach(file => dt.items.add(file));
                fileUpload.files = dt.files;
            };

            // Auto-resize textarea
            if (chatInput) {
                chatInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });

                // Keyboard shortcut
                chatInput.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        chatForm.dispatchEvent(new Event('submit'));
                    }
                });
            }

            // Form submission
            if (chatForm) {
                chatForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const message = chatInput.value.trim();
                    const hasFiles = selectedFiles.length > 0;

                    if (!message && !hasFiles) return;

                    // Demo: Add message to chat
                    if (message || hasFiles) {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'flex mb-4 justify-end';
                        messageDiv.innerHTML = `
                            <div class="max-w-xs sm:max-w-md lg:max-w-lg">
                                <div class="rounded-2xl px-4 py-3 bg-gray-200 text-gray-800 rounded-br-md">
                                    ${message ? `<div class="text-sm leading-relaxed">${message}</div>` : ''}
                                    ${hasFiles ? '<div class="text-sm text-gray-600 mt-1">📷 ' + selectedFiles.length + ' image(s)</div>' : ''}
                                </div>
                                <div class="text-xs text-gray-500 mt-1 text-right mr-1">
                                    Just now
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-2">
                                <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center shadow-sm">
                                    <span class="text-white text-sm font-semibold">ME</span>
                                </div>
                            </div>
                        `;
                        chatBox.appendChild(messageDiv);
                        chatBox.scrollTop = chatBox.scrollHeight;

                        // Clear form
                        chatInput.value = '';
                        chatInput.style.height = 'auto';
                        selectedFiles = [];
                        updateFileDisplay();
                        fileUpload.value = '';

                        if (toggle && toggle.checked) {
                            amountInput.value = '';
                            wrapper.classList.add('hidden');
                            amountInput.disabled = true;
                            toggle.checked = false;
                            const toggleBg = document.querySelector('.toggle-bg');
                            const toggleDot = document.querySelector('.toggle-dot');
                            toggleBg.classList.remove('bg-blue-500');
                            toggleBg.classList.add('bg-gray-300');
                            toggleDot.classList.remove('translate-x-5');
                        }
                    }
                });
            }

            // Drag and drop functionality
            if (chatForm) {
                chatForm.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.add('drag-over');
                });

                chatForm.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');
                });

                chatForm.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');

                    const files = Array.from(e.dataTransfer.files);
                    const allowedFiles = files.filter(file => file.type.startsWith('image/'));

                    if (allowedFiles.length > 0) {
                        selectedFiles = [...selectedFiles, ...allowedFiles];
                        updateFileDisplay();

                        const dt = new DataTransfer();
                        selectedFiles.forEach(file => dt.items.add(file));
                        fileUpload.files = dt.files;
                    }
                });
            }
        });

        // Sample image modal function (you can implement this)
        function openImageModal(images, index) {
            console.log('Opening image modal for:', images[index]);
            // Implement your image modal/lightbox here
        }
    </script>
</body>
</html>
