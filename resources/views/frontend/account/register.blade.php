@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')

<section class="w-full md:w-4/6 mx-auto bg-white pb-10">
    <div class="border-b-2 border-b-gray-400 h-20 flex justify-center items-center">
        <h2 class="font-bold text-lg">Register in 30 seconds</h2>
    </div>
    
    <div class="w-full md:w-[35%] mx-auto px-4">
        <div class="p-2">
            @include('frontend.components.flash-message')
        </div>
        
        <form method="POST" action="/register" class="mt-4">
            @csrf
            
            <div class="flex justify-center w-full">
                <h2 class="font-bold text-base">How would you like to use Marketplace NG</h2>
            </div>

            <div class="flex space-x-4 my-3">
                <label class="flex items-center border border-gray-400 py-2 px-4 rounded-lg w-full">
                    <input type="radio" name="acc_type" value="Private" class="form-radio text-dark_green" id="showDivRadio" onclick="showDiv('Profile Name')" required>
                    <span class="ml-2 font-semibold">Private</span>
                </label>

                <label class="flex items-center border border-gray-400 py-2 px-4 rounded-lg w-full">
                    <input type="radio" name="acc_type" value="Commercial" class="form-radio text-dark_green" onclick="showDiv('Company Name')">
                    <span class="ml-2 font-semibold">Commercial</span>
                </label>
            </div>

            <span class="mt-4">
                <a class="font-bold text-sm text-dark_green cursor-pointer" id="showModalRadio" onclick="showModal()">
                    When do I act commercially?
                </a>
            </span>

            <!-- Name Field (Shown for both Private and Commercial Users) -->
            <div id="myDiv" class="hidden mt-4">
                <div class="mb-4">
                    @if ($errors->has('name'))
                        <span class="text-red-700 py-1 text-sm">{{ $errors->first('name') }}</span>
                    @endif
                    <input type="text" id="name" name="name" placeholder="Profile Name" value="{{ old('name') }}" 
                           class="w-full px-3 py-2 text-base border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

                <div class="mb-4">
                    @if ($errors->has('phone'))
                        <span class="text-red-700 py-1 text-sm">{{ $errors->first('phone') }}</span>
                    @endif
                    <input type="text" id="phone" name="phone" placeholder="Phone Number" value="{{ old('phone') }}" 
                           class="w-full px-3 py-2 text-base border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            

            

            <div class="mb-4">
                @if ($errors->has('address'))
                    <span class="text-red-700 py-1 text-sm">{{ $errors->first('address') }}</span>
                @endif
                <input type="text" id="name" name="address" placeholder="Street Address" value="{{ old('address') }}"  
                       class="w-full px-3 py-2 text-base border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <div class="mb-4">
                @if ($errors->has('city'))
                    <span class="text-red-700 py-1 text-sm">{{ $errors->first('city') }}</span>
                @endif
                <input type="text" id="name" name="city" placeholder="City" value="{{ old('city') }}"  
                       class="w-full px-3 py-2 text-base border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

             <div class="mb-4">
                @if ($errors->has('state'))
                    <span class="text-red-700 py-1 text-sm">{{ $errors->first('state') }}</span>
                @endif
                <select name="state" id="state" class="w-full bg-body-100 px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    <option value="" selected="selected">- Select State -</option>
                        <option value="Abia">Abia</option>
                        <option value="Adamawa">Adamawa</option>
                        <option value="AkwaIbom">AkwaIbom</option>
                        <option value="Anambra">Anambra</option>
                        <option value="Bauchi">Bauchi</option>
                        <option value="Bayelsa">Bayelsa</option>
                        <option value="Benue">Benue</option>
                        <option value="Borno">Borno</option>
                        <option value="Cross River">Cross River</option>
                        <option value="Delta">Delta</option>
                        <option value="Ebonyi">Ebonyi</option>
                        <option value="Edo">Edo</option>
                        <option value="Ekiti">Ekiti</option>
                        <option value="Enugu">Enugu</option>
                        <option value="FCT">FCT</option>
                        <option value="Gombe">Gombe</option>
                        <option value="Imo">Imo</option>
                        <option value="Jigawa">Jigawa</option>
                        <option value="Kaduna">Kaduna</option>
                        <option value="Kano">Kano</option>
                        <option value="Katsina">Katsina</option>
                        <option value="Kebbi">Kebbi</option>
                        <option value="Kogi">Kogi</option>
                        <option value="Kwara">Kwara</option>
                        <option value="Lagos">Lagos</option>
                        <option value="Nasarawa">Nasarawa</option>
                        <option value="Niger">Niger</option>
                        <option value="Ogun">Ogun</option>
                        <option value="Ondo">Ondo</option>
                        <option value="Osun">Osun</option>
                        <option value="Oyo">Oyo</option>
                        <option value="Plateau">Plateau</option>
                        <option value="Rivers">Rivers</option>
                        <option value="Sokoto">Sokoto</option>
                        <option value="Taraba">Taraba</option>
                        <option value="Yobe">Yobe</option>
                        <option value="Zamfara">Zamafara</option>
                    </select>
            </div>

            <div class="mt-6">
                <h2 class="font-bold text-base">Your login details</h2>
            </div>
            
            <div class="mb-4 mt-3">
                @if ($errors->has('email'))
                    <span class="text-red-900 text-sm">{{ $errors->first('email') }}</span>
                @endif
                <input type="email" id="email" name="email" placeholder="Enter your email" 
                       class="w-full px-3 py-2 text-base border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                       value="{{ old('email') }}" required>
            </div>

            <!-- Password Field -->
            <div class="mb-4 relative">
                @if ($errors->has('password'))
                    <span class="text-red-900 text-sm">{{ $errors->first('password') }}</span>
                @endif
                <input type="password" id="password" name="password" placeholder="Enter your password" 
                       class="w-full px-3 py-2 text-base border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                       required>
                
                <!-- Show/Hide Button -->
                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500" aria-label="Toggle password visibility">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z" />
                    </svg>
                </button>
            </div>

            <div class="my-4">
                <label class="text-sm font-semibold">ReCaptcha *</label>
                @if ($errors->has('g-recaptcha-response'))
                    <span class="text-danger text-sm">{{ $errors->first('g-recaptcha-response') }}</span>
                @endif
                <div class="g-recaptcha mt-2" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>   
            </div>

            <div class="my-4">
                <div class="flex items-start">
                    <input id="terms-checkbox" type="checkbox" class="w-5 h-5 mt-1 text-blue-600 bg-gray-100 rounded border-gray-300 focus:ring-blue-500" required>
                    <label for="terms-checkbox" class="ml-2 text-sm text-gray-900">
                        Yes, I look forward to regular news by email from the group of companies - you can unsubscribe at any time
                    </label>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full bg-secondary-200 hover:bg-secondary-100 text-sm text-dark_green font-black py-3 px-2 rounded-full flex justify-center items-center transition-colors">
                    Register for Free
                </button>
            </div>

            <div class="mt-4 mb-6 text-sm text-gray-700">
                <a class="text-dark_green hover:underline" href="">Our terms of use</a> apply. Information on how we process your data can be found in our <a class="text-dark_green hover:underline" href="">privacy policy</a>.
            </div>
        </form>
    </div>
