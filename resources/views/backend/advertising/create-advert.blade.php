@include('backend.layouts.header')
@include('backend.layouts.nav')

<script src="{{ asset('backend/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('backend/js/jquery.min.js') }}"></script>
<script src="{{ asset('tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('tinymce/jquery.tinymce.min.js') }}"></script>



<main id="main" class="main">

    <div class="pagetitle">
    <h1>Advertising</h1>
  
    </div><!-- End Page Title -->
 

    <section class="section">
    <div class="row">
    <div class="col-lg-12">
    
        <div class="card">
        <div class="card-body">
            <h5 class="card-title">Adverts</h5>
            <!-- Table with stripped rows -->
          <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Company</th>
                <th scope="col">Url</th>
                <th scope="col">Duration</th>
                <th scope="col">Size</th>
                <th scope="col">Status</th>
                <th scope="col">Delete</th>
              </tr>
            </thead>
            <tbody>
            @foreach ( $adverts as $row )
              <tr>
                <td>
                  <img width="150px" height="150px" class="img-fluid object-fit-cover" style="max-height: 150px;" src="{{ asset('uploads/advertising/'.$row->image) }}">
                </td>
                <td>{{ $row->company }}</td>
                <td>{{ $row->url }} </td>
                <td>{{ $row->duration }} Days</td>
                <td>{{ $row->type }}</td>
                <td>{{ $row->status }}</td>
                <td><a href="/admin/delete-advert/{{ $row->advert_id }}" onclick="return confirm('Are you sure you want to delete Advert?');">Delete</a></td>
              </tr>
            @endforeach
              
            </tbody>
          </table>
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
            <h5 class="card-title">Create new Advert</h5>
            @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif
            <!-- General Form Elements -->
            <form action="/admin/create-advert" method="POST" role="form" enctype="multipart/form-data">
             @csrf

             <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Account Type:</label>
                <div class="">
                    @if ($errors->has('company'))
                        <span class="text-danger">{{ $errors->first('company') }}</span>
                    @endif
                <input type="text" name="company" class="form-control" placeholder="Company Name" value="{{ old('company') }}" required>
                </div>
            </div>

    
            <div class="row col-sm-12 mb-3">
                <label for="inputText" class=" col-form-label">Advert Url</label>
                <div class="">
                    @if ($errors->has('url'))
                        <span class="text-danger">{{ $errors->first('url') }}</span>
                    @endif
                <input type="text" name="url" class="form-control" placeholder="Advert Url" value="{{ old('url') }}" required>
                </div>
            </div>


            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Advert Duration:</label>
                <div class="">
                    @if ($errors->has('duration'))
                        <span class="text-danger">{{ $errors->first('duration') }}</span>
                    @endif
                <select name="duration" class="form-select" aria-label="Default select example" required>
                    <option value="">Select Duration</option>
                    <option value="7">7 Days</option>
                    <option value="14">14 Days</option>
                    <option value="30">30 Days</option>
                </select>
                </div>
            </div>

            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Advert Size:</label>
                <div class="">
                    @if ($errors->has('type'))
                        <span class="text-danger">{{ $errors->first('type') }}</span>
                    @endif
                <select name="type" class="form-select" aria-label="Default select example" required>
                    <option value="">Select Size</option>
                    <option value="banner">Leather Banner</option>
                    <option value="sidebar">Side Bar</option>
                </select>
                </div>
            </div>

            <div class="row col-sm-12 mb-2">
                <label for="inputText" class=" col-form-label">Advert Image</label>
                <div class="">
                    @if ($errors->has('advert_image'))
                        <span class="text-danger">{{ $errors->first('advert_image') }}</span>
                    @endif
                <input type="file" name="advert_image" class="form-control" accept="image/*" required>
                </div>
            </div>

            <div class="row mb-3">
                <label class=" col-form-label"></label>
                <button type="submit" class="btn btn-primary col-sm-10">Submit</button>
                
            </div>
    
            </form><!-- End General Form Elements -->
    
        </div>
        </div>
    
    </div>
    
    </div>
    </section>

    
    


    
    </main><!-- End #main -->

    @include('backend.layouts.footer')
