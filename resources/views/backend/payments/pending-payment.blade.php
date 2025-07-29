@include('backend.layouts.header')
@include('backend.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Pending Payment</h1>

</div><!-- End Page Title -->

<section class="section">
  <div class="row">


    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">All Request</h5>

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
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Amount</th>
 
                
              </tr>
            </thead>
            <tbody>
            @foreach ( $user as $row )
              <tr>
                <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                <td>{{ $row->email }} </td>
                <td>${{ number_format($row->price, 2, '.', ',') }} </td>
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
            {!! $user->links() !!}
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