</section>

<!-- Commercial Use Modal -->
<div id="myModal" class="fixed inset-0 hidden bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white w-full max-w-lg p-6 rounded-lg shadow-lg">
        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
            <h4 class="font-semibold text-lg">Information on commercial use</h4>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        
        <div class="mt-4">
            <p>In Marketplace NG ads, we separate private from business: private and commercial users must meet different requirements.</p>
        </div>

        <div class="mt-6">
            <!-- Accordion Items -->
            <div class="accordion-item border-b border-gray-200">
                <button class="w-full px-0 py-3 text-left flex justify-between items-center focus:outline-none" onclick="toggleAccordion('accordion1')">
                    <span class="font-semibold">When am I a commercial user?</span>
                    <svg id="icon1" class="h-5 w-5 transform transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div id="accordion1" class="max-h-0 overflow-hidden transition-all duration-300">
                    <div class="pb-4">
                        <p>You are trading commercially on Marketplace NG ads if you:</p>
                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>Buy or create items to sell</li>
                            <li>Offer services</li>
                            <li>Regularly offer large quantities of items</li>
                            <li>Sell similar goods over a longer period of time</li>
                            <li>Buy or sell for your company</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item border-b border-gray-200">
                <button class="w-full px-0 py-3 text-left flex justify-between items-center focus:outline-none" onclick="toggleAccordion('accordion2')">
                    <span class="font-semibold">Does this cost me anything?</span>
                    <svg id="icon2" class="h-5 w-5 transform transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div id="accordion2" class="max-h-0 overflow-hidden transition-all duration-300">
                    <div class="pb-4">
                        <p class="mt-2">Posting Ad on Marketplace NG is basically free. Both commercial and private users can place ads free of charge.</p>
                        <p class="mt-2">For commercial providers, up to 10 advertisements within 30 days are free of charge. A fee of ₦500 (including VAT) applies from the 11th advertisement onwards.</p>
                        <p class="mt-2">Exceptions are certain real estate categories and cars, for which fees may be charged from the first advertisement.</p>
                        <p class="mt-2">Optional additional fees may apply through additional packages for commercial users.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Prevent zooming on input fields in mobile */
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        font-size: 16px !important;
        min-height: 44px; /* Better touch target */
    }
    
    /* Remove number input spinners */
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    /* Better accordion transitions */
    .accordion-item {
        transition: all 0.3s ease;
    }
    
    /* Rotate accordion icons when expanded */
    .transform.rotate-180 {
        transform: rotate(180deg);
    }
</style>

<script>
  
    function showDiv(placeholderText) {
        document.getElementById('myDiv').classList.remove('hidden');
        document.getElementById('name').placeholder = placeholderText;
    }
    
    function hideDiv() {
        document.getElementById('myDiv').classList.add('hidden');
    }

    
    // Modal functions
    function showModal() {
        document.getElementById('myModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        document.getElementById('myModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = '<path fill-rule="evenodd" d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm-7.5-5a8.24 8.24 0 017.5-5 8.24 8.24 0 017.5 5 8.24 8.24 0 01-7.5 5 8.24 8.24 0 01-7.5-5z" clip-rule="evenodd"/>';
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = '<path d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>';
        }
    }
    
    // Toggle accordion items
    function toggleAccordion(id) {
        const content = document.getElementById(id);
        const icon = document.getElementById('icon' + id.slice(-1));
        
        if (content.style.maxHeight) {
            content.style.maxHeight = null;
            icon.classList.remove('rotate-180');
        } else {
            content.style.maxHeight = content.scrollHeight + "px";
            icon.classList.add('rotate-180');
        }
    }
</script>

@include('frontend.layouts.footer')