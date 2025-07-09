@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2">
        Account Settings
        @include('frontend.components.flash-message')
    </div>
  
		            
<div class="w-full max-w-xl mx-auto mt-10 space-y-2">
    <!-- Accordion 1 - Open by default -->
    <div class="accordion border rounded-lg">
        <button class="accordion-header w-full px-4 py-2 text-left font-semibold bg-gray-100 hover:bg-gray-200 flex justify-between items-center">
            <span>Phone number & Email</span>
            <svg class="icon w-4 h-4 transform transition-transform rotate-180" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="accordion-content px-4 py-6 bg-white border-t">
            <form method="POST" action="/user/profile-phone" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            	<div class="space-y-4">
            		<div class="">
            			<label class="text-sm">Phone Number</label>
            			<input type="text" maxlength="11" pattern="[0-9]*" name="phone" placeholder="Phone Number" value="{{ $user->phone }}" class="w-full px-3 py-2 border-b-2 border-2-gray-300" required>
            		</div>
            		<div class="">
            			<label class="text-sm">Verified Email Address</label>
            			<input type="text"  name="email" placeholder="Email Address" value="{{ $user->email }}" class="w-full px-3 py-2 border-b-2 border-2-gray-300" readonly>
            		</div>
            		<div class="">
			            <button type="submit" class="bg-primary py-2 text-xs lg:text-lg rounded-lg px-4">
			                <span>Update</span>
			            </button>
			         </div>
            	</div>
        	</form>
        </div>
    </div>

    <!-- Accordion 2 -->
    <div class="accordion border rounded-lg">
        <button class="accordion-header w-full px-4 py-2 text-left font-semibold bg-gray-100 hover:bg-gray-200 flex justify-between items-center">
            <span>Change Password</span>
            <svg class="icon w-4 h-4 transform transition-transform" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="accordion-content hidden px-4 py-2 bg-white border-t">
            <form method="POST" action="/user/change-password" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            	<div class="space-y-4">
            		<div class="">
            			<label class="text-sm">Current Password</label>
            			@if ($errors->has('old_password'))
                            <span class="text-red-700 text-sm">{{ $errors->first('old_password') }}</span>
                        @endif
            			
            			<input type="password" name="old_password" placeholder="Current Password"  class="w-full px-3 py-2 border-b-2 border-2-gray-300" required>
            		</div>

            		<div class="">
            			<label class="text-sm">New Password</label>
            			@if ($errors->has('password'))
                            <span class="text-red-700 text-sm">{{ $errors->first('password') }}</span>
                        @endif
            			
            			<input type="password" name="password" placeholder="New Password"  class="w-full px-3 py-2 border-b-2 border-2-gray-300" required>
            		</div>
            		<div class="">
            			<label class="text-sm">Confirm Password</label>
            			@if ($errors->has('password_confirmation'))
                            <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                        @endif
            			<input type="password"  name="password_confirmation" placeholder="Confirm Password"  class="w-full px-3 py-2 border-b-2 border-2-gray-300" readonly>
            		</div>

            		<div class="">
			            <button type="submit" class="bg-primary py-2 text-xs lg:text-lg rounded-lg px-4">
			                <span>Update</span>
			            </button>
			         </div>
            	</div>
        	</form>
        </div>
    </div>


    <div class="mt-4 text-red-700">
    	<a href="/user/disable-account" onclick="return confirm('Are you sure you want to Diactivate Account');">Delete Account</a>
    </div>
</div>


 
</div>

    
</section>

<script>
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.icon');

            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        });
    });
</script>


@include('dashboard.layouts.footer')