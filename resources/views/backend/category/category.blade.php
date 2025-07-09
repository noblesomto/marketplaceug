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


           <div class="container-fluid">
               <div class="row gx-2">
                @foreach ( $category as $row )
                   <div class="col-sm-3 border p-2 mx-3 my-1">
                       <div class="row ">
                           <div class="col-sm-8">
                               {{ $row->category }}
                           </div>
                           <div class="col-sm-4">
                              <a href="/admin/sub-category/{{ $row->id }}" >View </a>
                              <a href="/admin/delete-category/{{ $row->id }}" onclick="return confirm('Are you sure you want to delete this Category with the Subcategory and Brands?');" class="text-danger mx-2" title="Delete Category" > <i class="fa fa-trash"></i> </a>
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
            <h5 class="card-title">New Category</h5>
       
            <!-- General Form Elements -->
            <form action="/admin/category" method="POST" role="form" class="" enctype="multipart/form-data">
             @csrf 
    
            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Categor Name</label>
                <div class="col-sm-10">
                    @if ($errors->has('category'))
                        <span class="text-danger">{{ $errors->first('category') }}</span>
                    @endif
                <input type="text" name="category" class="form-control" placeholder="Category Name" value="{{ old('category') }}" required>
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