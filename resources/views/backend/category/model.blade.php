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
          <h5 class="card-title">{{ $brand->brand }}</h5>
          @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif


           <div class="container-fluid">
               <div class="row gx-2">
                @foreach ( $model as $row )
                   <div class="col-sm-3 border p-2 mx-3 my-1">
                       <div class="row ">
                           <div class="col-sm-8">
                               {{ $row->model }}
                           </div>
                           <div class="col-sm-4">
                      
                              <a href="/admin/delete-model/{{ $row->id }}/{{ $brand->id }}" onclick="return confirm('Are you sure you want to delete thie Model?');" class="text-danger mx-2" title="Delete Model" > <i class="fa fa-trash"></i> </a>
                           </div>
                       </div>
                   </div>
                 @endforeach
               </div> 
           </div>
    
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
            <h5 class="card-title">Models for {{ $brand->brand }}</h5>
       
            <!-- General Form Elements -->
            <form action="/admin/model/{{ $brand->id }}" method="POST" role="form" class="" enctype="multipart/form-data">
             @csrf 
    
          
            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Model Name</label>
                <div class="col-sm-10">
                    @if ($errors->has('brand'))
                        <span class="text-danger">{{ $errors->first('brand') }}</span>
                    @endif

                <input type="hidden" name="brand" value="{{ $brand->id }}">

                <input type="text" name="model" class="form-control" placeholder="Model Name" value="{{ old('model') }}" required>
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
