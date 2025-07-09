@include('backend.layouts.header')
@include('backend.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>All Users</h1>

</div><!-- End Page Title -->

<section class="section">
  <div class="row">


    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">All Users</h5>

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
                <th scope="col">A/C Number</th>
                <th scope="col">Balance</th>
                <th scope="col">Edit</th>
                <th scope="col">Transaction</th>
                <th scope="col">Action</th>
                <th scope="col">Delete</th>
              </tr>
            </thead>
            <tbody>
            @foreach ( $user as $row )
              <tr>
                <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                <td>{{ $row->acc_type }}</td>
                <td>{{ $row->acc_number }} </td>
                <td>{{ number_format($row->balance, 2, '.', ',') }} {{ $row->currency }}</td>
                <td><a href="/admin/edit-user/{{ $row->user_id }}" >Edit</a></td>
                <td><a href="/admin/view-user/{{ $row->user_id }}">View Transactions</a></td>
                <td>
                  <form class="form-horizontal" action="/admin/change-status/{{ $row->user_id }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <select name="user_status">
                        <option value="Active" <?php echo ($row['user_status']=='Active') ? "selected" : ""; ?>>Active</option>
                        <option value="Dormant" <?php echo ($row['user_status']=='Dormant') ? "selected" : ""; ?>>Dormant</option>
                        <option value="Blocked" <?php echo ($row['user_status']=='Blocked') ? "selected" : ""; ?>>Blocked</option>
                          <option value="Frozen" <?php echo ($row['user_status']=='Frozen') ? "selected" : ""; ?>>Frozen</option>
                    </select>
                    <input type="submit" name="submit" value="Change Status">
                </form>
                </td>
         
                <td><a href="/admin/delete-user/{{ $row->user_id }}" onclick="return confirm('Are you sure you want to delete User?');">Delete</a></td>
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