@include('backend.layouts.header')
@include('backend.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item active">Adverts</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">{{ $page_title }}</h5>
              <div class="search-bar" style="max-width: 300px;">
                <form class="search-form d-flex align-items-center">
                  <input type="text" name="query" placeholder="Search adverts..." title="Enter search keyword">
                  <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                </form>
              </div>
            </div>

            @if(session('status'))
              <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show mt-3">
                {{session('status')['text']}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <div class="table-responsive mt-3">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Advert</th>
                    <th scope="col">Owner</th>
                    <th scope="col">Price</th>
                    <th scope="col">Location</th>
                    <th scope="col">Published</th>
                    <th scope="col">Status</th>
                    <th scope="col">Sold</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($adverts as $row)
                  <tr>
                    <td>
                      <img src="{{ $row->firstImage && $row->firstImage->image
                                  ? asset('uploads/images/' . $row->firstImage->image)
                                  : asset('frontend/images/default.png') }}"
                           class="img-thumbnail rounded"
                           style="width: 60px; height: 50px; object-fit: cover;"
                           alt="Advert Image"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="View image">
                    </td>
                    <td class="fw-bold">{{ Str::limit($row->ad_title, 30) }}</td>
                    <td>
                      <a href="/admin/view-user/{{ $row->user->user_id }}" class="text-primary">
                        {{ $row->user->name }}
                      </a>
                    </td>

                    @if($row->category==3)
                        <td>{{ $row->salary }}</td>
                    @elseif($row->category==18)
                        <td>{{ $row->expected_salary }}</td>
                    @elseif($row->contact_price=="yes")
                        <td>Contact For Price</td>
                    @else
                        <td>₦{{ number_format(floatval($row->price ?? 0), 2) }}</td>
                    @endif

                    <td>{{ $row->state }}</td>
                    <td>{{ date('j M Y', strtotime($row->created_at)) }}</td>
                    <td>
                      @if($row->ad_status == 'active')
                        <span class="badge bg-success">Active</span>
                      @elseif($row->ad_status == 'disabled')
                        <span class="badge bg-warning text-dark">Disabled</span>
                      @else
                        <span class="badge bg-danger text-dark">Banned</span>
                      @endif
                    </td>
                    <td>
                      @if($row->sold == "Yes")
                        <span class="badge bg-danger">Sold</span>
                      @else
                        <span class="badge bg-info">Available</span>
                      @endif
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        @if($row->ad_status == 'active')
                          <a href="/admin/advert-status/{{ $row->id }}/banned" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Disable/Ban">
                            <i class="bi bi-x-circle"></i>
                          </a>
                        @else
                          <a href="/admin/advert-status/{{ $row->id }}/active" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Enable">
                            <i class="bi bi-check-all"></i>
                          </a>
                        @endif

                        @if($row->sold == "No")
                          <a href="/admin/sold-status/{{ $row->id }}/Yes" class="btn btn-sm btn-outline-danger"
                             onclick="return confirm('Mark this advert as sold?')" data-bs-toggle="tooltip" title="Mark Sold">
                            <i class="bi bi-check2-circle"></i>
                          </a>
                        @else
                          <a href="/admin/sold-status/{{ $row->id }}/No" class="btn btn-sm btn-outline-info"
                             onclick="return confirm('Mark this advert as available?')" data-bs-toggle="tooltip" title="Mark Available">
                            <i class="bi bi-x-square"></i>
                          </a>
                        @endif

                        <a href="/admin/delete-ad/{{ $row->id }}" class="btn btn-sm btn-outline-dark"
                           onclick="return confirm('Delete this advert permanently?')" data-bs-toggle="tooltip" title="Delete">
                          <i class="bi bi-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="row mt-3">
              <div class="col-md-6">
                <div class="text-muted">
                  Showing {{ $adverts->firstItem() }} to {{ $adverts->lastItem() }} of {{ $adverts->total() }} entries
                </div>
              </div>
              <div class="col-md-6">
                <div class="float-end">
                  {{ $adverts->links('pagination::bootstrap-4') }}
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
  // Enable tooltips
  document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });
  });
</script>

@include('backend.layouts.footer')
