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
                <th scope="col">Name</th>
                <th scope="col">A/C Type</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Date Joined</th>
                <th scope="col">view</th>
                <th scope="col">VerifyAccount</th>
              </tr>
            </thead>
            <tbody>
            @foreach ( $users as $row )
              <tr>
                <td>{{ $row->name }}</td>
                <td>{{ $row->acc_type }}</td>
                <td>{{ $row->email }} </td>
                <td>{{ $row->phone }} </td>
                <td>{{ date('j F Y', strtotime($row->created_at)); }}</td>
                <td><a href="/admin/view-user/{{ $row->user_id }}">View User</a></td>
                @if( $row->acc_status == 1 )
                <td><a class="text-primary" href="/admin/user-status/{{ $row->user_id }}/0">Unverify User</a></td>
                @else
                <td><a class="text-primary" href="/admin/user-status/{{ $row->user_id }}/1">Verify User</a></td>
                @endif
         
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
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            {{ $users->links('pagination::bootstrap-4') }}
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
