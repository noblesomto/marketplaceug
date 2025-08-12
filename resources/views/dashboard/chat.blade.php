@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')
<style>
    /* Tailwind-compatible switch animation */
    input:checked ~ div.dot {
        transform: translateX(100%);
    }
    input:checked ~ div.w-11 {
        background-color: #3b82f6; /* Tailwind blue-500 */
    }
</style>

<section class="max-w-2xl  mx-auto text-sm pt-2">
   
            <div class="flex flex-col bg-white p-3">
                
                <div class="container mx-auto pb-5">
                    <div class="flex items-center mb-2">
                        <a href="{{ url($advert->state_slug . '/' . $advert->title_slug .'/'. $advert->ad_id) }}" class="flex items-center">
                            <div class="mr-2"><img class="w-14 h-14 rounded-full" src="{{  asset('uploads/images/'.$advert->firstImage->image) }}"> </div>
                            <div class="flex-col">
                                <h4 class="font-semibold text-lg">{{ $advert->owner->name }}</h4> 
                                <h6 class=" text-base -mt-2 text-sm lg:text-base">{{ $advert->ad_title }}</h6> 
                            </div>
                        </a>
                    </div>


                    <div id="chat-box" class="chat-box overflow-y-auto  rounded-lg mb-2 max-h-[300px] sm:max-h-[350px] md:max-h-[380px] lg:max-h-[400px]">
                        @foreach($messages as $msg)
                            <div class="my-2 mx-1 {{ $msg->sender_id == $user->user_id ? 'text-right' : 'text-left' }}">
                                <div class="inline-block rounded-lg p-2 
                                    {{ $msg->sender_id == $user->user_id ? 'bg-dark_green text-white font-semibold' : 'bg-secondary-200 font-semibold' }}">
                                    <p>{{ $msg->message_content }}</p>
                                    <span class="text-xs font-normal">{{ ucfirst(\Carbon\Carbon::parse($msg->created_at)->format('d M Y, h:ia')) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form id="chat-form" class="">
                        @csrf
                        <input type="hidden" name="advert_id" value="{{ $advert->id }}">
                        <input type="hidden" name="receiver_id" value="{{ $receiver->user_id }}">
                        
                        <textarea type="text" id="chat-input" rows="3" name="message" class="flex-1 p-2 rounded-l-lg border border-gray-300 focus:outline-none focus:border-blue-400 w-full text-gray-700" 
                               placeholder="Type your message..." autocomplete="off"></textarea>

                        @if($advert->user_id == $user->user_id)

                        @else
                        <div class="space-y-4 mt-4">
                            <!-- Toggle Switch -->
                            <label class="flex items-center cursor-pointer">
                                <!-- Switch -->
                                <div class="relative">
                                    <input type="checkbox" id="toggleSwitch" class="sr-only">
                                    <div class="w-11 h-6 bg-gray-300 rounded-full shadow-inner transition"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                                <!-- Label -->
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
                                    class="mt-1 block h-10 w-full rounded-l-lg border border-gray-300 focus:outline-none focus:border-blue-400 px-2"
                                >
                            </div>
                        </div>
                        @endif

                        @if($advert->sold=="Yes" && $advert->user_id != $user->user_id)
                            <div class="flex justify-between mt-2">
                                <span class="bg-green-100 p-2">
                                    @if($payment->buyer_status =="pending")
                                        <a href="/payment/mark-received/{{ $payment->id }}" onclick="return confirm('Are you sure you want to confirm Received?');"><i class="bi bi-check-all"></i> Mark Received</a> 
                                    @else
                                        <span class="capitalize">{{ $payment->buyer_status }}</span>
                                    @endif
                                </span>
                                <span class="bg-yellow-100 p-2"><a href="/report-ad/{{ $advert->id }}"><i class="bi bi-exclamation-triangle"></i> Report an Issue</a> </span>
                            </div>
                        @endif

                        <button type="submit" id="sendBtn" class="bg-dark_green hover:bg-secondary-200 text-white px-4 py-2 rounded-lg w-full mt-3 mb-2">
                            <span class="default-text">Send</span>
                            <span class="loading-text hidden">Sending...</span>
                        </button>


                        @if($advert->buy_direct == "No" && $advert->user_id != $user->user_id)
                            <span class="mt-4 text-red-500">** Please avoid making payment before inspecting the goods **</span>
                        @endif

                    </form>
                </div>
                
        
    </div>


</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleSwitch');
    const wrapper = document.getElementById('extraInputWrapper');
    const amountInput = document.getElementById('extraInput');
    const chatInput = document.getElementById('chat-input');
    const chatForm = document.getElementById('chat-form');
    const sendBtn = document.getElementById('sendBtn');
    const defaultText = sendBtn.querySelector('.default-text');
    const loadingText = sendBtn.querySelector('.loading-text');

    if (toggle && wrapper && amountInput) {
        // Toggle switch functionality
        toggle.addEventListener('change', function () {
            if (toggle.checked) {
                wrapper.classList.remove('hidden');
                amountInput.removeAttribute('disabled');
                amountInput.setAttribute('required', 'required');
                if (amountInput.value) {
                    chatInput.value = `Would you accept ₦${amountInput.value}`;
                }
            } else {
                wrapper.classList.add('hidden');
                amountInput.removeAttribute('required');
                amountInput.setAttribute('disabled', 'disabled');
                amountInput.value = '';
                if (chatInput.value.startsWith('Would you accept')) {
                    chatInput.value = '';
                }
            }
        });

        // Update chat input on amount change
        amountInput.addEventListener('input', function() {
            if (toggle.checked && amountInput.value) {
                chatInput.value = `Would you accept ₦${amountInput.value}`;
            }
        });

        // Set correct state on page load
        if (toggle.checked) {
            wrapper.classList.remove('hidden');
            amountInput.removeAttribute('disabled');
            amountInput.setAttribute('required', 'required');
            if (amountInput.value) {
                chatInput.value = `Would you accept ₦${amountInput.value}`;
            }
        } else {
            wrapper.classList.add('hidden');
            amountInput.setAttribute('disabled', 'disabled');
        }
    }

    // Handle form submission
    if (chatForm && chatInput) {
    $(chatForm).on('submit', function (e) {
        e.preventDefault();

        let message = chatInput.value.trim();
        if (message === '') return;

        // Update message for offer
        if (toggle && toggle.checked && amountInput.value && !message.includes(amountInput.value)) {
            message = `Would you accept ₦${amountInput.value}`;
            chatInput.value = message;
        }

        // Disable button + show loading
        sendBtn.disabled = true;
        defaultText.classList.add('hidden');
        loadingText.classList.remove('hidden');

        $.ajax({
            url: '{{ route("chat.sendMessage", ["advertId" => $advert->id, "receiverId" => $receiver->user_id]) }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function () {
                // Reset form
                chatInput.value = '';
                if (toggle && toggle.checked) {
                    amountInput.value = '';
                    wrapper.classList.add('hidden');
                    amountInput.setAttribute('disabled', 'disabled');
                    amountInput.removeAttribute('required');
                    toggle.checked = false;
                }

                loadMessages();
            },
            error: function () {
                alert('Error sending message');
            },
            complete: function () {
                // Re-enable button
                sendBtn.disabled = false;
                defaultText.classList.remove('hidden');
                loadingText.classList.add('hidden');
            }
        });
    });
}

    // Load chat messages
    function loadMessages() {
        const chatBox = $('#chat-box');
        if (chatBox.length) {
            $.ajax({
                url: '{{ route("chat.show", ["advertId" => $advert->id, "receiverId" => $receiver->user_id]) }}',
                method: 'GET',
                success: function (data) {
                    chatBox.html($(data).find('#chat-box').html());
                    chatBox.scrollTop(chatBox.prop('scrollHeight'));
                }
            });
        }
    }

    // Poll for messages
    if ($('#chat-box').length) {
        setInterval(loadMessages, 5000);
    }
});
</script>



<script>
  const chatBox = document.getElementById('chat-box');
  
  // Function to check and update padding
  function updateChatBoxPadding() {
    if (chatBox.innerHTML.trim() === '') {
      chatBox.classList.remove('p-2');
    } else {
      chatBox.classList.add('p-2');
    }
  }
  
  // Initial check
  updateChatBoxPadding();
  
  // Optional: If you dynamically add content later, call updateChatBoxPadding() after adding content
</script>
@include('dashboard.layouts.footer')
