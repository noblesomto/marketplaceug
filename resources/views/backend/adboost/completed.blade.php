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
                <th scope="col">Amount Paid</th>
                <th scope="col">Days Remaining</th>
                <th scope="col">Start Date</th>
                <th scope="col">Boost Type</th>

              </tr>
            </thead>
            <tbody>
            @foreach ( $adverts as $row )
              @php
                  $expiry = \Carbon\Carbon::parse($row->start_date)->addDays($row->duration);
                  $daysRemaining = \Carbon\Carbon::now()->diffInDays($expiry, false); // false to get negative if expired
              @endphp
              <tr>
                <td> <img width="60px" src="{{  asset('uploads/images/'.$row->advert->firstImage->image) }}" class="img-responsive" alt="Image"></td>
                <td>{{ $row->advert->ad_title }}</td>
                <td><a href="/admin/view-user/{{ $row->user->user_id }}">{{ $row->user->name }}</a> </td>
                <td>₦{{ number_format($row->amount, 2, '.', ',') }}</td>
                <td>
                  @if ($daysRemaining >= 0)
                      <p class="text-green-600">Expires in {{ $daysRemaining }} day{{ $daysRemaining != 1 ? 's' : '' }}</p>
                  @else
                      <p class="text-red-600">Expired</p>
                  @endif
                </td>
                <td>{{ date('j F Y', strtotime($row->start_date)); }}</td>
                <td><span class="text-capitalize">{{$row->boost_type}}</span> </td>

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
