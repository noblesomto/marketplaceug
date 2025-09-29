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
              <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show">
                {{session('status')['text']}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <!-- Table with stripped rows -->
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Advert</th>
                    <th scope="col">Buyer</th>
                    <th scope="col">Ad Price</th>
                    <th scope="col">Commission</th>
                    <th scope="col">Amount Paid</th>
                    <th scope="col">Payment Date</th>
                    <th scope="col">Ship ID</th>
                    <th scope="col">Buyer Status</th>
                    <th scope="col">Shipper Status</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($payments as $row)
                    <tr>
                      <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                      <td>
                        @if($row->hasMedia('images'))
                          <img style="width: 60px; height: 50px; object-fit: cover;" src="{{ $row->getFirstMediaUrl('images', 'thumbnail') }}" class="img-thumbnail" alt="Image">
                        @else
                          <img width="60px" src="{{ asset('frontend/images/default.png') }}" class="img-thumbnail" alt="Default Image">
                        @endif
                        <br>
                        <small>{{ $row->advert->ad_title }}</small>
                      </td>
                      <td>
                        <a href="/admin/view-user/{{ $row->user->user_id }}" class="text-primary">
                          {{ $row->user->name }}
                        </a>
                      </td>
                      <td>₦{{ number_format($row->advert->price, 2) }}</td>
                      <td>₦{{ number_format($row->commission, 2) }}</td>
                      <td>₦{{ number_format($row->amount_paid, 2) }}</td>
                      <td>{{ $row->created_at->format('j F Y') }}</td>
                      <td>{{ $row->ship_code ?? 'N/A' }}</td>

                      <td>
                        <span class="badge bg-{{ $row->buyer_status == 'delivered' ? 'success' : ($row->buyer_status == 'canceled' ? 'danger' : 'warning') }}">
                          {{ ucfirst($row->buyer_status) }}
                        </span>
                      </td>
                      <td>
                          <span class="badge bg-{{ $row->shipping_status == 'delivered' ? 'success' : ($row->shipping_status == 'canceled' ? 'danger' : 'warning') }}">
                            {{ ucfirst($row->shipping_status) }}
                          </span>
                        </td>
                      <td>
                        <button class="btn btn-sm btn-primary edit-btn"
                                data-id="{{ $row->id }}"
                                data-buyer-status="{{ $row->buyer_status }}"
                                data-shipping-status="{{ $row->shipping_status }}"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal">
                          <i class="bi bi-pencil"></i> Edit
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center">No records available</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <!-- End Table with stripped rows -->

            <div class="row mt-3">
              <div class="col-md-6">
                <div class="text-muted">
                  Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results
                </div>
              </div>
              <div class="col-md-6">
                <div class="float-end">
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Payment Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="buyer_status" class="form-label">Buyer Status</label>
            <select class="form-select" id="buyer_status" name="buyer_status">
              <option value="pending">Pending</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="shipping_status" class="form-label">Shipping Status</label>
            <select class="form-select" id="shipping_status" name="shipping_status">
              <option value="pending">Pending</option>
              <option value="shipped">Shipped</option>
               <option value="pickup">Ready for Pickup</option>
              <option value="delivered">Delivered</option>
              <option value="canceled">Canceled</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('backend.layouts.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button click
    document.querySelectorAll('.edit-btn').forEach(button => {
      button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const buyerStatus = this.getAttribute('data-buyer-status');
        const shippingStatus = this.getAttribute('data-shipping-status');

        // Set form action
        document.getElementById('editForm').action = `/admin/update-payment/${id}`;

        // Populate form fields
        document.getElementById('buyer_status').value = buyerStatus || '';
        document.getElementById('shipping_status').value = shippingStatus;
      });
    });
  });
</script>
