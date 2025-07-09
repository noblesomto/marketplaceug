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
          <h5 class="card-title">GIG Locations</h5>
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
                <th scope="col">State</th>
                <th scope="col">City</th>
                <th scope="col">Address</th>
                <th scope="col">Delete</th>                
              </tr>
            </thead>
            <tbody>
            @foreach ( $gig as $row )
              <tr>
                <td>{{ $row->state->name }} </td>
                <td>{{ $row->city }}</td>
                <td>{{ $row->address }} </td>
                <td><a href="/settings/delete-gig-location/{{ $row->id }}" onclick="return confirm('Are you sure you want to delete Location?');">Delete</a> </td>
              </tr>
            @endforeach
              
            </tbody>
          </table>
        </div>
          <!-- End Table with stripped rows -->
    
          <div class="row">
            <div class="col-lg-12">
                <div class="pagination-box text-center mt-50">

                    <div class="row">
                        <div class="col-md-6 d-flex justify-content-start">
                            Showing {{ $gig->firstItem() }} to {{ $gig->lastItem() }} of {{ $gig->total() }} results
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            {{ $gig->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                  <br>
                
                </div>
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
            <h5 class="card-title">New Location</h5>
       
            <!-- General Form Elements -->
            <form action="/settings/gig-locations" method="POST" role="form" class="" enctype="multipart/form-data">
             @csrf 
    
            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">State</label>
                <div class="col-sm-10">
                    @if ($errors->has('state'))
                        <span class="text-danger">{{ $errors->first('state') }}</span>
                    @endif
                <select name="state" class="form-select" aria-label="Default select example" required>
                    <option value="">Select State</option>
                    @foreach ( $state as $row )
                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                    @endforeach
                </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Add City</label>
                <div class="col-sm-10">
                    @if ($errors->has('city'))
                        <span class="text-danger">{{ $errors->first('city') }}</span>
                    @endif
                <input type="text" name="city" class="form-control" placeholder="Enter City" value="{{ old('city') }}" required>
                </div>
            </div>

     

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Address</label>
                <div class="col-sm-10">
                    @if ($errors->has('address'))
                        <span class="text-danger">{{ $errors->first('address') }}</span>
                    @endif
                <input type="text" name="address" class="form-control" placeholder="Enter Location Address" value="{{ old('address') }}" required>
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