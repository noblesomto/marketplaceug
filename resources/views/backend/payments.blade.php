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
                <th scope="col">User</th>
                <th scope="col">Ad Price</th>
                <th scope="col">Commission</th>
                <th scope="col">Amount Paid</th>
                <th scope="col">Payment Date</th>
                <th scope="col">Payment Status</th>
                <th scope="col">Action</th>
                <th scope="col">Shipping Status</th>
                <th scope="col">Update Status</th>
              </tr>
            </thead>
            <tbody>
            @foreach ( $payments as $row )
              <tr>
                <td> <img width="60px" src="{{  asset('uploads/images/'.$row->advert->firstImage->image) }}" class="img-responsive" alt="Image"></td>
                <td>{{ $row->advert->ad_title }}</td>                
                <td><a href="/admin/view-user/{{ $row->user->user_id }}">{{ $row->user->name }}</a> </td>
                <td>₦{{ number_format($row->advert->price, 2, '.', ',') }}</td>
                <td>₦{{ number_format($row->commission, 2, '.', ',') }}</td>
                <td>₦{{ number_format($row->amount_paid, 2, '.', ',') }}</td>
                <td>{{ date('j F Y', strtotime($row->created_at)); }}</td>
                @if($row->payment_status=="paid")
                <td><span class="text-success text-capitalize">{{ $row->payment_status }}</span> </td>
                @else
                <td><span class="text-danger text-capitalize">{{ $row->payment_status }}</span> </td>
                @endif
                @if($row->payment_status=="paid")
                <td>Payment Confirmed</td>
                @else
                <td><a class="text-primary" href="/admin/confirm-payment/{{ $row->id }}" onclick="return confirm('Are you sure you want to Confirm Payment?');">Comfirm Payment</a></td>
                @endif
                @if($row->shipping_status=="delivered")
                <td><span class="text-success text-capitalize">{{ $row->shipping_status }}</span> </td>
                @else
                <td><span class="text-danger text-capitalize">{{ $row->shipping_status }}</span> </td>
                @endif
                <td>
                    @if($row->shipping_status=="delivered")
                        <span>Delivery Confirmed</span>
                    @else
                        <a class="text-primary" href="/admin/confirm-delivery/{{ $row->id }}" onclick="return confirm('Are you sure you want to Confirm Delivery?');">Comfirm Delivery</a>
                    @endif
                </td>
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
                            Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            {{ $payments->links('pagination::bootstrap-4') }}
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
