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
                <th scope="col">Sold Date</th>
                <th scope="col">Action</th>
                <th scope="col">Mark Sold</th>
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
                <td>{{ date('j F Y', strtotime($row->sold_date)); }}</td>
                @if( $row->ad_status == 1 )
                <td><a class="text-primary" href="/admin/advert-status/{{ $row->id }}/0"><i class="bi bi-x-circle"></i> Disable Advert</a></td>
                @else
                <td><a class="text-primary" href="/admin/advert-status/{{ $row->id }}/1"><i class="bi bi-check-all"></i> Enable Advert</a></td>
                @endif
                @if( $row->sold == "No" )
                <td><a class="text-primary" href="/admin/sold-status/{{ $row->id }}/Yes" onclick="return confirm('Are you sure you want to Mark Advert Sold?');"><i class="bi bi-check2-circle"></i> Mark Sold</a></td>
                @else
                <td><a class="text-primary" href="/admin/sold-status/{{ $row->id }}/No" onclick="return confirm('Are you sure you want to Mark Advert NOT Sold?');"><i class="bi bi-x-square"></i> Mark Not Sold</a></td>
                @endif
                <td><a class="text-danger" href="/admin/delete-advert/{{ $row->id }}" onclick="return confirm('Are you sure you want to delete Advert?');"><i class="bi bi-trash"></i> Delete Advert</a></td>
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
                            Showing {{ $adverts->firstItem() }} to {{ $adverts->lastItem() }} of {{ $adverts->total() }} results
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            {{ $adverts->links('pagination::bootstrap-4') }}
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

</main><!-- End #main -->
<script>
function myFunction() {
  confirm("Are you sure you want to delete?");
}
</script>
@include('backend.layouts.footer')
