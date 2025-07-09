@include('backend.layouts.header')
@include('backend.layouts.nav')

<script src="{{ asset('backend/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('backend/js/jquery.min.js') }}"></script>
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<main id="main" class="main">

    <div class="pagetitle">
    <h1>Edit User</h1>
  
    </div><!-- End Page Title -->
    
    <section class="section">
    <div class="row">
    <div class="col-lg-10 offset-1">
    
        <div class="card">
        <div class="card-body">
            <h5 class="card-title">Edit User</h5>
            @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif
            <!-- General Form Elements -->
            <form action="/admin/edit-user/{{ $user->user_id }}" method="POST" role="form" class="" >
             @csrf 
    
            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">First Name:</label>
                <div class="">
                    @if ($errors->has('first_name'))
                        <span class="text-danger">{{ $errors->first('first_name') }}</span>
                    @endif
                <input type="text" name="first_name" class="form-control" placeholder="First Name" value="{{ $user->first_name }}" required>
                </div>
            </div>

            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Last Name:</label>
                <div class="">
                    @if ($errors->has('last_name'))
                        <span class="text-danger">{{ $errors->first('last_name') }}</span>
                    @endif
                <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="{{ $user->last_name }}" required>
                </div>
            </div>

            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Email Address:</label>
                <div class="">
                    @if ($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                    @endif
                <input type="email" name="email" class="form-control" placeholder="Email Address" value="{{ $user->email }}" required>
                </div>
            </div>
            

            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Phone <small>(Add country code)</small>:</label>
                <div class="">
                    @if ($errors->has('phone'))
                        <span class="text-danger">{{ $errors->first('phone') }}</span>
                    @endif
                <input type="text" name="phone" class="form-control" placeholder="Phone" value="{{ $user->phone }}" required>
                </div>
            </div>

            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Date of Birth:</label>
                <div class="">
                    @if ($errors->has('dob'))
                        <span class="text-danger">{{ $errors->first('dob') }}</span>
                    @endif
                <input type="date" name="dob" class="form-control" placeholder="Date of birth" value="{{ $user->dob }}" required>
                </div>
            </div>

            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Gender:</label>
                <div class="">
                    @if ($errors->has('gender'))
                        <span class="text-danger">{{ $errors->first('gender') }}</span>
                    @endif
                <select name="gender" class="form-select" aria-label="Default select example" required>
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo ($user['gender']=='Male') ? "selected" : ""; ?>>Male</option>
                   <option value="Female" <?php echo ($user['gender']=='Female') ? "selected" : ""; ?>>Female</option>
                </select>
                </div>
            </div>

            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Account Type:</label>
                <div class="">
                    @if ($errors->has('acc_type'))
                        <span class="text-danger">{{ $errors->first('acc_type') }}</span>
                    @endif
                <select name="acc_type" class="form-select" aria-label="Default select example" required>
                    <option value="">Select Account Type</option>
                    <option value="Savings" {{ ($user->acc_type =='Savings') ? "selected" : ""; }}>Savings</option>
                    <option value="Cooperate" {{ ($user->acc_type =='Cooperate') ? "selected" : ""; }}>Cooperate</option>
                    <option value="Current" {{ ($user->acc_type =='Current') ? "selected" : ""; }}>Current</option>
                    <option value="Account Checking" {{ ($user->acc_type =='Account Checking') ? "selected" : ""; }}>Account Checking</option>
                </select>
                </div>
            </div>


            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Currency:</label>
                <div class="">
                    @if ($errors->has('currency'))
                        <span class="text-danger">{{ $errors->first('currency') }}</span>
                    @endif
                <select name="currency" class="form-select" aria-label="Default select example" required>
                    <option value="">Select Currency</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                </select>
                </div>
            </div>


            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Account Number:</label>
                <div class="">
                    @if ($errors->has('acc_number'))
                        <span class="text-danger">{{ $errors->first('acc_number') }}</span>
                    @endif
                <input type="text" name="acc_number" class="form-control" placeholder="Account Number" value="{{ $user->acc_number }}" required>
                </div>
            </div>

            
    
    
            <div class="row col-sm-12 mt-5">
                
                <button type="submit" class="btn btn-primary btn-block">Edit</button>
              
            </div>
    
            </form><!-- End General Form Elements -->
    
        </div>
        </div>
    
    </div>
    
    </div>
    </section>

    <section class="section">
    <div class="row">
    <div class="col-lg-10 offset-1">
    
        <div class="card">
        <div class="card-body">
            <h5 class="card-title">Update User Profile Picture</h5>
            @if(session('profile_status'))
                <div class="alert alert-{{session('profile_status')['type']}}">
                    {{session('profile_status')['text']}}
                </div>
            @endif
            <!-- General Form Elements -->
            <form action="/admin/update-picture/{{ $user->user_id }}" method="POST" role="form" enctype="multipart/form-data" >
             @csrf 
    
            <div class="row">
                <div class="col-lg-3">
                    @if($user->profile_picture=="")
                        <img src="{{ asset('dashboard/images/user.png') }}" width="150px">
                    @else
                        <img src="{{  asset('uploads/'.$user->profile_picture) }}" width="150px">
                    @endif
                </div>
                <div class="col-lg-9">
                    <label for="inputText" class=" col-form-label">Upload Profile Picture:</label>
                    <div class="">
                        @if ($errors->has('profile_picture'))
                            <span class="text-danger">{{ $errors->first('profile_picture') }}</span>
                        @endif
                    <input type="file" name="profile_picture" class="form-control" placeholder="Profile Picture"  required>
                    </div>

                    <div class="col-lg-12 mt-5">
                        <button type="submit" class="btn btn-primary btn-block">Upload Profile</button>
                    </div>
                </div>

                
            </div>

    
            </form><!-- End General Form Elements -->
    
        </div>
        </div>
    
    </div>
    
    </div>
    </section>
    
    </main><!-- End #main -->

    @include('backend.layouts.footer')