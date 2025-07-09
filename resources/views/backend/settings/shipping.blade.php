@include('backend.layouts.header')
@include('backend.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Dashboard</h1>

</div><!-- End Page Title -->

<section class="section">
  <div class="row">


    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Categories</h5>
          @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif
          <!-- Table with stripped rows -->
          <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">Logo</th>
                <th scope="col">Company</th>
                <th scope="col">Weight</th>
                <th scope="col">Description</th>
                <th scope="col">Price</th>
                <th scope="col">Username</th>
                <th scope="col">Password</th>
                <th scope="col">Edit</th>
              </tr>
            </thead>
            <tbody>
            @foreach ( $shippings as $row )
              <tr>
                <td> <img width="60px" src="{{  asset('uploads/shipping/'.$row->logo) }}" class="img-responsive" alt="Image"></td>
                <td>{{ $row->company }} </td>
                <td>Max {{ $row->weight }} Kg</td>
                <td>{{ $row->description }} </td>
                <td>₦{{ number_format($row->price, 2, '.', ',') }} </td>
                <td>{{ $row->username }} </td>
                <td>{{ $row->show_password }} </td>
                <td><a href="">Edit Password</a> </td>
              </tr>
            @endforeach
              
            </tbody>
          </table>
        </div>
          <!-- End Table with stripped rows -->
    
          <div class="row">
            <div class="col-lg-8 offset-md-2">
                
            </div>
          </div>

        </div>
      </div>

      



    </div>
  </div>
</section>

<section class="section">
    <div class="row">
    <div class="col-lg-12">
    
        <div class="card">
        <div class="card-body">
            <h5 class="card-title">New Shipping</h5>
       
            <!-- General Form Elements -->
            <form action="/settings/setup-shipping" method="POST" role="form" class="" enctype="multipart/form-data">
             @csrf 
    
            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Company Name</label>
                <div class="col-sm-10">
                    @if ($errors->has('company'))
                        <span class="text-danger">{{ $errors->first('company') }}</span>
                    @endif
                <input type="text" name="company" class="form-control" placeholder="Company Name" value="{{ old('company') }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Max Weight</label>
                <div class="col-sm-10">
                    @if ($errors->has('weight'))
                        <span class="text-danger">{{ $errors->first('weight') }}</span>
                    @endif
                <input type="text" name="weight" maxlength="11" pattern="[0-9]*" class="form-control" placeholder="Weight" value="{{ old('weight') }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Price</label>
                <div class="col-sm-10">
                    @if ($errors->has('price'))
                        <span class="text-danger">{{ $errors->first('price') }}</span>
                    @endif
                <input type="text" name="price" maxlength="11" pattern="[0-9]*" class="form-control" placeholder="Price" value="{{ old('price') }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-10">
                    @if ($errors->has('description'))
                        <span class="text-danger">{{ $errors->first('description') }}</span>
                    @endif
                <input type="text" name="description" class="form-control" placeholder="Description of Weight" value="{{ old('description') }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Company Logo</label>
                <div class="col-sm-10">
                    @if ($errors->has('logo'))
                        <span class="text-danger">{{ $errors->first('logo') }}</span>
                    @endif
                <input type="file" name="logo" class="form-control" placeholder="Company Logo" value="{{ old('logo') }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Company Username</label>
                <div class="col-sm-10">
                    @if ($errors->has('username'))
                        <span class="text-danger">{{ $errors->first('username') }}</span>
                    @endif
                <input type="text" name="username" class="form-control" placeholder="Company Username" value="{{ old('username') }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Company Password</label>
                <div class="col-sm-10">
                    @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                    @endif
                <input type="text" name="password" class="form-control" placeholder="Company Password" value="{{ old('password') }}" required>
                </div>
            </div>


           
    
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
    
            </form><!-- End General Form Elements -->
    
        </div>
        </div>
    
    </div>
    
    </div>
    </section>


</main><!-- End #main -->
<script>
function myFunction() {
  confirm("Are you sure you want to delete?");
}
</script>
@include('backend.layouts.footer')
