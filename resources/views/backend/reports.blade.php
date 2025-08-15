@include('backend.layouts.header')
@include('backend.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>{{ $page_title }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $page_title }}</li>
                </ol>
            </nav>
        </div>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="bi bi-flag me-2"></i>Complaint Management
                        </h5>
                    </div>

                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    @if(session('status')['type'] === 'success')
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                    @else
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    @endif
                                    <div>{{ session('status')['text'] }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Ad Title</th>
                                        <th scope="col">Complainer</th>
                                        <th scope="col">Complaint</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adverts as $row)
                                        <tr>
                                            <td>
                                                <h6 class="mb-0 fw-semibold">{{ $row->adverts->ad_title }}</h6>
                                                <small class="text-muted">ID: {{ $row->adverts->id }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium">{{ $row->user->name }}</span>
                                                    <small class="text-muted">{{ $row->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 250px;" title="{{ $row->message }}">
                                                    {{ $row->message }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($row->status == "resolved")
                                                    <span class="badge bg-success text-white">
                                                        <i class="bi bi-check-circle me-1"></i> Resolved
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-white">
                                                        <i class="bi bi-exclamation-triangle me-1"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    @if($row->status == "resolved")
                                                        <a href="/admin/report-status/{{ $row->id }}/pending"
                                                           class="btn btn-sm btn-outline-warning"
                                                           title="Mark as unresolved">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </a>
                                                    @else
                                                        <a href="/admin/report-status/{{ $row->id }}/resolved"
                                                           class="btn btn-sm btn-outline-success"
                                                           title="Mark as resolved">
                                                            <i class="bi bi-check-lg"></i>
                                                        </a>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete('{{ $row->id }}')"
                                                            title="Delete complaint">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($adverts->hasPages())
                            <div class="row align-items-center mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing <strong>{{ $adverts->firstItem() }}</strong> to <strong>{{ $adverts->lastItem() }}</strong> of <strong>{{ $adverts->total() }}</strong> results</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end">
                                        {{ $adverts->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<script>
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this complaint?')) {
        window.location.href = '/admin/delete-complaint/' + id;
    }
    return false;
}
</script>

<style>
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .table th {
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

@include('backend.layouts.footer')
