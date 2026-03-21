@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item active">{{ $page_title }}</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->
   @if(session('status'))
      <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show">
        {{session('status')['text']}}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">{{ $page_title }}</h5>
              <div class="d-flex">
                <input type="text" class="form-control me-2" id="searchInput" placeholder="Search..." style="width: 200px;">
                <button class="btn btn-primary">Export</button>
              </div>
            </div>

            @if(session('status'))
              <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show mt-3">
                {{session('status')['text']}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <div class="table-responsive mt-3">
              <table class="table table-striped table-hover">
                <thead class="table-light">
                  <tr>
                    <th scope="col" width="50">#</th>
                    <th scope="col">Advert</th>
                    <th scope="col">User</th>
                    <th scope="col">Price</th>
                    <th scope="col">Commission</th>
                    <th scope="col">Paid</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status</th>
                    <th scope="col" width="150">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($payments as $row)
                    <tr>
                      <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                      <td>
                        <div class="d-flex align-items-center">
                          @if($row->advert && $row->advert->hasMedia('images'))
                            <img width="50" height="50" src="{{ $row->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                                 class="rounded me-2 object-fit-cover" alt="Ad Image">
                          @else
                            <img width="50" height="50" src="{{ asset('frontend/images/default.png') }}"
                                 class="rounded me-2 object-fit-cover" alt="Default Image">
                          @endif
                          <span class="text-truncate" style="max-width: 150px;">{{ $row->advert->ad_title }}</span>
                        </div>
                      </td>
                      <td>
                        <a href="/admin/view-user/{{ $row->user->user_id }}" class="text-primary">
                          {{ $row->user->name }}
                        </a>
                      </td>
                      <td>₦{{ number_format($row->advert->price, 2) }}</td>
                      <td>₦{{ number_format($row->commission, 2) }}</td>
                      <td>₦{{ number_format($row->amount_paid, 2) }}</td>
                      <td>{{ date('M j, Y', strtotime($row->created_at)) }}</td>
                      <td>
                        <span class="badge rounded-pill bg-{{ $row->payment_status == 'paid' ? 'success' : 'danger' }}">
                          {{ ucfirst($row->payment_status) }}
                        </span>
                      </td>
                      <td>
                        @if($row->payment_status == "paid")
                          <span class="text-success">Confirmed</span>
                        @else
                          <button class="btn btn-sm btn-outline-primary confirm-btn"
                                  data-id="{{ $row->id }}"
                                  data-bs-toggle="modal"
                                  data-bs-target="#confirmModal">
                            <i class="bi bi-check-circle"></i> Confirm
                          </button>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="9" class="text-center py-4">No payment records found</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="row mt-3">
              <div class="col-md-6">
                <div class="text-muted">
                  Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} entries
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex justify-content-end">
                  {{ $payments->links('pagination::bootstrap-4') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to confirm this payment?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="confirmForm" method="POST">
          @csrf
          <button type="submit" class="btn btn-primary">Confirm Payment</button>
        </form>
      </div>
    </div>
  </div>
</div>

@include('admin.layouts.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Handle confirm button click
    document.querySelectorAll('.confirm-btn').forEach(button => {
      button.addEventListener('click', function() {
        const paymentId = this.getAttribute('data-id');
        document.getElementById('confirmForm').action = `/admin/confirm-payment/${paymentId}`;
      });
    });

    // Simple search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
      const input = this.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? '' : 'none';
      });
    });
  });
</script>
