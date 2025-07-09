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
                <th scope="col">Ad Title</th>
                <th scope="col">Complainer</th>
                <th scope="col">Email</th>
                <th scope="col">Complaint</th>
                <th scope="col">Action</th>
                <th scope="col">Delete</th>
              </tr>
            </thead>
            <tbody>
            @foreach ( $adverts as $row )
              <tr>
                <td>{{ $row->adverts->ad_title }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->user->email }} </td>
                <td>{{ $row->message }} </td>
               
                @if( $row->status == "resolved" )
                <td><a class="text-primary" href="/admin/report-status/{{ $row->id }}/pending">Not Resolved</a></td>
                @else
                <td><a class="text-primary" href="/admin/report-status/{{ $row->id }}/resolved">Mark Resolved</a></td>
                @endif
         
                <td><a href="#" onclick="return confirm('Are you sure you want to delete Report?');">Delete</a></td>
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
