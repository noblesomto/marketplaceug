@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="pb-14">
    
    <div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 md:mt-10 mb-5 rounded-lg">
        <div class="flex justify-center">
        <h3 class="text-2xl font-bold">Report User</h3>
    </div>

        @include('frontend.components.flash-message')
        <form method="POST" action="/report-user/{{ $reported->user_id }}">
            @csrf

            

            <div class="mb-4 mt-4">
                @if ($errors->has('subject'))
                    <span class="text-red-700 py-1">{{ $errors->first('subject') }}</span>
                @endif
                <label class="text-sm font-semibold">Name *</label>
                <input type="text" id="name" name="subject" placeholder="Subject" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $reported->name }}" readonly>
            </div>

            <div class="mb-4 mt-4">
                @if ($errors->has('subject'))
                    <span class="text-red-700 py-1">{{ $errors->first('subject') }}</span>
                @endif
                <label class="text-sm font-semibold">Subject *</label>
                <select name="subject" class="w-full px-3 py-2 border border-gray-300 bg-white rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    <option value="">--Select Reason for Report--</option>
                    <option value="Fraudulent Activity / Scam">Fraudulent Activity / Scam</option>
                    <option value="User Requested Payment Outside Platform">User Requested Payment Outside Platform</option>
                    <option value="Harassment or Abusive Behavior">Harassment or Abusive Behavior</option>
                    <option value="Fake or Misleading Information">Fake or Misleading Information</option>
                    <option value="Impersonation or Fake Profile">Impersonation or Fake Profile</option>
                    <option value="Inappropriate Language or Content">Inappropriate Language or Content</option>
                    <option value="Suspicious or Unverified Account">Suspicious or Unverified Account</option>
                    <option value="Spamming or Repeated Unwanted Messages">Spamming or Repeated Unwanted Messages</option>
                    <option value="User Violates Platform Rules">User Violates Platform Rules</option>
                    <option value="Others">Others</option>
                </select>
            </div>

            <div class="mb-4 mt-4">
                @if ($errors->has('first_name'))
                    <span class="text-red-700 py-1">{{ $errors->first('first_name') }}</span>
                @endif
                <label class="text-sm font-semibold">Full Name *</label>
                <input type="text" id="name" name="name" placeholder="Full Name" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" readonly value="{{ $reporter->name }}" >
            </div>


            <div class="mb-6 mt-4">
                @if ($errors->has('email'))
                    <span class="text-red-900 my-1">{{ $errors->first('email') }}</span>
                @endif
                <label class="text-sm font-semibold">Email *</label>
                <input type="email" id="email" name="email" placeholder="Email Address" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $reporter->email }}" readonly>
            </div>

            <div class="mb-4 mt-4">
                @if ($errors->has('message'))
                    <span class="text-red-700 py-1">{{ $errors->first('message') }}</span>
                @endif
                <label class="text-sm font-semibold">Message *</label>
                <textarea name="message" placeholder="Enter your Message..." class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[150px]" ></textarea>
                
            </div>

            <div class="mb-4 mt-4">
                <label class="text-sm font-semibold">ReCaptcha *</label>
                @if ($errors->has('g-recaptcha-response'))
                   <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
               @endif
                <div class="g-recaptcha" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>               
            </div>

            <div class="mt-8">
                <button type="submit" class="flex justify-center items-center bg-transparent hover:bg-primary text-dark_green font-semibold hover:text-dark_green  py-3 px-6 border-2 border-dark_green hover:border-dark_green rounded-full ">
                    <span>Submit</span>
                    <span class="ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                          <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
              </div>

        </form>
    </div>
</section>

@include('frontend.layouts.footer')


