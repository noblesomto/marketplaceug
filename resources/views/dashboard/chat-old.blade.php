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

<section class="max-w-2xl  mx-auto text-sm pt-10">
   
            <div class="flex flex-col bg-white p-3">
                
                <div class="container mx-auto ">
                    <div class="flex items-center mb-5">
                        <a href="/advert/{{ $advert->id }}/{{ $advert->title_slug }}" class="flex items-center">
                            <div class="mr-2"><img class="w-14 h-14 rounded-full" src="{{  asset('uploads/images/'.$advert->firstImage->image) }}"> </div>
                            <div class="flex-col">
                                <h4 class="font-semibold text-lg">{{ $advert->owner->name }}</h4> 
                                <h6 class=" text-base -mt-2">{{ $advert->ad_title }}</h6> 
                            </div>
                        </a>
                    </div>


                    <div id="chat-box" class="chat-box overflow-y-auto bg-gray-100 p-1 rounded-lg mb-4 " style="max-height: 400px;">
                    <!-- Messages will be loaded here -->
                    @foreach($messages as $msg)
                        <div class="mb-2 {{ $msg->sender_id == $user->user_id ? 'text-right' : 'text-left' }}">
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
                        
                        <textarea type="text" id="chat-input" name="message" class="flex-1 p-2 rounded-l-lg border border-gray-300 focus:outline-none focus:border-blue-400 w-full" 
                               placeholder="Type your message..." autocomplete="off"> </textarea>

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
                                    maxlength="11" pattern="[0-9]*"
                                    class="mt-1 block h-10 w-full rounded-md border-gray-300 shadow-lg focus:border-indigo-500 focus:ring-indigo-500 px-2"
                                >
                            </div>
                        </div>


                        
                        <button type="submit" class="bg-dark_green hover:bg-secondary-200 text-white px-4 py-2 rounded-r-lg w-full mt-5">Send</button>
                    </form>
                </div>
                
        
    </div>


</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Scroll chat box to the bottom
        const chatBox = $('#chat-box');
        chatBox.scrollTop(chatBox.prop('scrollHeight'));

        // AJAX form submission for sending a message
        $('#chat-form').on('submit', function (e) {
            e.preventDefault();

            let message = $('#chat-input').val().trim();
            if (message === '') return;

            $.ajax({
                url: '{{ route("chat.sendMessage", ["advertId" => $advert->id, "receiverId" => $receiver->user_id]) }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function () {
                    $('#chat-input').val('');
                    loadMessages();
                },
                error: function (xhr) {
                    alert('Error sending message');
                }
            });
        });

        // Function to load latest messages
        function loadMessages() {
            $.ajax({
                url: '{{ route("chat.show", ["advertId" => $advert->id, "receiverId" => $receiver->user_id]) }}',
                method: 'GET',
                success: function (data) {
                    // Update the chat box with new messages
                    chatBox.html($(data).find('#chat-box').html());
                    chatBox.scrollTop(chatBox.prop('scrollHeight'));
                }
            });
        }

        // Poll for new messages every 5 seconds
        setInterval(loadMessages, 5000);
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleSwitch');
    const wrapper = document.getElementById('extraInputWrapper');
    const input = document.getElementById('extraInput');

    toggle.addEventListener('change', function () {
        if (toggle.checked) {
            wrapper.classList.remove('hidden');
            input.setAttribute('required', 'required');
        } else {
            wrapper.classList.add('hidden');
            input.removeAttribute('required');
            input.value = '';
        }
    });

    // Run on page load if state is preserved
    if (toggle.checked) {
        wrapper.classList.remove('hidden');
        input.setAttribute('required', 'required');
    }
});
</script>



@include('dashboard.layouts.footer')