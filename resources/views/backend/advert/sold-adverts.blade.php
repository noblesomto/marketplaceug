@include('backend.layouts.header')
@include('backend.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>{{ $page_title }}</h1>

</div><!-- End Page Title -->

<section class="section">
  <div class="row">


    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">{{ $page_title }}</h5>

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
                <th scope="col">#</th>
                <th scope="col">Advert</th>
                <th scope="col">Ad Owner</th>
                <th scope="col">Ad Price</th>
                <th scope="col">Location</th>
                <th scope="col">Published Date</th>
                <th scope="col">Action</th>
                <th scope="col">Delete</th>
              </tr>
            </thead>
            <tbody>
            @foreach ( $adverts as $row )
              <tr>
                <td> <img width="60px" src="{{  asset('uploads/images/'.$row->firstImage->image) }}" class="img-responsive" alt="Image"></td>
                <td>{{ $row->ad_title }}</td>                
                <td><a href="/admin/view-user/{{ $row->user->user_id }}">{{ $row->user->name }}</a> </td>
                <td>₦{{ number_format($row->price, 2, '.', ',') }}</td>
                <td>{{ $row->state }}</td>
                <td>{{ date('j F Y', strtotime($row->created_at)); }}</td>
                @if( $row->ad_status == 1 )
                <td><a class="text-primary" href="/admin/advert-status/{{ $row->id }}/0">Disable Advert</a></td>
                @else
                <td><a class="text-primary" href="/admin/advert-status/{{ $row->id }}/1">Enable Advert</a></td>
                @endif
                <td><a class="text-danger" href="/admin/delete-advert/{{ $row->id }}" onclick="return confirm('Are you sure you want to delete Advert?');">Delete Advert</a></td>
              </tr>
            @endforeach
              
            </tbody>
          </table>
          </div>
          <!-- End Table with stripped rows -->

<div class="pagination-box text-center mt-50">
  <br>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            {{ $adverts->links('pagination::bootstrap-4') }}
        </ul>
    </nav>
</div>

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