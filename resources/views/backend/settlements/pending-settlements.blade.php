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
                <th scope="col">Amount</th>
                <th scope="col">Bank Name</th>
                <th scope="col">Account name</th>
                <th scope="col">Account Number</th>
                <th scope="col">Settlement Status</th>
                <th scope="col">Action</th>

              </tr>
            </thead>
            <tbody>
            @if(!empty($payments) && $payments->isNotEmpty())
    @foreach ($payments as $row)
        <tr>
    <td>
        @if($row->advert->firstImage->image)
            <img
                width="60px"
                src="{{ asset('uploads/images/' . $row->advert->firstImage->image) }}"
                class="img-responsive"
                alt="Image"
            >
        @else
            <img
                width="60px"
                src="{{ asset('frontend/images/default.png') }}"
                class="img-responsive"
                alt="Default Image"
            >
        @endif
    </td>
    <td>{{ $row->advert->ad_title }}</td>
    <td><a href="/admin/view-user/{{ $row->user->user_id }}">{{ $row->advert->owner->name }}</a> </td>
    <td>₦{{ number_format($row->advert->price, 2, '.', ',') }}</td>
    <td>{{ $row->advert->owner->bank_name }}</td>
    <td>{{ $row->advert->owner->account_name }}</td>
    <td>{{ $row->advert->owner->account_number }}</td>
    <td>
      <span class="badge
        {{ $row->seller_settlement === 'yes' ? 'bg-success text-white' : 'bg-secondary text-white' }}">
        {{ $row->seller_settlement }}
    </span>
    </td>

    <td><a class="text-primary" href="/admin/confirm-settlement/{{ $row->id }}" onclick="return confirm('Are you sure you want to Confirm Settlement?');">Comfirm Settlement</a></td>


    @endforeach
@else
    <tr>
        <td colspan="12">No records available</td>
    </tr>
@endif
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
