@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2">
        Profile Name & Address
        @include('frontend.components.flash-message')
    </div>
  
		            
		        	<div class="max-w-2xl mx-auto bg-white p-3 rounded-lg">
				        <form method="POST" action="/user/profile-address" enctype="multipart/form-data">
				            @csrf
				            @method('PUT')
				       
				        <div class="mb-4 mt-1">
				            
				            <label class="text-sm font-semibold">Name *</label>
				            @if ($errors->has('name'))
				                <span class="text-red-700 py-1">{{ $errors->first('name') }}</span>
				            @endif
				            <input type="text" id="name" name="name" placeholder="Name" value="{{ $user->name }}" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
				        </div>

				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold">Address *</label>
				            @if ($errors->has('address'))
				                <span class="text-red-700 py-1">{{ $errors->first('address') }}</span>
				            @endif
				            <input type="text" id="name" name="address" placeholder="Address" value="{{ $user->address }}" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
				        </div>

				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold">City *</label>
				            @if ($errors->has('city'))
				                <span class="text-red-700 py-1">{{ $errors->first('city') }}</span>
				            @endif
				            <input type="text" id="name" name="city" placeholder="City" value="{{ $user->city }}" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
				        </div>


				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold">State *</label>
				            @if ($errors->has('state'))
				                <span class="text-red-700 py-1">{{ $errors->first('state') }}</span>
				            @endif
<select name="state" id="state" class="w-full bg-body-100 px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
    <option value="" {{ old('state', $user->state ?? '') == '' ? 'selected' : '' }}>- Select State -</option>
    <option value="Abia" {{ old('state', $user->state ?? '') == 'Abia' ? 'selected' : '' }}>Abia</option>
    <option value="Adamawa" {{ old('state', $user->state ?? '') == 'Adamawa' ? 'selected' : '' }}>Adamawa</option>
    <option value="AkwaIbom" {{ old('state', $user->state ?? '') == 'AkwaIbom' ? 'selected' : '' }}>AkwaIbom</option>
    <option value="Anambra" {{ old('state', $user->state ?? '') == 'Anambra' ? 'selected' : '' }}>Anambra</option>
    <option value="Bauchi" {{ old('state', $user->state ?? '') == 'Bauchi' ? 'selected' : '' }}>Bauchi</option>
    <option value="Bayelsa" {{ old('state', $user->state ?? '') == 'Bayelsa' ? 'selected' : '' }}>Bayelsa</option>
    <option value="Benue" {{ old('state', $user->state ?? '') == 'Benue' ? 'selected' : '' }}>Benue</option>
    <option value="Borno" {{ old('state', $user->state ?? '') == 'Borno' ? 'selected' : '' }}>Borno</option>
    <option value="Cross River" {{ old('state', $user->state ?? '') == 'Cross River' ? 'selected' : '' }}>Cross River</option>
    <option value="Delta" {{ old('state', $user->state ?? '') == 'Delta' ? 'selected' : '' }}>Delta</option>
    <option value="Ebonyi" {{ old('state', $user->state ?? '') == 'Ebonyi' ? 'selected' : '' }}>Ebonyi</option>
    <option value="Edo" {{ old('state', $user->state ?? '') == 'Edo' ? 'selected' : '' }}>Edo</option>
    <option value="Ekiti" {{ old('state', $user->state ?? '') == 'Ekiti' ? 'selected' : '' }}>Ekiti</option>
    <option value="Enugu" {{ old('state', $user->state ?? '') == 'Enugu' ? 'selected' : '' }}>Enugu</option>
    <option value="FCT" {{ old('state', $user->state ?? '') == 'FCT' ? 'selected' : '' }}>FCT</option>
    <option value="Gombe" {{ old('state', $user->state ?? '') == 'Gombe' ? 'selected' : '' }}>Gombe</option>
    <option value="Imo" {{ old('state', $user->state ?? '') == 'Imo' ? 'selected' : '' }}>Imo</option>
    <option value="Jigawa" {{ old('state', $user->state ?? '') == 'Jigawa' ? 'selected' : '' }}>Jigawa</option>
    <option value="Kaduna" {{ old('state', $user->state ?? '') == 'Kaduna' ? 'selected' : '' }}>Kaduna</option>
    <option value="Kano" {{ old('state', $user->state ?? '') == 'Kano' ? 'selected' : '' }}>Kano</option>
    <option value="Katsina" {{ old('state', $user->state ?? '') == 'Katsina' ? 'selected' : '' }}>Katsina</option>
    <option value="Kebbi" {{ old('state', $user->state ?? '') == 'Kebbi' ? 'selected' : '' }}>Kebbi</option>
    <option value="Kogi" {{ old('state', $user->state ?? '') == 'Kogi' ? 'selected' : '' }}>Kogi</option>
    <option value="Kwara" {{ old('state', $user->state ?? '') == 'Kwara' ? 'selected' : '' }}>Kwara</option>
    <option value="Lagos" {{ old('state', $user->state ?? '') == 'Lagos' ? 'selected' : '' }}>Lagos</option>
    <option value="Nasarawa" {{ old('state', $user->state ?? '') == 'Nasarawa' ? 'selected' : '' }}>Nasarawa</option>
    <option value="Niger" {{ old('state', $user->state ?? '') == 'Niger' ? 'selected' : '' }}>Niger</option>
    <option value="Ogun" {{ old('state', $user->state ?? '') == 'Ogun' ? 'selected' : '' }}>Ogun</option>
    <option value="Ondo" {{ old('state', $user->state ?? '') == 'Ondo' ? 'selected' : '' }}>Ondo</option>
    <option value="Osun" {{ old('state', $user->state ?? '') == 'Osun' ? 'selected' : '' }}>Osun</option>
    <option value="Oyo" {{ old('state', $user->state ?? '') == 'Oyo' ? 'selected' : '' }}>Oyo</option>
    <option value="Plateau" {{ old('state', $user->state ?? '') == 'Plateau' ? 'selected' : '' }}>Plateau</option>
    <option value="Rivers" {{ old('state', $user->state ?? '') == 'Rivers' ? 'selected' : '' }}>Rivers</option>
    <option value="Sokoto" {{ old('state', $user->state ?? '') == 'Sokoto' ? 'selected' : '' }}>Sokoto</option>
    <option value="Taraba" {{ old('state', $user->state ?? '') == 'Taraba' ? 'selected' : '' }}>Taraba</option>
    <option value="Yobe" {{ old('state', $user->state ?? '') == 'Yobe' ? 'selected' : '' }}>Yobe</option>
    <option value="Zamfara" {{ old('state', $user->state ?? '') == 'Zamfara' ? 'selected' : '' }}>Zamfara</option>
</select>
				        </div>

				        
				        <div class="mb-6 mt-4">
				            
				           <label for="profile_image" class="block text-gray-700 text-sm font-bold mb-2">Profile Image</label>
				           @if ($errors->has('profile_image'))
				                <span class="text-red-900 my-1">{{ $errors->first('profile_image') }}</span>
				            @endif
						    <input type="file" name="profile_image" id="profile_image"
						        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
				          </div>
				

				          <div class="mt-8">
				            <button type="submit" class="btn btn-primary py-1 text-lg flex justify-center items-center">
				                <span>Update</span>
				                <span class="ml-2">
				                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
				                      <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
				                    </svg>
				                </span>
				            </button>
				          </div>
				     				        
				        </form>
				        
				    </div>
		
 
</div>

    
</section>



@include('dashboard.layouts.footer')