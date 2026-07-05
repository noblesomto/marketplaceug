@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>User Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/users">Users</a></li>
                    <li class="breadcrumb-item active">Details</li>
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
                            <i class="bi bi-person-badge me-2"></i>User Details - {{ $user->name }}
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

                        <!-- Advertising Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                                                <i class="bi bi-megaphone text-primary fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="text-muted mb-1">Total Adverts</h6>
                                                <h4 class="mb-0 fw-bold">{{ $active_adverts ?? 0 }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded-3 me-3">
                                                <i class="bi bi-check-circle text-success fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="text-muted mb-1">Sold Adverts</h6>
                                                <h4 class="mb-0 fw-bold">{{ $sold_adverts ?? 0 }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card stat-card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded-3 me-3">
                                                <i class="bi bi-currency-exchange text-info fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="text-muted mb-1">Total Revenue</h6>
                                                <h4 class="mb-0 fw-bold">₦{{ number_format($totalRevenue ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-none">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="bi bi-info-circle me-2"></i>Basic Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Full Name</label>
                                            <p class="fw-medium">{{ $user->name ?? 'Not provided' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Email Address</label>
                                            <p class="fw-medium">{{ $user->email }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Phone Number</label>
                                            <p class="fw-medium">{{ $user->phone ?? 'Not provided' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Account Status</label>
                                            <p>
                                                @if($user->acc_status == "0")
                                                    <span class="badge bg-warning bg-opacity-15 text-warning">
                                                        <i class="bi bi-hourglass-split me-1"></i>Pending
                                                    </span>
                                                @elseif($user->disable_account === 'yes')
                                                    <span class="badge bg-danger bg-opacity-15 text-danger">
                                                        <i class="bi bi-slash-circle me-1"></i>Disabled
                                                    </span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-15 text-success">
                                                        <i class="bi bi-check-circle me-1"></i>Active
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Account Verfication</label>
                                            <p>
                                                @if($user->verified == "no")
                                                    <span class="badge bg-warning bg-opacity-15 text-white">
                                                        <i class="bi bi-hourglass-split me-1"></i>Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-15 text-white">
                                                        <i class="bi bi-check-circle me-1"></i>Verified
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address & Financial Information -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-none">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="bi bi-geo-alt me-2"></i>Location & Financials
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Address</label>
                                            <p class="fw-medium">{{ $user->address ?? 'Not provided' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">City</label>
                                            <p class="fw-medium">{{ $user->city ?? 'Not provided' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">State</label>
                                            <p class="fw-medium">{{ $user->state ?? 'Not provided' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Pending Settlement</label>
                                            <p class="fw-medium text-success">₦{{ number_format($pendingRevenue, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-none">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="bi bi-gear me-2"></i>Account Actions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap gap-3 justify-content-center">
                                            <!-- Status Toggle -->
                                            <div class="text-center">
                                                @if($user->acc_status == "0")
                                                    <h6 class="text-muted mb-2">Activate Account</h6>
                                                    <a href="/admin/user-status/{{ $user->user_id }}/1"
                                                       class="btn btn-success px-4"
                                                       onclick="return confirm('Activate this account?');">
                                                        <i class="bi bi-check-circle me-1"></i> Activate
                                                    </a>
                                                @elseif($user->disable_account === 'yes')
                                                    <h6 class="text-muted mb-2">Enable Account</h6>
                                                    <form method="POST" action="/admin/disable-status/{{ $user->user_id }}" style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="action" value="enable">
                                                        <button type="submit" class="btn btn-success px-4"
                                                                onclick="return confirm('Re-enable this account?');">
                                                            <i class="bi bi-check-circle me-1"></i> Enable
                                                        </button>
                                                    </form>
                                                @else
                                                    <h6 class="text-muted mb-2">Disable Account</h6>
                                                    <button type="button" class="btn btn-warning px-4"
                                                            onclick="showDisableModal('{{ $user->user_id }}', '{{ addslashes($user->name) }}')">
                                                        <i class="bi bi-slash-circle me-1"></i> Disable
                                                    </button>
                                                @endif
                                            </div>

                                            <!-- Delete User -->
                                            <div class="text-center">
                                                <h6 class="text-muted mb-2">Delete Account</h6>
                                                <form action="{{ route('admin.delete.user', $user->user_id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger px-4"
                                                            onclick="return confirm('WARNING: This will permanently delete the user account and all associated data. Continue?');">
                                                        <i class="bi bi-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Back Button -->
                                            <div class="text-center">
                                                <h6 class="text-muted mb-2">Return to List</h6>
                                                @if($user->acc_status == "0")
                                                    <a href="/admin/unverified-users" class="btn btn-outline-secondary px-4">
                                                @elseif($user->disable_account === 'yes')
                                                    <a href="/admin/disabled-users" class="btn btn-outline-secondary px-4">
                                                @else
                                                    <a href="/admin/active-users" class="btn btn-outline-secondary px-4">
                                                @endif
                                                    <i class="bi bi-arrow-left me-1"></i> Back
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<style>
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .form-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .fw-medium {
        font-weight: 500;
    }
    .badge {
        padding: 0.35rem 0.65rem;
        font-weight: 500;
    }
    .stat-card {
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
</style>

{{-- Disable account modal with reason --}}
<div class="modal fade" id="disableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="disableForm" action="">
                @csrf
                <input type="hidden" name="action" value="disable">
                <div class="modal-header border-0">
                    <h6 class="modal-title text-danger"><i class="bi bi-slash-circle me-2"></i>Disable Account</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <p class="text-muted mb-3" id="disableModalMessage"></p>
                    <label class="form-label fw-semibold">Reason <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="reason" class="form-control" rows="3"
                              placeholder="e.g. Suspicious activity, violated terms…" maxlength="500"></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-slash-circle me-1"></i>Disable Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showDisableModal(userId, userName) {
    document.getElementById('disableModalMessage').textContent =
        `This will block "${userName}" from logging in. You can re-enable at any time.`;
    document.getElementById('disableForm').action = `/admin/disable-status/${userId}`;
    new bootstrap.Modal(document.getElementById('disableModal')).show();
}
</script>

@include('admin.layouts.footer')
